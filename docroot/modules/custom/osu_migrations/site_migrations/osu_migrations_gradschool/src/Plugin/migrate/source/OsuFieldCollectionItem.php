<?php

namespace Drupal\osu_migrations_gradschool\Plugin\migrate\source;

use Drupal\paragraphs\Plugin\migrate\source\d7\FieldCollectionItem;

/**
 * Field Collection Item source plugin.
 *
 * Custom plugin to limit on a bundle.
 *
 * Available configuration keys:
 * - field_name: (optional) If supplied, this will only return field collections
 *   of that particular type.
 * - bundle: (optional) if supplied, this will limit to the bundle.
 *
 * @MigrateSource(
 *   id = "d7_osu_field_collection_item",
 *   source_module = "field_collection",
 * )
 */
class OsuFieldCollectionItem extends FieldCollectionItem {

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = parent::query();
    if ($this->configuration['field_name']) {
      $query->condition('fc.bundle', $this->configuration['bundle']);
    }
    return $query;
  }

}
