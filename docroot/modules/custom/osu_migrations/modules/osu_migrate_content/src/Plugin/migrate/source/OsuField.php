<?php

namespace Drupal\osu_migrate_content\Plugin\migrate\source;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\field\Plugin\migrate\source\d7\Field;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * Modified Field Source from Drupal 7.
 *
 * @MigrateSource(
 *   id = "osu_d7_field",
 *   source_module = "field_sql_storage"
 * )
 */
class OsuField extends Field {

  /**
   * What entity types to limit on.
   *
   * @var string
   */
  protected string $includeEntity;

  /**
   * What Bundles to exclude.
   *
   * @var array
   */
  protected array $excludeBundles;

  /**
   *
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
    $this->includeEntity = $configuration['entity_type'] ?? '';
    $this->excludeBundles = isset($configuration['exclude_bundle']) ? (array) $configuration['exclude_bundle'] : [];
  }

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = parent::query();
    if (!empty($this->includeEntity)) {
      $query->condition('fci.entity_type', $this->includeEntity);
    }
    if (!empty($this->excludeBundles)) {
      $query->condition('fci.bundle', $this->excludeBundles, 'NOT IN');
    }
    return $query;
  }

}
