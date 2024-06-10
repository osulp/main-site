<?php

namespace Drupal\paragraphs_to_layout_builder;

/**
 * Represents a layout migration item.
 *
 * Stores structured data for a layout migration. Contains type, id, delta, and
 * migration properties.
 *
 * @package Drupal\d8_migration
 */
class LayoutMigrationItem {

  /**
   * The item type or bundle.
   *
   * @var string
   */
  protected $type;

  /**
   * The entity id.
   *
   * @var int
   */
  protected $id;

  /**
   * The delta (weight) of the item from the source field.
   *
   * @var int
   */
  protected $delta;

  /**
   * The migration id for this item.
   *
   * @var string
   */
  protected $migrationId;

  /**
   * LayoutMigrationItem constructor.
   *
   * @param string $type
   *   The paragraph bundle.
   * @param int $id
   *   The paragraph id.
   * @param string $delta
   *   The paragraph field item delta.
   * @param string $migration_id
   *   The migration related to this item.
   */
  public function __construct($type, $id, $delta, $migration_id) {
    $this->type = $type;
    $this->id = $id;
    $this->delta = $delta;
    $this->migrationId = $migration_id;
  }

  /**
   * Gets the id property.
   *
   * @return int
   *   The id property.
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Gets the migration_id property.
   *
   * @return string
   *   The migration_id property.
   */
  public function getMigrationId() {
    return $this->migrationId;
  }

  /**
   * Gets the delta property.
   *
   * @return int
   *   The delta property.
   */
  public function getDelta() {
    return $this->delta;
  }

  /**
   * Gets the type property.
   *
   * @return string
   *   The type property.
   */
  public function getType() {
    return $this->type;
  }

}
