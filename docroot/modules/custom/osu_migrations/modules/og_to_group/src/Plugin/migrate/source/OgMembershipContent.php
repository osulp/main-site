<?php

namespace Drupal\og_to_group\Plugin\migrate\source;

/**
 * Drupal 7 OG Membership source from Database.
 *
 * Migrate Source plugin to query Drupal 7 and load all node relationships for
 * group content.
 *
 * @MigrateSource(
 *   id = "d7_og_membership_content",
 *   source_module = "og"
 * )
 *
 * Examples:
 * Example usage that will migrate all node types.
 *
 * @code
 * source:
 *   plugin: d7_og_membership_content
 * @endcode
 *
 * Example usage that will migrate filtered down to the provided node types.
 *
 * @code
 * source:
 *   plugin: d7_og_membership_content
 *     node_type:
 *       - book
 * @endcode
 */
class OgMembershipContent extends OgMembershipBase {

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = parent::query();
    $query->innerJoin('node', 'n', 'n.nid = ogm.etid');
    $query->condition('ogm.entity_type', 'node');

    if (isset($this->configuration['node_type'])) {
      $query->condition('n.type', (array) $this->configuration['node_type'], 'IN');
    }
    return $query;
  }

}
