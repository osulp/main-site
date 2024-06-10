<?php

namespace Drupal\paragraphs_to_layout_builder\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\paragraphs_to_layout_builder\Exception\LayoutMigrationMissingBlockException;
use Drupal\paragraphs_to_layout_builder\Exception\LayoutMigrationMissingParagraphToLayoutException;
use Drupal\paragraphs_to_layout_builder\LayoutBase;
use Drupal\paragraphs_to_layout_builder\LayoutMigrationItem;

/**
 * Paragraphs Layout process plugin.
 *
 * @code
 * layout_builder__layout:
 *   plugin: layout_builder_layout
 *   source_field: field_paragraphs
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "paragraphs_layout"
 * )
 */
class ParagraphsLayout extends LayoutBase {

  /**
   * Transform paragraph source values into a Layout Builder sections.
   *
   * @param mixed $value
   *   The value to be transformed.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration in which this process is being executed.
   * @param \Drupal\migrate\Row $row
   *   The row from the source to process. Normally, just transforming the value
   *   is adequate but very rarely you might need to change two columns at the
   *   same time or something like that.
   * @param string $destination_property
   *   The destination property currently worked on. This is only used together
   *   with the $row above.
   *
   * @return \Drupal\layout_builder\Section[]
   *   A Layout Builder Section object populated with Section Components.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\migrate\MigrateException
   */
  public function transform($value,
    MigrateExecutableInterface $migrate_executable,
    Row $row,
    $destination_property
  ) {
    $sourceField = $this->configuration['source_field'];
    if (!isset($sourceField)) {
      throw new MigrateException('Missing source_field for paragraph layout process plugin.');
    }

    $values = $row->getSourceProperty($sourceField);
    $map = $row->getSource()['constants']['map'];
    $sections = [];

    if (is_array($values)) {
      foreach ($values as $delta => $item) {
        try {
          $type = $this->getParagraphType($item['value']);
          $sectionType = $this->getSectionType($type);
          $section = $this->createSection($sectionType, []);

          // Map migration IDs to their layout builder region.
          $migration_ids = [];
          if ($type == "paragraph_2_col") {
            $migration_ids[$map['paragraph_2_col_left']] = "blb_region_col_1";
            $migration_ids[$map['paragraph_2_col_right']] = "blb_region_col_2";
          }
          elseif ($type == "paragraph_3_col") {
            $migration_ids[$map['paragraph_3_col_left']] = "blb_region_col_1";
            $migration_ids[$map['paragraph_3_col_center']] = "blb_region_col_2";
            $migration_ids[$map['paragraph_3_col_right']] = "blb_region_col_3";
          }
          elseif (array_key_exists($type, $map)) {
            $migration_ids[$map[$type]] = "blb_region_col_1";
          }
          else {
            throw new LayoutMigrationMissingParagraphToLayoutException($this->t('Missing custom paragraph migration for paragraph type @type.', ['@type' => $type]));
          }
          // Iterate through migration_ids creating components for each block and attaching to section.
          foreach ($migration_ids as $migration_id => $migration_row) {
            $migrationItem = new LayoutMigrationItem($type, $item['value'], $delta, $migration_id);
            $components = $this->createComponent($migrationItem, $section, $migration_row);

            // Limitations on menu migrations means we don't know what section type to use until now.
            if ($components[0]->get('configuration')['id'] == 'inline_block:osu_menu_bar_item') {
              // Query old db to get the menu bg color option.
              $menu_style_query = $this->migrateDb->select('field_data_field_p_menu_styles', 'fdfpms');
              $menu_style_query->fields('fdfpms', ['field_p_menu_styles_value']);
              $menu_style_query->condition('fdfpms.entity_id', $item['value'], 'IN');
              $menu_bg_color = $menu_style_query->execute()->fetchField();

              $menu_section_settings = $this->setMenuBgClass($menu_bg_color);
              $section = $this->createSection('bootstrap_layout_builder:blb_col_' . count($components), [], $menu_section_settings);
            }

            foreach ($components as $component) {
              $section->appendComponent($component);
            }
          }

          $sections[] = $section;
        }
        catch (LayoutMigrationMissingBlockException $e) {
          $this->handleMissingBlockException($migrate_executable, $e);
          continue;
        }
        catch (LayoutMigrationMissingParagraphToLayoutException $e) {
          $migrate_executable->saveMessage($e->getMessage(), $e->getCode());
          if ($migrate_executable instanceof MigrateExecutable) {
            $migrate_executable->message->display($e->getMessage());
          }
          continue;
        }
      }
    }

    return $sections;
  }

  /**
   * Gets the type of paragraph given a paragraph id.
   *
   * Uses basic static caching since this may be called multiple times for the
   * same paragraphs.
   *
   * @param string $id
   *   The paragraph id.
   *
   * @return string
   *   The paragraph bundle.
   */
  public function getParagraphType($id) {
    $types = &drupal_static(__FUNCTION__);
    if (!isset($types[$id])) {
      $query = $this->migrateDb->select('paragraphs_item', 'p');
      $query->fields('p', ['bundle']);
      $query->condition('p.item_id', $id, '=');
      $types[$id] = $query->execute()->fetchField();
    }
    return $types[$id];
  }

  /**
   * Set the Menu bar section options.
   *
   * @param string $paragraph_style
   *
   * @return array
   *   Layout builder Section settings.
   */
  private function setMenuBgClass(string $paragraph_style) {
    $menu_section_settings = [
      'container' => 'container',
      'container_wrapper' => [
        'bootstrap_styles' => [
          'background' => [
            'background_type' => 'color',
          ],
        ],
      ],
    ];
    switch ($paragraph_style) {
      case 'menu-orange':
        $menu_section_settings['container_wrapper']['bootstrap_styles']['background_color']['class'] = 'osu-bg-osuorange';
        $menu_section_settings['container_wrapper']['bootstrap_styles']['text_color']['class'] = 'osu-text-bucktoothwhite';
        break;

      case 'menu-gray':
        $menu_section_settings['container_wrapper']['bootstrap_styles']['background_color']['class'] = 'osu-bg-light-grey';
        break;

      case 'menu-blue':
        $menu_section_settings['container_wrapper']['bootstrap_styles']['background_color']['class'] = 'osu-bg-moondust';
        break;

      case 'menu-black':
        $menu_section_settings['container_wrapper']['bootstrap_styles']['background_color']['class'] = 'osu-bg-page-alt-2';
        $menu_section_settings['container_wrapper']['bootstrap_styles']['text_color']['class'] = 'osu-text-bucktoothwhite';
        break;

      case 'menu-green':
        $menu_section_settings['container_wrapper']['bootstrap_styles']['background_color']['class'] = 'osu-bg-crater';
        $menu_section_settings['container_wrapper']['bootstrap_styles']['text_color']['class'] = 'osu-text-bucktoothwhite';
        break;

      default:
        $menu_section_settings['container_wrapper']['bootstrap_styles']['background_color']['class'] = 'osu-bg-page-default';
        break;
    }
    return $menu_section_settings;
  }

}
