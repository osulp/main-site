<?php

namespace Drupal\osu_groups_basic_group;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Render\Element\RenderCallbackInterface;

/**
 * Provides a trusted callback to alter the system branding block.
 *
 * @see osu_groups_basic_group_block_view_system_branding_block_alter()
 */
class OsuGroupsBasicGroupSystemBrandingBlockAlter implements RenderCallbackInterface {

  /**
   * Pre Render Callback Sets site name if node is in a group.
   */
  public static function preRender($build) {
    // Ensures Block will be cached based on URL path only.
    CacheableMetadata::createFromRenderArray($build)
      ->addCacheContexts(['url.path'])
      ->applyTo($build);

    /** @var \Drupal\osu_groups\OsuGroupsHandler $osu_groups */
    $osu_groups = \Drupal::service('osu_groups.group_handler');

    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      $group_content = $osu_groups->getGroupContentFromNode($node);
      if ($group_content) {
        $group_name = $osu_groups->getGroupNameFromNode($node);
        $group = $group_content->getGroup();
        // Set the group name, path in the site branding block.
        $build['content']['group_name_link'] = [
          '#type' => 'link',
          '#title' => $group_name,
          '#url' => $group->toUrl(),
          '#attributes' => [
            'class' => [
              'site-name__group-link',
              'text-decoration-none',
              'osu-text-osuorange',
              'fw-bolder',
            ],
          ],
        ];
      }
    }
    // For Group Entities Only we return an H1 as the current theme shows this
    // in the header block.
    elseif ($group = \Drupal::routeMatch()->getParameter('group')) {
      $group_name = $osu_groups->getGroupnameFromGroup($group);
      // Set the group name, path in the site branding block.
      $build['content']['group_name_link'] = [
        '#type' => 'link',
        '#title' => [
          '#type' => 'html_tag',
          '#tag' => 'h1',
          '#value' => $group_name,
          '#attributes' => [
            'class' => [
              'site-name__group-link__heading'
            ],
          ],
        ],
        '#attributes' => [
          'class' => [
            'site-name__group-link',
            'site-name__group-front',
            'text-decoration-none',
            'osu-text-osuorange',
            'fw-bolder',
          ],
        ],
        '#url' => $group->toUrl(),
      ];
    }
    return $build;
  }

}
