<?php

namespace Drupal\osu_migrations_research\Plugin\migrate\process;

use Drupal\block_content\Entity\BlockContent;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\paragraphs_to_layout_builder\LayoutBase;

/**
 * Custom plugin for handling paragraph grid items from d7.
 *
 * @MigrateProcessPlugin(
 *   id = "grid_item",
 *   handle_multiples = TRUE
 * )
 */
class GridItemFc extends LayoutBase {

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Get entity ids for link data.
    $entity_ids = [];
    foreach ($value as $entity_id) {
      $entity_ids[] = $entity_id['value'];
    }
    $results = [];
    if (!empty($entity_ids)) {
      // Query migratedb for Grid Content.
      $query = $this->migrateDb->select('field_data_field_grid_content', 'gc');
      $query->fields('gc', ['field_grid_content_value']);
      $query->innerJoin('field_data_field_grid_item', 'gi', 'gi.field_grid_item_value = gc.entity_id');
      $query->condition('gc.entity_id', $entity_ids, 'IN');
      $query->orderBy('gi.delta');
      $results = $query->execute();
    }
    $block_ids = [];
    foreach ($results as $result) {
      $block = BlockContent::create([
        'info' => 'Migrated d7 paragraph grid content',
        'type' => 'paragraph_block',
        'langcode' => 'en',
        'body' => [
          'value' => $result->field_grid_content_value,
          'format' => 'full_html',
        ],
        'reusable' => 0,
      ]);
      $block->save();
      $block_ids[] = $block->id();
    }
    return implode(',', $block_ids);
  }

}
