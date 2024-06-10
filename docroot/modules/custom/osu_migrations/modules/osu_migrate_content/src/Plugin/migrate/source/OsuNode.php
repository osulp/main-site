<?php

namespace Drupal\osu_migrate_content\Plugin\migrate\source;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\node\Plugin\migrate\source\d7\Node;

/**
 * @MigrateSource(
 *   id = "osu_d7_node",
 *   source_module = "node"
 * )
 */
class OsuNode extends Node {

  /**
   * Node Bundles to exclude from migration.
   *
   * @var array
   */
  protected array $excludeBundle;

  /**
   *
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    if (empty($configuration['exclude_bundle'])) {
      throw new \InvalidArgumentException('osu_d7_node missing exclude_bundle property.');
    }
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager, $module_handler);
    $this->excludeBundle = (array) $configuration['exclude_bundle'];
  }

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = parent::query();
    if (!empty($this->excludeBundle)) {
      $query->condition('n.type', $this->excludeBundle, 'NOT IN');
    }
    return $query;
  }

}
