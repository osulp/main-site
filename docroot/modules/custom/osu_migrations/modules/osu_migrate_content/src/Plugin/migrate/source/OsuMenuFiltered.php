<?php

namespace Drupal\osu_migrate_content\Plugin\migrate\source;

use Drupal\system\Plugin\migrate\source\Menu;

/**
 * Custom Source Plugin to filter out book-toc- menus.
 *
 * @MigrateSource(
 *   id = "menu_filtered",
 *   source_module = "menu"
 * )
 */
class OsuMenuFiltered extends Menu {

  /**
   * {@inheritDoc}
   */
  public function query() {
    // Filter out book-toc menus as we will migrate them differently.
    $query = parent::query();
    $query->condition('m.menu_name', 'book-toc-%', 'NOT LIKE');
    return $query;
  }

}
