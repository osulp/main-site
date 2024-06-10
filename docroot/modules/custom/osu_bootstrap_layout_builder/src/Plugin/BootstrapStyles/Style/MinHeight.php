<?php

namespace Drupal\osu_bootstrap_layout_builder\Plugin\BootstrapStyles\Style;

use Drupal\bootstrap_styles\Style\StylePluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
* Style MinHeight class.
*
* @package Drupal\osu_bootstrap_layout_builder\Plugin\Style
*
* @Style(
*   id = "min_height",
*   title = @Translation("Minimum height"),
*   group_id = "sizing",
*   weight = 0
* )
*/
class MinHeight extends StylePluginBase {

  /**
  * {@inheritDoc}
  */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $config = $this->config();

    $form['sizing']['min_height'] = [
      '#type' => 'textarea',
      '#default_value' => $config->get('min_height'),
      '#title' => $this->t('Minimum height'),
      '#cols' => 60,
      '#rows' => 5,
    ];

    return $form;
  }

  /**
  * {@inheritDoc}
  */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->config()
      ->set('min_height', $form_state->getValue('min_height'))
      ->save();
  }

  /**
  * {@inheritDoc}
  */
  public function buildStyleFormElements(array &$form, FormStateInterface $form_state, $storage) {
    $form['min_height'] = [
      '#type' => 'radios',
      '#title' => $this->t('Minimum height'),
      '#options' => $this->getStyleOptions('min_height'),
      '#default_value' => $storage['min_height']['class'] ?? 0,
      '#validated' => TRUE,
      '#attributes' => [
        'class' => ['bs_input-boxes', 'field-min-height'],
      ],
    ];

    return $form;
  }

  /**
  * {@inheritDoc}
  */
  public function submitStyleFormElements(array $group_elements) {
    return [
      'min_height' => [
        'class' => $group_elements['min_height'],
      ],
    ];
  }

  /**
  * {@inheritDoc}
  */
  public function build(array $build, array $storage, $theme_wrapper = NULL) {
    $classes = [];
    if (isset($storage['min_height']['class'])) {
      $classes[] = $storage['min_height']['class'];
    }


    // Add the classes to the build.
    $build = $this->addClassesToBuild($build, $classes);

    $build['#attached']['library'][] = 'osu_bootstrap_layout_builder/plugin.min_height.build';

    return $build;
  }

}
