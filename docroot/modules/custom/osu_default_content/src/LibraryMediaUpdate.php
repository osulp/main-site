<?php

namespace Drupal\osu_default_content;

use Drupal\Component\Uuid\Uuid;

/**
 * Update the Media used in Backgrounds for Layout Builder Section Library.
 */
class LibraryMediaUpdate {

  /**
   * Update the Background Setting for the Section.
   *
   * @param string $uuidSectionLibrary
   *   The UUID of the section from the Library.
   * @param string|null $uuidSectionBlock
   *   (Optional) The UUID of the Block in the Components section of the Layout
   *   Item.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Exception
   */
  public static function updateSectionLibrarySectionBackground(string $uuidSectionLibrary, string $uuidSectionBlock = NULL): void {
    if (!Uuid::isValid($uuidSectionLibrary)) {
      throw new \Exception('Bad UUID provided for Section Library Item.');
    }
    $section_storage = \Drupal::entityTypeManager()
      ->getStorage('section_library_template');
    /** @var array $section_library_items */
    $section_library_items = $section_storage->loadByProperties(['uuid' => $uuidSectionLibrary]);
    /** @var \Drupal\section_library\Entity\SectionLibraryTemplate $section_library_item */
    $section_library_item = reset($section_library_items);
    /** @var \Drupal\layout_builder\Plugin\Field\FieldType\LayoutSectionItem $layouts */
    $layouts = $section_library_item->get('layout_section')->first();
    /** @var \Drupal\layout_builder\Section $layout_section */
    $layout_section = $layouts->getValue()['section'];
    if (!is_null($uuidSectionBlock) && Uuid::isValid($uuidSectionBlock)) {
      $layout_component = $layout_section->getComponent($uuidSectionBlock);
      $layout_component_additional = $layout_component->get('additional');
      $layout_component_additional['bootstrap_styles']['block_style']['background_media']['image']['media_id'] = self::getMediaId();
      $layout_component->set('additional', $layout_component_additional);
    }
    else {
      $layout_section_settings = $layout_section->getLayoutSettings();
      $layout_section_settings['container_wrapper']['bootstrap_styles']['background_media']['image']['media_id'] = self::getMediaId();
      $layout_section->setLayoutSettings($layout_section_settings);
      $section_library_item->set('layout_section', $layout_section);
    }
    $section_library_item->save();
  }

  /**
   * Get the Media ID for our default image provided by the UUID.
   *
   * @return mixed
   *   The ID of the media object
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  private static function getMediaId(): mixed {
    /** @var \Drupal\media\MediaStorage $media_storage */
    $media_storage = \Drupal::entityTypeManager()->getStorage('media');
    /** @var array $media_item_arr */
    $media_item_arr = $media_storage->loadByProperties(['uuid' => 'ca47a269-1650-4593-8156-4d99fb97d293']);
    /** @var \Drupal\media\Entity\Media $media_item */
    $media_item = reset($media_item_arr);
    return $media_item->get('mid')->first()->getValue()['value'];
  }

}
