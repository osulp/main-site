<?php

namespace Drupal\osu_user_to_profiles\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Drupal 7 Migrate OSU Faculty Profile type.
 *
 * Examples:
 *
 * @code
 * source:
 *   plugin: d7_osu_profile2
 *   source_module: osu_faculty
 * @endcode
 *
 * In this example nodes of type page are retrieved from the source database.
 *
 * @code
 * source:
 *   plugin: d7_osu_profile2
 *   node_type: [osu_employee, osu_person]
 * @endcode
 *
 * @MigrateSource(
 *   id = "d7_osu_profile2",
 *   source_module = "profile2"
 * )
 */
class OsuProfile2 extends FieldableEntity {

  /**
   * {@inheritDoc}
   */
  public function getIds() {
    return [
      'pid' => [
        'type' => 'integer',
        'alias' => 'p',
      ],
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = $this->select('profile', 'p');
    $query->fields('p', ['pid', 'type', 'uid', 'created', 'changed']);
    if (isset($this->configuration['profile2_type'])) {
      $query->condition('p.type', (array) $this->configuration['profile2_type'], 'IN');
    }
    return $query;
  }

  /**
   * {@inheritDoc}
   */
  public function fields() {
    return [
      'pid' => $this->t('Profile id'),
      'type' => $this->t('Entity Bundle'),
      'uid' => $this->t('User the profile belongs to'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Modified timestamp'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $pid = $row->getSourceProperty('pid');
    $type = $row->getSourceProperty('type');
    // Check to see if this entity was translated.
    $entity_translatable = $this->isEntityTranslatable('profile2');
    $source_language = $this->getEntityTranslationSourceLanguage('profile2', $pid);
    $language = $entity_translatable && $source_language ? $source_language : $row->getSourceProperty('language');

    // Get Field API field values.
    foreach ($this->getFields('profile2', $type) as $field_name => $field) {
      $row->setSourceProperty($field_name, $this->getFieldValues('profile2', $field_name, $pid, NULL, $language));
    }
    return parent::prepareRow($row);
  }

}
