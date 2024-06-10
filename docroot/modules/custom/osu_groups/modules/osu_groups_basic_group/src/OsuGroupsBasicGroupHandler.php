<?php

namespace Drupal\osu_groups_basic_group;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\group\Entity\Group;

/**
 * Provides helper functions to get data about basic groups.
 */
class OsuGroupsBasicGroupHandler {

  /**
   * Entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private EntityFieldManagerInterface $entityFieldManager;

  /**
   * Entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  private EntityTypeBundleInfoInterface $bundleInfo;

  /**
   * Constructs an OsuGroupsBasicGroupHandler object.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfo
   *   The Entity Type Bundle Service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The Entity Field Manager Interface.
   */
  public function __construct(EntityTypeBundleInfoInterface $bundleInfo, EntityFieldManagerInterface $entityFieldManager) {
    $this->entityFieldManager = $entityFieldManager;
    $this->bundleInfo = $bundleInfo;
  }

  /**
   * Get the Group Landing Page for given group.
   *
   * @param \Drupal\group\Entity\Group $group
   *   The Group to look at.
   *
   * @return \Drupal\node\Entity\Node|null
   *   The Node representing the lading page or null.
   */
  public function getGroupLandingNode(Group $group) {
    if ($this->bundleHasField('field_group_landing_page', $group->bundle(), $group->getEntityTypeId())) {
      $group_landing_node_list = $group->get('field_group_landing_page');
      if (count($group_landing_node_list) > 0) {
        return $group_landing_node_list
          ->first()
          ->get('entity')
          ->getTarget()
          ->getValue();
      }
    }
    return NULL;
  }

  /**
   * Check if the given field exists in the given entity.
   *
   * @param string $field_name
   *   The filed name to check.
   * @param string $entity_type
   *   The entity type to look at.
   *
   * @return bool
   *   A boolean TRUE if the field is in the entity type; otherwise, FALSE;
   */
  public function entityTypeHasField($field_name, $entity_type = 'node'): bool {
    $bundles = $this->bundleInfo->getBundleInfo($entity_type);
    foreach ($bundles as $bundle => $label) {
      $all_bundle_fields = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);
      if (isset($all_bundle_fields[$field_name])) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Check if the given field exists in the bundle and entity combination.
   *
   * @param string $field_name
   *   The field name to check.
   * @param string $bundle
   *   The bundle to filter against.
   * @param string $entity_type
   *   The entity type to look at.
   *
   * @return bool
   *   A Boolean True if the field is in the bundle type; otherwise, FALSE.
   */
  public function bundleHasField($field_name, $bundle = 'page', $entity_type = 'node'): bool {
    $all_bundle_fields = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle);
    if (isset($all_bundle_fields[$field_name])) {
      return TRUE;
    }
    return FALSE;
  }

}
