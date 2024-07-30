<?php

namespace Drupal\osu_migrations_cla\Plugin\migrate\process;

use Drupal\layout_builder\Section;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\paragraphs_to_layout_builder\LayoutMigrationItem;
use Drupal\paragraphs_to_layout_builder\Plugin\migrate\process\ParagraphsLayout;

/**
 * Paragraphs Layout process plugin for the SeaGrant site.
 *
 * @code
 * layout_builder__layout:
 *   plugin: cla_2col_combo_paragraphs_layout
 *   source_field: field_paragraphs
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "cla_2col_combo_paragraphs_layout"
 * )
 */
class ClaTwoColComboLayout extends ParagraphsLayout {

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $sourceField = $this->configuration['source_field'];
    $values = $row->getSourceProperty($sourceField);
    $map = $row->getSource()['constants']['map'];
    $sections = [];
    if (is_array($values)) {
      foreach ($values as $delta => $item) {
        $type = $this->getParagraphType($item['value']);
        $sectionType = $this->getSectionType($type);
        $section = $this->createSection($sectionType, []);
        $migration_ids = [];
        if ($type == "2_column_combo") {
          $migration_ids[$map['2_column_combo_left']] = "blb_region_col_1";
          $migration_ids[$map['2_column_combo_right']] = "blb_region_col_2";
          foreach ($migration_ids as $migration_id => $migration_row) {
            $migrationItem = new LayoutMigrationItem($type, $item['value'], $delta, $migration_id);
            $components = $this->createComponent($migrationItem, $section, $migration_row);
            $this->appendComponentsToSection($components, $section);
          }
          $sections[] = $section;
          return $sections;
        }
      }
    }
    return parent::transform($value, $migrate_executable, $row, $destination_property);
  }

  /**
   * Maps paragraph bundle type to bootstrap layout builder section type.
   *
   * @param string $paragraphType
   *   Name of the paragraph bundle.
   *
   * @return string
   *   Name of the layout builder section
   */
  public static function getSectionType(string $paragraphType): string {
    /*
     * Paragraph Types of:
     *   paragraph_1_col_clean
     *   paragraph_divider
     *   paragraph_accordion
     *   paragraph_alert
     * all map to bootstrap_layout_builder:blb_col_1 along with any other
     * paragraph type not listed here specifically.
     */
    return match ($paragraphType) {
      "paragraph_1_col",
      "paragraph_3_col" => "bootstrap_layout_builder:blb_col_3",
      "paragraph_menu", "grid_layout" => "bootstrap_layout_builder:blb_col_4",
      "paragraph_2_col", "2_column_combo" => "bootstrap_layout_builder:blb_col_2",
      default => "bootstrap_layout_builder:blb_col_1"
    };
  }

}
