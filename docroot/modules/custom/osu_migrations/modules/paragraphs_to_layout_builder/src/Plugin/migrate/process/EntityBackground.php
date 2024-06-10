<?php

namespace Drupal\paragraphs_to_layout_builder\Plugin\migrate\process;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity Background Process plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "entity_background"
 * )
 */
class EntityBackground extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The database connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private Connection $migrateDb;

  /**
   * The Drupal migrate lookup service.
   *
   * @var \Drupal\migrate\MigrateLookupInterface
   */
  private MigrateLookupInterface $migrateLookup;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrateLookupInterface $migrateLookup) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migrateDb = Database::getConnection('default', 'migrate');
    $this->migrateLookup = $migrateLookup;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('migrate.lookup'));
  }

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $entity_background_id = $value['value'];
    // Check to see which type of background we have for the media item.
    $query = $this->migrateDb->select('field_data_eb_selection', 'fdebs');
    $query->fields('fdebs', ['eb_selection_value']);
    $query->condition('fdebs.entity_id', $entity_background_id);
    $eb_bg_type = $query->execute()->fetchField();
    $eb_selection = '';
    switch ($eb_bg_type) {
      // Normal Image.
      case 'group_eb_image':
        $image_query = $this->migrateDb->select('field_data_field_eb_image', 'fdfebi');
        $image_query->fields('fdfebi', ['field_eb_image_fid']);
        $image_query->condition('fdfebi.entity_id', $entity_background_id);
        $image_fid = [$image_query->execute()->fetchField()];
        $eb_selection = 'image';
        break;

      // Parallax Image.
      case 'group_eb_parallax':
        $parallax_query = $this->migrateDb->select('field_data_field_eb_parallax_image', 'fdfebpi');
        $parallax_query->fields('fdfebpi', ['field_eb_parallax_image_fid']);
        $parallax_query->condition('fdfebpi.entity_id', $entity_background_id);
        $image_fid = [$parallax_query->execute()->fetchField()];
        $eb_selection = 'parallax';
        break;

      default:
        $image_fid = NULL;
    }
    if (!is_null($image_fid)) {
      // If we got an id perform a lookup and return the new media id.
      try {
        $media_id = $this->migrateLookup->lookup('upgrade_d7_media_images', $image_fid);
      }
      catch (PluginException $e) {
        return NULL;
      }
      catch (MigrateException $e) {
        throw $e;
      }
      return reset($media_id)['mid'] . ",$eb_selection";
    }
    return $value;
  }

}
