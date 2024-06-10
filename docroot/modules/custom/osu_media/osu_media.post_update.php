<?php

/**
 * @file
 * Post Update file.
 */

use Drupal\Core\Config\FileStorage;

/**
 * Remove view mode for media_card_caption.
 */
function osu_media_post_update_remove_card_caption_display(&$sandbox) {
  /** @var \Drupal\Core\Config\Config $media_card_caption_image_display */
  $media_card_caption_image_display = \Drupal::service('config.factory')
    ->getEditable('core.entity_view_display.media.image.media_card_caption');
  $media_card_caption_image_display->delete();

  /** @var \Drupal\Core\Config\Config $media_card_caption_remote_video_display */
  $media_card_caption_remote_video_display = \Drupal::service('config.factory')
    ->getEditable('core.entity_view_display.media.remote_video.media_card_caption');
  $media_card_caption_remote_video_display->delete();

  /** @var \Drupal\Core\Config\Config $media_card_caption */
  $media_card_caption = \Drupal::service('config.factory')
    ->getEditable('core.entity_view_mode.media.media_card_caption');
  $media_card_caption->delete();
}

/**
 * Add Frameless as an option for remote video.
 */
function osu_media_post_update_add_frameless_embed_option(&$sandbox) {
  $config_name = 'core.entity_view_display.media.remote_video.frameless';
  /** @var string $osu_media_module_path */
  $osu_media_module_path = \Drupal::service('module_handler')
    ->getModule('osu_media')
    ->getPath();
  $config_path = realpath($osu_media_module_path . '/config/install');

  /** @var \Drupal\Core\Config\StorageInterface $config_storage */
  $config_storage = \Drupal::service('config.storage');
  $config_source = new FileStorage($config_path);
  $config_storage->write($config_name, $config_source->read($config_name));
}
