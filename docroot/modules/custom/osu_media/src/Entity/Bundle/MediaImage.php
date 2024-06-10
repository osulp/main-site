<?php

namespace Drupal\osu_media\Entity\Bundle;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\media\Entity\Media;

/**
 * A bundle class for media entities.
 */
class MediaImage extends Media {

  /**
   * {@inheritDoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    $this->updateThumbnail();
  }

}
