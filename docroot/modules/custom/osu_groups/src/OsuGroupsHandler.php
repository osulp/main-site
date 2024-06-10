<?php

namespace Drupal\osu_groups;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;

/**
 * Provides helper functions to get data about groups.
 */
class OsuGroupsHandler {


  /**
   * The Entity Type manager Service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * Creates a new OsuGroupsHandler.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity Type manager Service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Get the Group name for the given node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   Group to interrogate.
   *
   * @return string|null
   *   The Group Name or null.
   */
  public function getGroupNameFromNode(Node $node) {
    /** @var \Drupal\group\Entity\Storage\GroupContentStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content');
    $group_content = $storage->loadByEntity($node);
    if ($group_content) {
      $group_content = reset($group_content);
      $group = $group_content->getGroup();
      return $group->get('label')->first()->getValue()['value'];
    }
    return NULL;
  }

  /**
   * Get the group name for the given group.
   *
   * @param \Drupal\group\Entity\Group $group
   *   The Group to look interrogate.
   *
   * @return string
   *   The String representing the Groups Name.
   */
  public function getGroupnameFromGroup(Group $group): string {
    return $group->get('label')->first()->getValue()['value'];
  }

  /**
   * Get the Group Content entity for the node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node to interrogate.
   *
   * @return \Drupal\group\Entity\GroupContentInterface|null
   *   The Group Content Entity or null.
   */
  public function getGroupContentFromNode(Node $node) {
    // group_content will be removed in group v3, change to group_relationship.
    /** @var \Drupal\group\Entity\Storage\GroupContentStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content');
    $group_content = $storage->loadByEntity($node);
    if ($group_content) {
      return reset($group_content);
    }
    return NULL;
  }

}
