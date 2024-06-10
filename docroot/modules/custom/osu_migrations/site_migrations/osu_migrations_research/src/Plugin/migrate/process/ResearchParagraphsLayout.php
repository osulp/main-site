<?php

namespace Drupal\osu_migrations_research\Plugin\migrate\process;

use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;
use Drupal\paragraphs_to_layout_builder\LayoutMigrationItem;
use Drupal\paragraphs_to_layout_builder\Plugin\migrate\process\ParagraphsLayout;

/**
 * Paragraphs Layout process plugin for the Research site.
 *
 * @code
 * layout_builder__layout:
 *   plugin: research_paragraphs_layout
 *   source_field: field_paragraphs
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "research_paragraphs_layout"
 * )
 */
class ResearchParagraphsLayout extends ParagraphsLayout {

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
      "paragraph_menu", "par_image_grid", "grid" => "bootstrap_layout_builder:blb_col_4",
      "paragraph_2_col" => "bootstrap_layout_builder:blb_col_2",
      default => "bootstrap_layout_builder:blb_col_1"
    };
  }

  /**
   * Creates a component from a paragraph.
   *
   * @param \Drupal\paragraphs_to_layout_builder\LayoutMigrationItem $item
   *   A migration item instance.
   * @param \Drupal\layout_builder\Section $section
   *   The layout builder section this block will be applied to.
   * @param string $row
   *   The region the component belongs within.
   *
   * @return \Drupal\layout_builder\SectionComponent[]
   *   A Layout Builder SectionComponent.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\migrate\MigrateException
   * @throws \Drupal\paragraphs_to_layout_builder\Exception\LayoutMigrationMissingBlockException
   */
  public function createComponent(LayoutMigrationItem $item, Section $section, string $row = 'blb_region_col_1') {
    if ($item->getMigrationId() === 'paragraph_image_grid__to__layout_builder' || $item->getMigrationId() === 'paragraph_grid__to__layout_builder') {
      $block_id = $this->lookupBlock($item->getMigrationId(), $item->getId());
      /** @var \Drupal\block_content\Entity\BlockContent $block */
      $block = $this->entityTypeManager->getStorage('block_content')
        ->load($block_id);
      return $this->handleGridLayoutItems($block, $item);
    }
    return parent::createComponent($item, $section, $row);
  }

  /**
   * Additional blocks need to be queried for Grid Layout.
   *
   * @param \Drupal\block_content\Entity\BlockContent $block
   *   The block containing IDs of the Grid Item blocks.
   * @param \Drupal\paragraphs_to_layout_builder\LayoutMigrationItem $item
   *   A migration item instance.
   *
   * @return array
   *   layout builder block settings array
   */
  protected function handleGridLayoutItems($block, LayoutMigrationItem $item) {
    $block_ids = explode(',', $block->get('body')->value);
    $components = [];
    foreach ($block_ids as $index => $block_id) {
      $query = $this->db->select('block_content_field_data', 'b')
        ->fields('b', ['type'])
        ->condition('b.id', $block_id, '=');
      $block_type = $query->execute()->fetchField();
      $block_revision_id = $this->blockContentStorage->getLatestRevisionId($block_id);
      // Using mod 4 and adding 1 we should always return column 1-4.
      $row = 'blb_region_col_' . ($index % 4 + 1);
      $additional = $this->getAdditionalBlockSettings($block, $row, $item);
      $components[] = $this->createSectionComponent($block_type, $block_revision_id, $row, $additional, $index);
    }
    return $components;
  }

  /**
   * Retrieves additional block settings for the layout builder.
   *
   * This method retrieves additional settings for the block based on the
   * provided parameters. If the Migration Item is a custom grid_layout,
   * specific settings for the block are returned. Otherwise, the parent method
   * is called to retrieve the additional settings.
   *
   * @param \Drupal\block_content\Entity\BlockContent|\Drupal\Core\Block\BlockPluginInterface $block
   *   The block plugin instance.
   * @param string $row
   *   The row where the block is placed.
   * @param \Drupal\paragraphs_to_layout_builder\LayoutMigrationItem $item
   *   The layout migration item.
   *
   * @return array
   *   The additional block settings.
   */
  protected function getAdditionalBlockSettings($block, string $row, LayoutMigrationItem $item) {
    if ($item->getType() === 'par_image_grid' || $item->getType() === 'grid') {
      return [
        'bootstrap_styles' => [
          'block_style' => [
            'background' => ['background_type' => 'color'],
            'background_color' => ['class' => '_none'],
            'padding' => ['class' => '_none'],
            'padding_left' => ['class' => '_none'],
            'padding_top' => ['class' => 'pt-4'],
            'padding_right' => ['class' => '_none'],
            'padding_bottom' => ['class' => '_none'],
            'margin' => ['class' => '_none'],
          ],
        ],
      ];
    }
    return parent::getAdditionalBlockSettings($block, $row, $item);
  }

  /**
   * Creates a section component for the layout builder.
   *
   * This method creates a section component based on the provided parameters.
   * If the block type is 'osu_card', additional customization logic can be
   * implemented here.
   *
   * @param string $block_type
   *   The type of the block.
   * @param int $block_latest_revision_id
   *   The latest revision ID of the block.
   * @param int $row
   *   The row where the section component will be placed.
   * @param array $additional
   *   Additional parameters for creating the section component.
   * @param int $weight
   *   The weight of the section component within the row. (Default: 0)
   *
   * @return \Drupal\layout_builder\SectionComponent
   *   The created section component.
   */
  public function createSectionComponent($block_type, $block_latest_revision_id, $row, array $additional, $weight = 0) {
    if ($block_type === 'osu_card') {
      return SectionComponent::fromArray([
        'uuid' => $this->uuid->generate(),
        'region' => $row,
        'configuration' => [
          'id' => "inline_block:{$block_type}",
          'label' => 'Layout Builder Inline Block',
          'provider' => 'layout_builder',
          'label_display' => '0',
          'view_mode' => 'full_image',
          'block_revision_id' => $block_latest_revision_id,
          'block_serialized' => NULL,
          'context_mapping' => [],
        ],
        'additional' => $additional,
        'weight' => $weight,
      ]);
    }
    return parent::createSectionComponent($block_type, $block_latest_revision_id, $row, $additional, $weight);
  }

}
