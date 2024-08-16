<?php

namespace Drupal\osu_migrations_gradschool\Plugin\migrate\source;

use Drupal\menu_link_content\Plugin\migrate\source\MenuLink;

/**
 * Drupal 7 menu link source from database.
 *
 * @MigrateSource(
 *   id = "og_menu_link",
 *   source_module = "og_menu"
 * )
 */
class OgMenuLink extends MenuLink {

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = parent::query();
    $query->condition('ml.menu_name', 'menu-og%', 'LIKE');
    return $query;
  }

}
