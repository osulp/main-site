<?php

namespace Drupal\osu_migrate_content\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Context source plugin.
 *
 * @MigrateSource(
 *   id = "osu_d7_context",
 *   source_module = "context"
 * )
 */
class OsuContext extends DrupalSqlBase {

  /**
   * {@inheritDoc}
   */
  public function getIds() {
    return [
      'name' => [
        'type' => 'string',
        'max_length' => 255,
        'is_ascii' => TRUE,
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = $this->select('context', 'oc');
    $query->fields('oc', [
      'name',
      'description',
      'tag',
      'conditions',
      'reactions',
      'condition_mode',
    ]);
    return $query;
  }

  /**
   * {@inheritDoc}
   */
  public function fields() {
    return [
      'name' => $this->t('Context name'),
      'description' => $this->t('Context description'),
      'tag' => $this->t('Context tag'),
      'conditions' => $this->t('Context conditions'),
      'reactions' => $this->t('Context reactions'),
      'condition_mode' => $this->t('The condition mode, 0 for any 1 for all'),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function prepareRow(Row $row) {
    $context_status_arr = $this->variableGet('context_status', []);
    $context_name = $row->getSourceProperty('name');
    if (isset($context_status_arr[$context_name]) && $context_status_arr[$context_name] === TRUE) {
      $row->setSourceProperty('status', 0);
    }
    else {
      $row->setSourceProperty('status', 1);
    }
    return parent::prepareRow($row);
  }

}
