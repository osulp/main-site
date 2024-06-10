<?php

namespace Drupal\osu_migrations_media\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "media_remote_video"
 * )
 */
class MediaRemoteVideo extends ProcessPluginBase {

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $update_uri_scheme = preg_replace([
      '/^youtube:\/\/v\//i',
      '/^vimeo:\/\/v\//i',
      '/^mediaspace:\/\/v\//i',
    ], [
      'https://www.youtube.com/watch?v=',
      'https://vimeo.com/',
      'https://',
    ], $value);
    return $update_uri_scheme;
  }

}
