<?php

namespace Drupal\paragraphs_to_layout_builder\Plugin\migrate\process;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Drupal\osu_migrations\OsuMediaEmbed;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs_to_layout_builder\LayoutBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom plugin for handling paragraph accordion items from d7.
 *
 * @MigrateProcessPlugin(
 *   id = "accordion_item",
 *   handle_multiples = TRUE
 * )
 */
class AccordionItem extends LayoutBase {

  /**
   * The OSU Media Embed Service.
   *
   * @var \Drupal\osu_migrations\OsuMediaEmbed
   */
  private OsuMediaEmbed $osuMediaEmbed;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, UuidInterface $uuid, Connection $db, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory, MigrateLookupInterface $migrateLookup, OsuMediaEmbed $osuMediaEmbed) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $uuid, $db, $entityTypeManager, $configFactory, $migrateLookup);
    $this->osuMediaEmbed = $osuMediaEmbed;
  }

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $headerText = $value[0];
    $accordionItemIds = $value[1];
    if (!empty($accordionItemIds)) {
      $d7_accordions = $this->getAccordionItems($accordionItemIds);

      // Create accordion items using title and body from d7.
      $paragraph_items = [];
      foreach ($d7_accordions as $accordion) {
        // Get the current value.
        $bodyValue = $accordion->field_p_accordion_group_content_value;
        // Pass it to our service to get the new embed value.
        $transformedEmbedCode = $this->osuMediaEmbed->transformEmbedCode($bodyValue);

        $paragraph_items[] = Paragraph::create([
          'type' => 'osu_accordion_item',
          'field_p_accordion_title' => $accordion->field_p_accordion_group_title_value,
          'field_p_accordion_body' => [
            'value' => $transformedEmbedCode,
            'format' => 'full_html',
          ],
        ]);
      }

      // Create accordion section and attach accordion items.
      $paragraph_section = Paragraph::create([
        'type' => 'osu_accordion_section',
        'field_p_accordion_heading' => $headerText,
        'field_osu_paragraph_item' => $paragraph_items,
      ]);

      // Return accordion section which gets attached to the block created by the
      // migration.
      return $paragraph_section;
    }
    return $value;
  }

  /**
   * Query Migration source database for all Paragraph Accordion Bundles.
   *
   * @param mixed $value
   *   The id of the paragraph.
   *
   * @return \Drupal\Core\Database\StatementInterface|null
   *   A prepared statement, or NULL if the query is not valid.
   */
  private function getAccordionItems($value) {
    $entity_ids = [];
    $revision_ids = [];
    foreach ($value as $id) {
      $entity_ids[] = $id['value'];
      $revision_ids[] = $id['revision_id'];
    }

    $query = $this->migrateDb->select('field_data_field_p_accordion_group_title', 'title');
    $query->leftJoin(
      'field_revision_field_p_accordion_group_content',
      'content',
      'title.entity_id = content.entity_id && title.revision_id = content.revision_id'
    );
    $query->fields('title', ['field_p_accordion_group_title_value']);
    $query->fields('content', ['field_p_accordion_group_content_value']);
    $query->condition('title.entity_id', $entity_ids, 'IN');
    $query->condition('title.revision_id', $revision_ids, 'IN');
    return $query->execute();
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('uuid'),
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('migrate.lookup'),
      $container->get('osu_migrations.osu_media_embed')
    );
  }

}
