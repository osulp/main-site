<?php

namespace Drupal\osu_migrate_content\Plugin\migrate\process;

use Drupal\block\Plugin\migrate\process\BlockPluginId;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "osu_block_plugin_id"
 * )
 */
class OsuBlockPluginId extends BlockPluginId {

  /**
   * {@inheritDoc}
   *
   * Run the parent transform but if that doesn't find a block look up our own
   * block lookup plugin and get the uuid of the block.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $parent_value = parent::transform($value, $migrate_executable, $row, $destination_property);
    if (is_null($parent_value) && is_array($value)) {
      [$module, $delta] = $value;
      switch ($module) {
        case 'block':
          if ($this->blockContentStorage) {
            $lookup_result = $this->migrateLookup->lookup([
              'upgrade_d7_custom_block',
            ], [$delta]);
            if ($lookup_result) {
              $block_id = $lookup_result[0]['id'];
              return 'block_content:' . $this->blockContentStorage->load($block_id)
                ->uuid();
            }
          }
          break;

        default:
          break;
      }
    }
    return $parent_value;
  }

}
