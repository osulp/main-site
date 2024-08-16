<?php

namespace Drupal\osu_migrations_gradschool\Plugin\migrate\process;

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
 * Get the group menu name from a group.
 *
 * @MigrateProcessPlugin(
 *   id = "og_menu_name",
 *   source_module = "og_menu"
 * )
 */
class OgMenuName extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs a Migrate Process Plugin object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity Type manager Interface.
   */
  public function __construct(array $configuration, string $plugin_id, mixed $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
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
    $migrate_database = Database::getConnection('default', 'migrate');
    $migrate_query = $migrate_database->select('og_menu', 'ogmenu');
    $migrate_query->fields('ogmenu', ['gid']);
    $migrate_query->condition('ogmenu.menu_name', $value);
    $og_id = $migrate_query->execute()->fetchField();
    if ($og_id !== FALSE) {
      /** @var \Drupal\group\Entity\Storage\GroupStorage $group_storage */
      $group_storage = $this->entityTypeManager->getStorage('group');
      /** @var \Drupal\group\Entity\Group $group */
      $group = $group_storage->load($og_id);
      $group_menu_content = group_content_menu_get_menus_per_group($group);
      $group_menu_content = reset($group_menu_content);
      $group_content_menu_id = $group_menu_content->get('entity_id')
        ->first()
        ->getValue()['target_id'];
      return GroupContentMenuInterface::MENU_PREFIX . $group_content_menu_id;
    }
    throw new MigrateSkipRowException(sprintf('No group found for OG Menu %s.', $value));
  }

}
