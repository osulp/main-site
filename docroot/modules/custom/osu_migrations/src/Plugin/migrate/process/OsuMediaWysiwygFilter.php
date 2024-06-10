<?php

namespace Drupal\osu_migrations\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\osu_migrations\OsuMediaEmbed;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Process Plugin to transform Drupal 7 embed to Drupal 9.
 *
 * @MigrateProcessPlugin(
 *   id = "osu_media_wysiwyg_filter"
 * )
 */
class OsuMediaWysiwygFilter extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The OSU Media Embed Service.
   *
   * @var \Drupal\osu_migrations\OsuMediaEmbed
   */
  private OsuMediaEmbed $osuMediaEmbed;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, OsuMediaEmbed $osuMediaEmbed) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->osuMediaEmbed = $osuMediaEmbed;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('osu_migrations.osu_media_embed'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Check to see if the $value is an array or not and if it is an array get
    // the nested value key.
    $value_is_array = is_array($value);
    $text = (string) ($value_is_array ? $value['value'] : $value);

    $text = $this->osuMediaEmbed->transformEmbedCode($text);

    if ($value_is_array) {
      $value['value'] = $text;
    }
    else {
      $value = $text;
    }
    return $value;
  }

}
