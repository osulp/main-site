<?php

namespace Drupal\live_feeds;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Simple class to provide functions for requesting RSS feeds.
 *
 * @package Drupal\live_feeds
 */
class GetFeed implements TrustedCallbackInterface {

  /**
   * The Guzzle HTTP Client.
   *
   * @var \GuzzleHttp\Client
   */
  private $httpClient;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private LoggerChannelFactoryInterface $logger;

  /**
   * Constructor.
   *
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The HTTP Client.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   */
  public function __construct(ClientInterface $httpClient, LoggerChannelFactoryInterface $logger_factory) {
    $this->httpClient = $httpClient;
    $this->logger = $logger_factory;
  }

  /**
   * {@inheritDoc}
   */
  public static function trustedCallbacks() {
    return ['getFeed'];
  }

  /**
   * Get the RSS feed from given URL.
   *
   * @param string $feed_url
   *   The Feed url will attempt to retrieve.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */

  /**
   * Get the RSS feed from given URL.
   *
   * @param string $feed_url
   *   The feed URL to retrieve.
   *
   * @return \SimpleXMLElement|false
   *   The feed as a SimpleXMLElement, or FALSE on failure.
   *
   * @throws \Exception
   *   If the feed cannot be loaded.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getFeed($feed_url) {
    // Try to request the feed.
    try {
      $http_response = $this->httpClient->request('GET', $feed_url);
      $response = $http_response->getBody();

      return $this->parseResponseToXml($response);

    }
    catch (RequestException $e) {
      // Log the failed request to watchdog.
      $this->logger->get('live_feeds')
        ->error('Failed request for "@feed": @message', [
          '@feed' => $feed_url,
          '@message' => $e->getMessage(),
        ]);
    }

    return FALSE;
  }

  /**
   * Cleans the response and converts it to XML.
   *
   * @param string $response
   *   The feed response to parse.
   *
   * @return \SimpleXMLElement|null
   *   The parsed feed as a SimpleXMLElement, or NULL on failure.
   *
   * @throws \Exception
   *   If the feed cannot be loaded.
   */
  private function parseResponseToXml(string $response): ?\SimpleXMLElement {
    $cleaned_response = preg_replace('/[^[:print:]\r\n]/', '', $response);
    $feedXml = simplexml_load_string($cleaned_response);

    if ($feedXml === FALSE) {
      throw new \Exception('Failed to parse the feed');
    }

    return $feedXml;
  }

}
