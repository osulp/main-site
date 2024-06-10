<?php

namespace Drupal\osu_icon_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * A widget for OSU Icon.
 *
 * @FieldWidget(
 *   id = "osu_icon_widget",
 *   label = @Translation("OSU Icon"),
 *   field_types = {
 *     "osu_icon"
 *   }
 * )
 */
class OsuIconWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = $items[$delta]->value ?? '';
    $size = $items[$delta]->size ?? '';
    $element['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon name'),
      '#description' => $this->t('Start typing the name of the icon and it will show a list of available icons that match.'),
      '#default_value' => $value,
      '#size' => 10000,
      '#maxlength' => 100,
      '#id' => 'osuIconInput',
    ];
    $element['size'] = [
      '#type' => 'select',
      '#title' => $this->t('Size'),
      '#description' => $this->t('Set the Icon size relative to the font size'),
      '#options' => [
        'fa-2x' => $this->t('2x'),
        'fa-3x' => $this->t('3x'),
        'fa-4x' => $this->t('4x'),
        'fa-5x' => $this->t('5x'),
        'fa-6x' => $this->t('6x'),
        'fa-7x' => $this->t('7x'),
        'fa-8x' => $this->t('8x'),
        'fa-9x' => $this->t('9x'),
        'fa-10x' => $this->t('10x'),
      ],
      '#default_value' => $size,
    ];
    return $element;
  }

}
