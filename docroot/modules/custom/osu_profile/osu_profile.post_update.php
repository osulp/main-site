<?php

use Drupal\Core\Config\FileStorage;
use Drupal\field\Entity\FieldConfig;

function osu_profile_post_update_set_name_h1_formatter(&$sandbox) {
  // Set title to be H1 and text-break.
  $profile_layout = \Drupal::configFactory()
    ->getEditable('core.entity_view_display.node.osu_profile.default');
  $third_party_settings = $profile_layout->get('third_party_settings');
  $components = $third_party_settings['layout_builder']['sections'][0]['components'];
  if (isset($components['3a4fe79a-e52e-4fa2-99ee-3f5fec409e60'])) {
    $components['3a4fe79a-e52e-4fa2-99ee-3f5fec409e60']['additional']['component_attributes']['block_content_attributes']['class'] = "";
    $components['3a4fe79a-e52e-4fa2-99ee-3f5fec409e60']['configuration']['formatter'] = [
      "type" => "text_field_formatter",
      "label" => "hidden",
      "settings" => [
        "link_to_entity" => 0,
        "wrap_tag" => "h1",
        "wrap_class" => "text-break",
        "wrap_attributes" => "",
        "override_link_label" => "",
        "token" => "",
      ],
      "third_party_settings" => [],
    ];
    $third_party_settings['layout_builder']['sections'][0]['components'] = $components;
    $profile_layout->set('third_party_settings', $third_party_settings);
    $profile_layout->save();
    return t('Updated Profile name to display as H1');
  }
  return t('Title block uuid does not match expected.');
}

/**
 * Update osu profile to create a new field type.
 */
function osu_profile_post_update_add_field_osu_organizations(&$sandbox) {
  $install_path = \Drupal::service('module_handler')
    ->getModule('osu_profile')
    ->getPath();
  $config_path = realpath($install_path . '/config/install');
  $source = new FileStorage($config_path);
  FieldConfig::create($source->read('field.field.node.osu_profile.field_osu_organizations'))
    ->save();
  /** @var \Drupal\Core\Config\CachedStorage $config_storage */
  $config_storage = \Drupal::service('config.storage');
  $config_storage->write('core.entity_form_display.node.osu_profile.default', $source->read('core.entity_form_display.node.osu_profile.default'));
  $config_storage->write('core.entity_view_display.node.osu_profile.default', $source->read('core.entity_view_display.node.osu_profile.default'));
}
