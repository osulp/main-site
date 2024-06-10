<?php

namespace Drupal\og_to_group\Plugin\migrate\process;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\group_content_menu\GroupContentMenuInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Get the group menu name from group.
 *
 * @MigrateProcessPlugin(
 *   id = "og_book_menu",
 *   source_module = "menu"
 * )
 */
class OgBookMenu extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $book_id = substr($value, strlen('book-toc-'));
    // Get the migrate source database connection.
    $migrate_database = Database::getConnection('default', 'migrate');
    $migrate_query = $migrate_database->select('node', 'n');
    $migrate_query->fields('n', ['title']);
    $migrate_query->condition('n.nid', $book_id);
    $migrate_query->condition('n.type', 'book');
    $book_name = $migrate_query->execute()->fetchField();
    $migrate_group_id_query = $migrate_database->select('node', 'n');
    $migrate_group_id_query->fields('n', ['nid']);
    $migrate_group_id_query->condition('n.title', $book_name);
    $migrate_group_id_query->condition('n.type', 'group');
    $migrate_group_id = $migrate_group_id_query->execute()->fetchField();
    if ($migrate_group_id !== FALSE) {
      /** @var \Drupal\group\Entity\Storage\GroupStorage $group_storage */
      $group_storage = $this->entityTypeManager->getStorage('group');
      /** @var \Drupal\group\Entity\Group $group */
      $group = $group_storage->load($migrate_group_id);
      $group_menu_content = group_content_menu_get_menus_per_group($group);
      $group_menu_content = reset($group_menu_content);
      $group_content_menu_id = $group_menu_content->get('entity_id')
        ->first()
        ->getValue()['target_id'];
      return GroupContentMenuInterface::MENU_PREFIX . $group_content_menu_id;
    }
    throw new MigrateSkipRowException(sprintf('No group found for Book %d Name %s', $book_id, $book_name));
  }

}
