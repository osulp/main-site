<?php

/**
 * @file
 * OSU Groups Basic Group updates to run after others updates have run.
 */

use Drupal\group\Entity\Group;

/**
 * Update all existing groups' field value after adding new field.
 */
function osu_groups_basic_group_post_update_9001(&$sandbox) {
  foreach (Group::loadMultiple() as $group) {
    if ($group->getGroupType()->id() == 'basic_group') {
      $group->set('field_hide_global_navigation', TRUE);
      $group->save();
    }
  }
}
