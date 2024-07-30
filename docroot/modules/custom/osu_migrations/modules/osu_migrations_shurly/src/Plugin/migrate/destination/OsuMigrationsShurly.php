<?php

namespace Drupal\osu_migrations_shurly\Plugin\migrate\destination;

use Drupal\migrate\Plugin\migrate\destination\DestinationBase;
use Drupal\migrate\Row;

/**
 * Provides a ShURLy destination plugin.
 *
 * @MigrateDestination(
 *   id = "shurly"
 * )
 */
class OsuMigrationsShurly extends DestinationBase {

  /**
   * {@inheritDoc}
   */
  public function getIds() {
    return ['rid' => ['type' => 'integer']];
  }

  /**
   * {@inheritDoc}
   */
  public function import(Row $row, array $old_destination_id_values = []): array|bool {
    // Implement your custom import logic here.
    // This method should return an array of destination IDs if successful,
    // false on failure.
    $record = [];
    $record['destination'] = $row->getSourceProperty('destination');
    $record['hash'] = $row->getSourceProperty('hash');
    $record['custom'] = $row->getSourceProperty('custom');
    $record['created'] = $row->getSourceProperty('created');
    $record['source'] = $row->getSourceProperty('source');
    $record['uid'] = $row->getSourceProperty('uid');
    $record['count'] = $row->getSourceProperty('count');
    $record['last_used'] = $row->getSourceProperty('last_used');
    $record['active'] = $row->getSourceProperty('active');

    return [\Drupal::database()->insert('shurly')->fields($record)->execute()];
  }

  /**
   * {@inheritDoc}
   */
  public function fields(): array {
    return [
      'destination' => $this->t('The destination URL'),
      'hash' => $this->t('The hash of the ShURLy redirection.'),
      'custom' => $this->t('Boolean to represent if the link was custom.'),
      'created' => $this->t('timestamp the redirect was created.'),
      'source' => $this->t('The source URL.'),
      'uid' => $this->t('The uid of the user who created the ShURLy redirection.'),
      'count' => $this->t('The number of clicks.'),
      'last_used' => $this->t('Timestamp the last time the link was used.'),
      'active' => $this->t('Boolean represents status of the link.'),
    ];
  }

}
