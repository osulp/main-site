<?php

use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;

/**
 * Implements hook_form_FORM_ID_alter()
 */
function osu_standard_form_install_configure_form_alter(&$form, FormStateInterface $formState) {
  // Set some placeholder text for this.
  $form['site_information']['site_mail']['#default_value'] = 'noreply@mail.drupal.oregonstate.edu';
  $form['site_information']['site_name']['#attributes']['placeholder'] = t('OSU Site');

  // Account information defaults.
  $form['admin_account']['account']['name']['#default_value'] = 'cws_dpla';
  $form['admin_account']['account']['mail']['#default_value'] = 'noreply@mail.drupal.oregonstate.edu';

  // Date/time settings.
  $form['regional_settings']['site_default_country']['#default_value'] = 'US';
  $form['regional_settings']['date_default_timezone']['#default_value'] = 'America/Los_Angeles';

  // Update notifications.
  $form['update_notifications']['enable_update_status_module']['#default_value'] = 0;
}

/**
 * Implements hook_install_tasks().
 */
function osu_standard_install_tasks(&$install_state) {
  $tasks = [];
  $tasks['osu_standard_default_modules'] = [
    'display_name' => t('Add Modules.'),
    'display' => TRUE,
  ];
  $tasks['osu_standard_update_default_configuration'] = [
    'display_name' => t('Update provided configurations'),
    'display' => TRUE,
  ];
  return $tasks;
}

/**
 * Install modules that require a full site to be ready.
 *
 * This allows modules to be installed and not have a hard dependency on the
 * installation profile.
 *
 * @param array $install_state
 *   The Drupal Install State.
 */
function osu_standard_default_modules(array &$install_state) {
  \Drupal::service('module_installer')->install([
    'ckeditor_div_manager',
    'osu_block_types',
    'osu_story',
    'osu_profile',
    'osu_library_hero',
    'osu_library_three_column_cards',
    'osu_library_three_column_equal',
    'osu_library_two_column_25_75',
    'osu_library_two_column_50_50',
  ], TRUE);
}

/**
 * Apply Configuration updates to the site post install.
 *
 * @param array $install_state
 *  The Drupal Install State.
 */
function osu_standard_update_default_configuration(array &$install_state) {
  $site_host = \Drupal::request()->getHost();
  $site_host = str_replace(['dev.', 'stage.'], '', $site_host);

  $config_factory = \Drupal::configFactory();
  // Set Google CSE as default search.
  $google_search_settings = $config_factory->getEditable('search.page.google_cse_search');
  $google_search_config = $google_search_settings->get('configuration');
  $google_search_config['limit_domain'] = $site_host;
  $google_search_settings->set('configuration', $google_search_config);
  $google_search_settings->save();

  // Remove User and Node search.
  $node_search = $config_factory->getEditable('search.page.node_search');
  $node_search->set('status', FALSE);
  $node_search->save();
  $user_search = $config_factory->getEditable('search.page.user_search');
  $user_search->set('status', FALSE);
  $user_search->save();

  // Add cws_dpla to user 1 and enable cas
  $super_user = User::load(1);
  /** @var Drupal\cas\Service\CasUserManager $casUserManager */
  $casUserManager = \Drupal::service('cas.user_manager');
  $casUserManager->setCasUsernameForAccount($super_user, 'cws_dpla');

  // Remove display author information from node Webform type
  $node_type_webform = $config_factory->getEditable('node.type.webform');
  $node_type_webform->set('display_submitted', FALSE);
  $node_type_webform->save();
}
