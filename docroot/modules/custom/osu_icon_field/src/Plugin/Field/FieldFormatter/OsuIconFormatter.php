<?php

namespace Drupal\osu_icon_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'osu_icon' formatter.
 *
 * @FieldFormatter(
 *   id = "osu_icon_formatter",
 *   label = @Translation("OSU Icon"),
 *   field_types = {
 *     "osu_icon"
 *   }
 * )
 */
class OsuIconFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $icon = $item->getValue('values')['value'] ?? '';
      $iconSize = $item->getValue('values')['size'] ?? '';

      $elements[$delta] = [
        '#theme' => 'osu_icon_field_formatter',
        '#icon' => $icon,
        '#size' => $iconSize,
      ];
    }

    return $elements;
  }

}
