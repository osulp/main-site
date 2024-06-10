<?php

namespace Drupal\live_feeds;

use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Class LiveFeedsSmartLimit.
 *
 * @package Drupal\live_feeds
 */
class LiveFeedsSmartTrim implements TrustedCallbackInterface {

  /**
   * {@inheritDoc}
   */
  public static function trustedCallbacks() {
    return ['liveFeedsLimit'];
  }

  /**
   * Takes a long string and truncates it after a number of words.
   *
   * @param string $stringBig
   *   The string to be truncated.
   * @param int $wordLimit
   *   The number of words to limit to.
   *
   * @return string
   *   The truncated string.
   */
  public function liveFeedsLimit($stringBig, $wordLimit) {
    $string = explode(' ', $stringBig);
    if (count($string) > $wordLimit) {
      return implode(' ', array_slice($string, 0, $wordLimit)) . " &hellip;";
    }
    return implode(' ', $string) . " &hellip;";
  }

}
