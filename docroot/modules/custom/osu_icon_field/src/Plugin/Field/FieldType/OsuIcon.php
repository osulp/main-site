<?php

namespace Drupal\osu_icon_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides a field type of OSU Icon.
 *
 * @FieldType(
 *   id = "osu_icon",
 *   label = @Translation("OSU Icon"),
 *   category = @Translation("Icons"),
 *   default_formatter = "osu_icon_formatter",
 *   default_widget = "osu_icon_widget",
 * )
 */
class OsuIcon extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      // Columns contains the values that the field will store.
      'columns' => [
        // List the values that the field will save. This
        // field will only save a single value, 'value'.
        'value' => [
          'description' => 'The icon name.',
          'type' => 'text',
          'size' => 'tiny',
          'not null' => FALSE,
        ],
        'size' => [
          'description' => 'The icon size.',
          'type' => 'text',
          'size' => 'tiny',
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = [];
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The icon name.'));
    $properties['size'] = DataDefinition::create('string')
      ->setLabel(t('Size'))
      ->setDescription(t('The size of the icon.'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return $value === NULL || $value === '';
  }

}
