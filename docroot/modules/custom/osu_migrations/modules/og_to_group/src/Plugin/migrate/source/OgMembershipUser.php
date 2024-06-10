<?php

namespace Drupal\og_to_group\Plugin\migrate\source;

/**
 * Drupal 7 OG Membership source from Database.
 *
 * Migrate Source plugin to query Drupal 7 and load all user relationships for
 * group membership.
 *
 * @MigrateSource(
 *   id = "d7_og_membership_user",
 *   source_module = "og"
 * )
 *
 * Examples:
 *
 * @code
 * source:
 *   plugin: d7_og_membership_user
 * @endcode
 *
 * @code
 * source:
 *   plugin: d7_og_membership_user
 *   role_name:
 *     - editor
 *     - author
 * @endcode
 */
class OgMembershipUser extends OgMembershipBase {

  /**
   * {@inheritDoc}
   *
   * Query the OG Membership table and grab only user relationships.
   */
  public function query() {
    $query = parent::query();
    $query->condition('ogm.entity_type', 'user');

    if (isset($this->configuration['role_name'])) {
      $query->innerJoin('og_users_roles', 'ogur', 'ogm.gid = ogur.gid');
      $query->innerJoin('og_role', 'ogr', 'ogur.rid = ogr.rid');
      $query->condition('ogr.name', (array) $this->configuration['role_name'], 'IN');
    }
    return $query;
  }

}
