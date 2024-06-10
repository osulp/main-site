<?php

namespace Drupal\osu_migrations\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'MigratedPathLookup' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "osu_migrate_path_lookup"
 * )
 */
class OsuMigratePathLookup extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The Migration Lookup Interface.
   *
   * @var \Drupal\migrate\MigrateLookupInterface
   */
  private MigrateLookupInterface $lookup;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrateLookupInterface $lookup) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->lookup = $lookup;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('migrate.lookup'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $matches = [];
    if (preg_match('/^(node)\/(\d+)$/', $value, $matches)) {
      $id = $matches[2];
      $base_path = $matches[1];
      $migrations = [
        'book_to_page',
        'page_to_page',
      ];
      $migration_lookup = $this->lookup->lookup($migrations, [$id]);

      if (!empty($migration_lookup)) {
        $new_id = reset($migration_lookup)['nid'];
        $value = $base_path . '/' . $new_id;
      }
    }

    return $value;
  }

}
