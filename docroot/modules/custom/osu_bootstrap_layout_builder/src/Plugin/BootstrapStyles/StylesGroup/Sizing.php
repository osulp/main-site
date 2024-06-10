<?php

namespace Drupal\osu_bootstrap_layout_builder\Plugin\BootstrapStyles\StylesGroup;

use Drupal\bootstrap_styles\StylesGroup\StylesGroupPluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Style group Sizing class.
 *
 * @package Drupal\osu_bootstrap_layout_builder\Plugin\StylesGroup
 *
 * @StylesGroup(
 *   id = "sizing",
 *   title = @Translation("Sizing"),
 *   weight = 6,
 *   icon = "osu_bootstrap_layout_builder/images/plugins/sizing-icon.svg"
 * )
 */
class Sizing extends StylesGroupPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['sizing'] = [
      '#type' => 'details',
      '#title' => $this->t('Sizing'),
      '#open' => FALSE,
    ];
    return $form;
  }

}
