<?php

namespace Drupal\osu_migrate_content\Plugin\migrate\source;

use Drupal\menu_link_content\Plugin\migrate\source\MenuLink;

/**
 * Custom Source Plugin to filter out book-toc- menu links.
 *
 * @MigrateSource(
 *   id = "menu_link_filtered",
 *   source_module = "menu"
 * )
 */
class OsuMenuLinksFiltered extends MenuLink {

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = parent::query();
    $query->condition('ml.menu_name', 'book-toc-%', 'NOT LIKE');
    return $query;
  }

}
