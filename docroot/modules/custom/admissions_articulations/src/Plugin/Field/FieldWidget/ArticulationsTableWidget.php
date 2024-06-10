<?php

namespace Drupal\admissions_articulations\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the 'admissions_articulations_field_widget' field widget.
 *
 * @FieldWidget(
 *   id = "articulations_table_widget",
 *   label = @Translation("Articulations Table"),
 *   field_types = {"articulations_table"},
 * )
 */
class ArticulationsTableWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#element_validate'] = [[$this, 'validateArticulations']];
    $element['value'] = $element + [
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->value ?? NULL,
    ];
    return $element;
  }

  /**
   * Validate the fields and convert them into a single value as text.
   */
  public function validateArticulations(&$element, FormStateInterface $form_state, array &$complete_form) {
    if (array_key_exists('value', $element)) {
      $articulations_table_value = $element['value']['#value'];
      if (strlen($articulations_table_value) > 0) {
        if (filter_var($articulations_table_value, FILTER_VALIDATE_URL) === FALSE) {
          $form_state->setError($element, $this->t('Articulations Table Source must be a URL.'));
        }
        if (substr_compare($articulations_table_value, 'inc', strlen($articulations_table_value) - strlen('inc'), strlen('inc') !== 0)) {
          $form_state->setError($element, $this->t('Articulations field must have a valid extension (inc).'));
        }
      }
    }
  }

}
