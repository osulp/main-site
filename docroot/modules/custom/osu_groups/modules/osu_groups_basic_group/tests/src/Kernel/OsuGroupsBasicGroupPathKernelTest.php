<?php

namespace Drupal\Tests\osu_groups_basic_group\Kernel;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;
use Drupal\group_content_menu\Entity\GroupContentMenu;
use Drupal\group_content_menu\Entity\GroupContentMenuType;
use Drupal\group_content_menu\GroupContentMenuInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\pathauto\PathautoGeneratorInterface;
use Drupal\system\Entity\Menu;
use Drupal\Tests\pathauto\Functional\PathautoTestHelperTrait;

/**
 * Unit Tests for OSU Groups Basic Group.
 */
class OsuGroupsBasicGroupPathKernelTest extends EntityKernelTestBase {

  use PathautoTestHelperTrait;

  /**
   * Modules required to run tests.
   *
   * @var string[]
   */
  protected static $modules = [
    'system',
    'token',
    'filter',
    'language',
    'field',
    'text',
    'user',
    'node',
    'link',
    'menu_link_content',
    'path',
    'path_alias',
    'redirect',
    'pathauto',
    'group',
    'options',
    'entity',
    'variationcache',
    'gnode',
    'group_content_menu',
    'osu_groups',
    'osu_groups_basic_group',
  ];

  /**
   * Pathauto pattern entities.
   *
   * @var \Drupal\pathauto\PathautoPatternInterface
   */
  protected $nodePattern;

  /**
   * Pathauto pattern entities.
   *
   * @var \Drupal\pathauto\PathautoPatternInterface
   */
  protected $groupPattern;

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('redirect');
    $this->installEntitySchema('group');
    $this->installEntitySchema('group_content');
    $this->installEntitySchema('group_content_menu');
    $this->installEntitySchema('menu_link_content');
    if ($this->container->get('entity_type.manager')
      ->hasDefinition('path_alias')) {
      $this->installEntitySchema('path_alias');
    }
    $this->installConfig(['pathauto', 'system', 'node', 'group']);
    $this->installSchema('node', ['node_access']);

    // Create a node type.
    $type = NodeType::create(['type' => 'page']);
    $type->save();
    node_add_body_field($type);

    // Create a group type.
    $group_type = GroupType::create([
      'id' => 'basic_group',
      'label' => 'Basic',
      'new_revision' => TRUE,
      'creator_membership' => TRUE,
      'creator_wizard' => FALSE,
    ]);
    $group_type->save();

    // Create a menu.
    $menu = Menu::create(['id' => 'main_menu', 'title' => 'Main Menu']);
    $menu->save();

    // Create group content menu type.
    $group_menu_type = GroupContentMenuType::create([
      'id' => 'group_menu',
      'label' => 'Group Menu',
    ]);
    $group_menu_type->save();
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    // Create group content from plugin.
    $storage->createFromPlugin($group_type, 'group_content_menu:group_menu')
      ->save();
    $storage->createFromPlugin($group_type, 'group_node:page', [
      'group_cardinality' => 1,
      'entity_cardinality' => 1,
      'use_creation_wizard' => FALSE,
    ])->save();

    // Set config for pathauto to create new aliases
    // and delete old ones (Default).
    $config = $this->config('pathauto.settings');
    $config->set('update_action', PathautoGeneratorInterface::UPDATE_ACTION_DELETE);
    $config->save();

    // Add pathauto patterns.
    $this->nodePattern = $this->createPattern('node', '[node:menu-link:parent:url:path]/[node:title]');
    $this->nodePattern->save();
    $this->groupPattern = $this->createPattern('group', '[group:title]');
    $this->groupPattern->save();
    \Drupal::service('router.builder')->rebuild();

    // Create a new user.
    $this->createUser();
    $this->setCurrentUser($this->createUser());
  }

  /**
   * Test the Group Alias generation process.
   */
  public function testOsuGroupsAlias() {
    // Create first node.
    $node_1 = Node::create([
      'title' => 'About Us',
      'type' => 'page',
    ]);
    $node_1->setPublished();
    $node_1->save();

    // Put it in the menu.
    $link_1 = MenuLinkContent::create([
      'title' => 'About Us',
      'link' => ['uri' => 'entity:/node/' . $node_1->id()],
      'menu_name' => 'main_menu',
      'weight' => 0,
    ]);
    $link_1->save();

    // Create a second node that will be in the group.
    $node_2 = Node::create([
      'title' => 'About Us',
      'type' => 'page',
    ]);
    $node_2->setPublished();
    $node_2->save();

    // Create a group.
    $group = Group::create([
      'title' => 'Digital Experience',
      'type' => 'basic_group',
    ]);
    $group->enforceIsNew();
    $group->setPublished();

    $group->save();
    // Manually set the alias for the group.
    $this->saveEntityAlias($group, '/about-us/dx');

    // Add group menu.
    $group_menu = GroupContentMenu::create([
      'type' => 'group_content_menu',
      'bundle' => 'group_menu',
      'label' => 'Group 1 Menu',
    ]);
    $group_menu->save();

    // Add content to group.
    $group->addContent($group_menu, 'group_content_menu:group_menu');
    $group->addContent($node_2, 'group_node:page');

    // Put group in the menu.
    $group_link = MenuLinkContent::create([
      'title' => 'Digital Experience',
      'link' => ['uri' => 'internal:/group/' . $group->id()],
      'menu_name' => 'main_menu',
      'parent' => 'menu_link_content:' . $link_1->uuid(),
      'weight' => 0,
    ]);
    $group_link->save();

    $group_menu_node_2 = MenuLinkContent::create([
      'title' => 'About Us',
      'link' => ['uri' => 'entity:/node/' . $node_2->id()],
      'menu_name' => GroupContentMenuInterface::MENU_PREFIX . $group_menu->id(),
      'weight' => 0,
    ]);
    $group_menu_node_2->save();

    $this->assertEntityAlias($node_1, '/about-us');
    $this->assertEntityAlias($group, '/about-us/dx');
    $this->assertEntityAlias($node_2, '/about-us/dx/about-us');
  }

}
