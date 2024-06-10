<?php

namespace Drupal\osu_migrate_content\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Node migration event subscriber.
 */
class TextAreaFormatValueMigrate implements EventSubscriberInterface {

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [
      MigrateEvents::PRE_IMPORT => 'onPreImport',
    ];
  }

  /**
   * Update Text format for body fields on node migration.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $importEvent
   */
  public function onPreImport(MigrateImportEvent $importEvent): void {
    if ($importEvent->getMigration()->getBaseId() === 'upgrade_d7_node') {
      $migration = $importEvent->getMigration();
      $processes = $migration->getProcess();
      foreach ($processes as $destination => $process) {
        if ($destination === 'body') {
          $body_process = [
            'plugin' => 'sub_process',
            'source' => 'body',
            'process' => [
              'value' => [
                'plugin' => 'osu_media_wysiwyg_filter',
                'source' => 'value',
              ],
              'summary' => [
                'plugin' => 'get',
                'source' => 'summary',
              ],
              'format' => [
                'plugin' => 'default_value',
                'default_value' => 'full_html',
              ],
            ],
          ];
          $processes['body'] = $body_process;
        }
      }
      $migration->setProcess($processes);
    }
  }

}
