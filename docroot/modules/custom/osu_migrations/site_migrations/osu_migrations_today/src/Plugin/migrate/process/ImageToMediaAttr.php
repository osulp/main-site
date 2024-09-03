<?php

namespace Drupal\osu_migrations_today\Plugin\migrate\process;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateLookupInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Transform a value representing an image to a media attribute.
 *
 * This process plugin is used to transform an image value into a media
 * attribute. It loads the specified image file, updates its title and alt
 * attributes if necessary, and returns the ID of the media entity.
 *
 * @MigrateProcessPlugin(
 *   id = "img_media_attr",
 *   source_module = "image",
 * )
 */
class ImageToMediaAttr extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\migrate\MigrateLookupInterface
   */
  private MigrateLookupInterface $migrateLookup;

  /**
   * Constructs a Migrate Process Plugin object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity Type manager Interface.
   */
  public function __construct(array $configuration, string $plugin_id, mixed $plugin_definition, EntityTypeManagerInterface $entityTypeManager, MigrateLookupInterface $migrateLookup) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->migrateLookup = $migrateLookup;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('migrate.lookup')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $source_fid = $value['fid'];
    $source_title = $value['title'];
    $source_alt = $value['alt'];
    /** @var \Drupal\media\MediaStorage $meida_storage */
    $meida_storage = $this->entityTypeManager->getStorage('media');
    /** @var \Drupal\file\FileStorage $file_storage */
    $file_storage = $this->entityTypeManager->getStorage('file');
    $lookup_result = $this->migrateLookup->lookup('upgrade_d7_media_images', [$source_fid]);
    if (!empty($lookup_result)) {
      $media = $meida_storage->load(reset($lookup_result)['mid']);
      if ($media) {
        // Load the file referenced by the media item.
        if ($media->hasField('field_media_image') &&
          !$media->get('field_media_image')->isEmpty()) {
          $file_id = $media->get('field_media_image')->target_id;
          $file = $file_storage->load($file_id);
          if ($file) {
            // Update the title and alt if necessary.
            $field_media_image = $media->get('field_media_image');

            if (!empty($source_title)) {
              if (empty($field_media_image->title)) {
                $field_media_image->title = $source_title;
              }
              if (empty($field_media_image->alt)) {
                $field_media_image->alt = $source_alt ?: (strlen($source_title) > 512 ? substr($source_title, 0, 512) : $source_title);
              }
            }
            elseif (empty($field_media_image->alt) && !empty($source_alt)) {
              $field_media_image->alt = $source_alt;
            }
            // Save the updated media entity.
            $media->save();
          }
        }
        return $media->id();
      }
    }
  }

}
