<?php

namespace Drupal\paragraphs_to_layout_builder\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Custom process plugin for handling class list field type in d7.
 *
 * Migrations view class list as Array of Arrays, this class concats them into
 * a single string.
 *
 * @MigrateProcessPlugin(
 *   id = "class_list",
 *   handle_multiples = TRUE
 * )
 */
class ClassList extends ProcessPluginBase {

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $result = "";
    foreach ($value as $val) {
      $result = $result . " " . $val['value'];
    }
    return $result;
  }

}
