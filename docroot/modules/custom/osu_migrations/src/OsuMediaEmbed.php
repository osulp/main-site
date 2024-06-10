<?php

namespace Drupal\osu_migrations;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Template\Attribute;
use Drupal\migrate\MigrateLookupInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

/**
 * Replace the old WYSIWYG Embed code with the new Media Embed code.
 */
class OsuMediaEmbed {

  /**
   * The migrate.lookup service.
   *
   * @var \Drupal\migrate\MigrateLookupInterface
   */
  protected $lookup;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs an OsuMediaEmbed object.
   *
   * @param \Drupal\migrate\MigrateLookupInterface $lookup
   *   The migrate.lookup service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(MigrateLookupInterface $lookup, EntityTypeManagerInterface $entity_type_manager) {
    $this->lookup = $lookup;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Parse the string and replace the old fid embed with the new media embed.
   *
   * @param string $value
   *   The Body value to check for and replace the Drupal 7 Embed Code.
   *
   * @return string
   *   The full processed body value with either the new embed code or
   *   unchanged.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\migrate\MigrateException
   */
  public function transformEmbedCode(string $value) {
    // Find our old encoded data and save it a capture group called tag_info.
    $pattern = '/\[\[\s*(?<tag_info>\{.+\})\s*\]\]/sU';
    // If we can use associative array use it.
    if (defined(JsonDecode::class . '::ASSOCIATIVE')) {
      $decoder = new JsonDecode([JsonDecode::ASSOCIATIVE => TRUE]);
    }
    else {
      $decoder = new JsonDecode(TRUE);
    }
    $text = preg_replace_callback($pattern, function ($matches) use ($decoder) {
      // Find 2 or more consecutive spaces and replace it with one.
      $matches['tag_info'] = preg_replace('/\s+/', ' ', $matches['tag_info']);
      try {
        $tag_info = $decoder->decode($matches['tag_info'], JsonEncoder::FORMAT);
        if (!is_array($tag_info) || !array_key_exists('fid', $tag_info)) {
          return $matches[0];
        }
        // Get the ID and view mode.
        $embed_metadata = [
          'id' => $tag_info['fid'],
          'view_mode' => $tag_info['view_mode'] ?? 'default',
        ];
        // Check to see if we have attributes and if not create an empty array.
        $source_attributes = !empty($tag_info['attributes']) ? $tag_info['attributes'] : [];
        // Add alt and title overrides.
        foreach (['alt', 'title'] as $attribute_name) {
          if (!empty($source_attributes[$attribute_name])) {
            $embed_metadata[$attribute_name] = $source_attributes[$attribute_name];
          }
        }

        // Get the alignment classes.
        if (!empty($source_attributes['class']) && is_string($source_attributes['class'])) {
          $classes_arr = array_unique(explode(' ', preg_replace('/\s{2,}/', ' ', trim($source_attributes['class']))));
          $old_alignment = [
            'media-wysiwyg-align-center' => 'center',
            'media-wysiwyg-align-left' => 'left',
            'media-wysiwyg-align-right' => 'right',
          ];
          foreach ($old_alignment as $old => $new) {
            if (in_array($old, $classes_arr, TRUE)) {
              $embed_metadata['data-align'] = $new;
            }
          }
        }
        return $this->getEmbedCode($embed_metadata) ?? $matches[0];
      }
      catch (NotEncodableValueException $e) {
        return $matches[0];
      }
      catch (\LogicException $e) {
        return $matches[0];
      }
    }, $value);
    return $text;
  }

  /**
   * Get the new drupal media embed code.
   *
   * @param array $embedMetadata
   *   An array of media data.
   *
   * @return string|null
   *   Either return the new media embed code or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\migrate\MigrateException
   */
  private function getEmbedCode(array $embedMetadata) {
    if (empty($embedMetadata['id']) || empty($embedMetadata['view_mode'])) {
      return NULL;
    }
    // Get the New media ID, could be in any one of these migration.
    $newMid = $this->lookup->lookup([
      'upgrade_d7_media_audio',
      'upgrade_d7_media_documents',
      'upgrade_d7_media_images',
      'upgrade_d7_media_kaltura',
      'upgrade_d7_media_local_video',
      'upgrade_d7_media_remote_video',
    ], [$embedMetadata['id']]);
    if (empty($newMid)) {
      return NULL;
    }
    // Lookup returns a nested array, we only need the id.
    $newMid = reset($newMid)['mid'];
    /** @var \Drupal\media\Entity\Media $mediaEntity */
    $mediaEntity = $this->entityTypeManager->getStorage('media')
      ->load($newMid);
    // Get the UUID of the media object.
    $mediaEntityUuid = $mediaEntity->uuid();

    $attributes = [];
    $attributes['data-entity-type'] = 'media';
    $attributes['data-entity-uuid'] = $mediaEntityUuid;
    $attributes['data-view-mode'] = 'default';
    // Alt, title, caption and align should be handled conditionally.
    $conditionalAttributes = ['alt', 'title', 'data-caption', 'data-align'];
    foreach ($conditionalAttributes as $conditionalAttribute) {
      if (!empty($embedMetadata[$conditionalAttribute])) {
        $attributes[$conditionalAttribute] = $embedMetadata[$conditionalAttribute];
      }
    }
    /** @var \Drupal\Core\Template\Attribute $attribute */
    $attribute = new Attribute($attributes);
    return "<drupal-media {$attribute->__toString()}></drupal-media>";
  }

}
