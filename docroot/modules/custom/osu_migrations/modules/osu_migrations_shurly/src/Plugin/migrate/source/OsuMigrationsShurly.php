<?php

namespace Drupal\osu_migrations_shurly\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 7 Migrations for ShURLy.
 *
 * @MigrateSource(
 *   id = "d7_shurly",
 *   source_module = "shurly"
 * )
 */
class OsuMigrationsShurly extends DrupalSqlBase {

  /**
   * {@inheritDoc}
   */
  public function getIds() {
    return [
      'rid' => [
        'type' => 'integer',
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = $this->select('shurly', 'shurly');
    $query->fields('shurly', [
      'rid',
      'uid',
      'source',
      'destination',
      'hash',
      'created',
      'count',
      'last_used',
      'custom',
      'active',
    ]);
    $query->distinct();

    return $query;
  }

  /**
   * {@inheritDoc}
   */
  public function fields() {
    return [
      'rid' => $this->t('The shurly ID.'),
      'uid' => $this->t('The user ID.'),
      'source' => $this->t('The source URL.'),
      'destination' => $this->t('The destination URL.'),
      'hash' => $this->t('The hash.'),
      'created' => $this->t('The created date.'),
      'count' => $this->t('The count.'),
      'last_used' => $this->t('The last used date.'),
      'custom' => $this->t('The custom field.'),
      'active' => $this->t('The active date.'),
    ];
  }

}
