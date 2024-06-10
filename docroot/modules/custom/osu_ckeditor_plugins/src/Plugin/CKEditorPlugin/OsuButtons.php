<?php

namespace Drupal\osu_ckeditor_plugins\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "osu_ckeditor_plugins" plugin.
 *
 * @CKEditorPlugin(
 *   id = "osu_ckeditor_plugins_osu_buttons",
 *   label = @Translation("OSU Buttons"),
 *   module = "osu_ckeditor_plugins"
 * )
 */
class OsuButtons extends CKEditorPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The module Extension List Service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  private ModuleExtensionList $moduleExtensionList;

  /**
   * Constructs a \Drupal\ckeditor\CKEditorPluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   The module Extension List Service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleExtensionList $moduleExtensionList) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleExtensionList = $moduleExtensionList;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('extension.list.module')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return $this->moduleExtensionList->getPath('osu_ckeditor_plugins') . '/js/plugins/osu_buttons/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $module_path = $this->moduleExtensionList->getPath('osu_ckeditor_plugins');
    return [
      'osu_buttons' => [
        'label' => $this->t('osu_buttons'),
        'image' => $module_path . '/js/plugins/osu_buttons/icons/osu_buttons.png',
      ],
    ];
  }

}
