<?php

namespace Drupal\paragraphs_to_layout_builder\Plugin\migrate\process;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Process plugin to just return the new fid of the file for a background.
 *
 * The migration configuration 'custom_source_lookup' is required.
 *
 * @MigrateProcessPlugin(
 *   id = "file_background"
 * )
 *
 * Examples:
 *   field_eb_background_fc:
 *     plugin: file_background
 *     source: field_p_2_col_left_bg
 *     custom_source_lookup: upgrade_d7_media_images
 *
 * @code
 *
 * @endcode
 */
class FileBackground extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The Migration Lookup Interface.
   *
   * @var \Drupal\migrate\MigrateLookupInterface
   */
  private MigrateLookupInterface $migrateLookup;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrateLookupInterface $migrateLookup) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
    $source_fid = $value['fid'];
    $lookup_source_id = $this->configuration['custom_source_lookup'];
    try {
      $destination_fid = $this->migrateLookup->lookup($lookup_source_id, [$source_fid]);
    }
    catch (PluginException $e) {
      return NULL;
    }
    catch (MigrateException $e) {
      throw $e;
    }
    return reset($destination_fid)['mid'];
  }

}
