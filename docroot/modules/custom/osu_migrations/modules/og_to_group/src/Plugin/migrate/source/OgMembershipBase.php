<?php

namespace Drupal\og_to_group\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Base OG Migration class.
 */
class OgMembershipBase extends DrupalSqlBase {

  /**
   * {@inheritDoc}
   */
  public function getIds() {
    return [
      'id' => [
        'type' => 'integer',
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = $this->select('og_membership', 'ogm');
    $query->fields('ogm', ['id', 'etid', 'gid']);
    $query->distinct();

    return $query;
  }

  /**
   * {@inheritDoc}
   */
  public function fields() {
    return [
      'id' => $this->t('The Group Membership ID'),
      'etid' => $this->t('The Target Entity ID'),
      'gid' => $this->t('The Group ID'),
    ];
  }

}
