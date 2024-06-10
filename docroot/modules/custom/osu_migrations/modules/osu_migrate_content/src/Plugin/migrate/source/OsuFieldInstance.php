<?php

namespace Drupal\osu_migrate_content\Plugin\migrate\source;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\field\Plugin\migrate\source\d7\FieldInstance;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * @MigrateSource(
 *   id = "osu_d7_field_instance",
 *   source_module = "field"
 * )
 */
class OsuFieldInstance extends FieldInstance {

  /**
   * What Bundles to exclude.
   *
   * @var array
   */
  protected array $excludeBundles;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
    $this->excludeBundles = isset($configuration['exclude_bundle']) ? (array) $configuration['exclude_bundle'] : [];
  }

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = parent::query();
    if (!empty($this->excludeBundles)) {
      $query->condition('fci.bundle', $this->excludeBundles, 'NOT IN');
    }
    return $query;
  }

}
