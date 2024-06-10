<?php

namespace Drupal\admissions_articulations\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Articulations Link' formatter.
 *
 * @FieldFormatter(
 *   id = "articulations_table_formatter",
 *   label = @Translation("Articulations Table"),
 *   field_types = {
 *     "articulations_table"
 *   }
 * )
 */
class ArticulationsTableFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Guzzle\Client instance.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private ClientInterface $httpClient;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private LoggerChannelFactoryInterface $logger;

  /**
   * Constructs an Articulations Table Formatter.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The Guzzle HTTP Client interface.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The Logger Factory Interface.
   */
  public function __construct($plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    ClientInterface $http_client,
    LoggerChannelFactoryInterface $logger) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->httpClient = $http_client;
    $this->logger = $logger;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('http_client'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'articulations_table',
        '#markup' => $this->getArticulationsTable($item->value),
      ];
    }

    return $element;
  }

  /**
   * Fetch the Articulations Table.
   *
   * @param string $articulationsUrl
   *   URL to the Articulations Table.
   *
   * @return string
   *   Either the data from the Articulations source or a message that it's
   *   not working.
   */
  protected function getArticulationsTable(string $articulationsUrl): string {
    try {
      $request = $this->httpClient->request('GET', $articulationsUrl);
      if (in_array($request->getStatusCode(), [200, 301, 302])) {
        $response_body = $request->getBody();
        return Xss::filter($response_body, ['pre']);
      }
    }
    catch (GuzzleException $e) {
      $this->logger->get('articulations_table')->error($e->getMessage());
    }
    return "There was a problem fetching articulation";
  }

}
