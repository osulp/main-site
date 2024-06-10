<?php

namespace Drupal\paragraphs_to_layout_builder\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\paragraphs_to_layout_builder\LayoutBase;

/**
 * Process plugin to get the default layout of the node type.
 *
 * Example:
 * Get the default layout for the Node Bundle Page.
 *
 * @code
 * layout_temp:
 *   plugin: default_layout
 *   bundle: page
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "default_layout"
 * )
 */
class DefaultLayout extends LayoutBase {

  /**
   * {@inheritDoc}
   *
   * Get the default layout of the node type to be added in the migration.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $bundle = $this->configuration['bundle'];
    if ($bundle) {
      $sections = $this->loadDefaultSections($bundle);
      if (!empty($sections)) {
        return $sections;
      }
      else {
        return NULL;
      }
    }
    return $value;
  }

}
