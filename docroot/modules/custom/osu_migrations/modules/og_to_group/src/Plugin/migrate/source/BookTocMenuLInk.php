<?php

namespace Drupal\og_to_group\Plugin\migrate\source;

use Drupal\menu_link_content\Plugin\migrate\source\MenuLink;

/**
 * Drupal 7 menu link source from database.
 *
 * @MigrateSource(
 *   id = "book_toc_menu_link",
 *   source_module = "menu"
 * )
 */
class BookTocMenuLInk extends MenuLink {

  /**
   * {@inheritDoc}
   *
   * Copied the full query from menu link but changed the module condition from
   * menu to book. Everything else from MenuLink was left alone.
   */
  public function query() {
    $query = $this->select('menu_links', 'ml')
      ->fields('ml');
    $and = $query->andConditionGroup()
      ->condition('ml.module', 'book')
      ->condition('ml.router_path', [
        'admin/build/menu-customize/%',
        'admin/structure/menu/manage/%',
      ], 'NOT IN');
    $condition = $query->orConditionGroup()
      ->condition('ml.customized', 1)
      ->condition($and);
    $query->condition($condition);
    $query->condition('ml.menu_name', 'book-toc-%', 'LIKE');
    $query->leftJoin('menu_links', 'pl', '[ml].[plid] = [pl].[mlid]');
    $query->addField('pl', 'link_path', 'parent_link_path');
    $query->orderBy('ml.depth');
    $query->orderby('ml.mlid');
    return $query;
  }

}
