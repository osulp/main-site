<?php

namespace Drupal\osu_migrate_content\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\MigrateLookupInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * OSU Context to Block Visibility Group Event Subscriber.
 */
class BlockVisibilityGroupsPostRowMigrate implements EventSubscriberInterface {

  /**
   * The Entity Type Manager Service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * The Migration Lookup Service.
   *
   * @var \Drupal\migrate\MigrateLookupInterface
   */
  private MigrateLookupInterface $migrateLookup;

  /**
   * Creates a new Event Subscriber.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\migrate\MigrateLookupInterface $migrateLookup
   *   The Migration Lookup Interface.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, MigrateLookupInterface $migrateLookup) {
    $this->entityTypeManager = $entityTypeManager;
    $this->migrateLookup = $migrateLookup;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    return [MigrateEvents::POST_ROW_SAVE => 'onPostRowSave'];
  }

  /**
   * Create the blocks and set the Visibility Group.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   The migration import event.
   */
  public function onPostRowSave(MigratePostRowSaveEvent $event) {
    if ($event->getMigration()->getBaseId() === "upgrade_d7_context") {
      // Load the reactions.
      $context_reactions = unserialize($event->getRow()
        ->getSourceProperty("reactions"), ["allowed_classes" => FALSE]);
      // Get the Block Visibility Group Machine Name.
      $block_visibility_group_name = $event->getRow()
        ->getDestinationProperty("id");
      /** @var Drupal\Core\Config\Entity\ConfigEntityStorage $block_storage */
      $block_storage = $this->entityTypeManager->getStorage('block');
      $old_blocks = [];
      if (isset($context_reactions["block"])) {
        $old_blocks = $context_reactions["block"]["blocks"];
      }
      foreach ($old_blocks as $block_config) {
        if ($block_config["module"] === "block") {
          $newBLocks = $this->migrateLookup->lookup('upgrade_d7_custom_block', [$block_config["delta"]])[0];
          if (!empty($newBLocks)) {
            $block_content = $this->entityTypeManager->getStorage('block_content')
              ->load($newBLocks["id"]);
            $block_content->uuid();
            $region = $this->getRegion($block_config["region"]);
            $block_id = $block_visibility_group_name . '_' . preg_replace("/[^a-z0-9_]+/", '_', $block_config["delta"]);
            if (strlen($block_id) > 255) {
              $block_id = substr($block_id, 0, 255);
            }
            // Create a new block placement and save it.
            $new_block_placement = $block_storage->create([
              'id' => $block_id,
              'theme' => 'madrone',
              'plugin' => 'block_content:' . $block_content->uuid(),
              'weight' => $block_config["weight"],
              'region' => $region,
              'visibility' => [
                'condition_group' => [
                  "id" => "condition_group",
                  "negate" => FALSE,
                  "block_visibility_group" => $block_visibility_group_name,
                ],
              ],
            ]);
            $new_block_placement->save();
          }
        }
        elseif ($block_config["module"] === "views") {
          $view_storage = $this->entityTypeManager->getStorage('view');
          $view_name = explode('-', $block_config["delta"])[0];
          $block_id = $block_visibility_group_name . '_' . preg_replace("/[^a-z0-9_]+/", '_', $block_config["delta"]);
          if (strlen($block_id) > 255) {
            $block_id = substr($block_id, 0, 255);
          }
          // Make sure the view exists.
          if ($view_storage->load($view_name) !== NULL) {
            $region = $this->getRegion($block_config["region"]);
            $new_block_placement = $block_storage->create([
              'id' => $block_id,
              'theme' => 'madrone',
              'plugin' => 'views_block:' . $block_config["delta"],
              'weight' => $block_config["weight"],
              'region' => $region,
              'visibility' => [
                'condition_group' => [
                  "id" => "condition_group",
                  "negate" => FALSE,
                  "block_visibility_group" => $block_visibility_group_name,
                ],
              ],
            ]);
            $new_block_placement->save();
          }
        }
        elseif ($block_config["module"] === 'menu') {
          $menu_storage = $this->entityTypeManager->getStorage('menu');
          /** @var \Drupal\system\Entity\Menu $menu_block */
          $menu_block = $menu_storage->load($block_config['delta']);
          $region = $this->getRegion($block_config["region"]);
          $block_id = $block_visibility_group_name . '_' . preg_replace("/[^a-z0-9_]+/", '_', $block_config["delta"]);
          if (strlen($block_id) > 255) {
            $block_id = substr($block_id, 0, 255);
          }
          if ($menu_block !== NULL) {
            // Create a new block placement and save it.
            $new_block_placement = $block_storage->create([
              'id' => $block_id,
              'theme' => 'madrone',
              'plugin' => 'system_menu_block:' . $block_config["delta"],
              'weight' => $block_config["weight"],
              'region' => $region,
              'settings' => [
                'id' => 'system_menu_block:' . $block_config["delta"],
                'label' => $menu_block->get('label'),
                'label_display' => 'visible',
                'provider' => 'system',
                'level' => 1,
                'depth' => 0,
                'expand_all_items' => FALSE,
              ],
              'visibility' => [
                'condition_group' => [
                  "id" => "condition_group",
                  "negate" => FALSE,
                  "block_visibility_group" => $block_visibility_group_name,
                ],
              ],
            ]);
            $new_block_placement->save();
          }
        }
      }
    }
  }

  /**
   * Return the update region names.
   *
   * @param string $old_region
   *   The old region name.
   *
   * @return string
   *   The new region name.
   */
  private function getRegion(string $old_region) {
    return match ($old_region) {
      'nav' => 'primary_menu',
      'help' => 'help',
      'features' => 'highlighted',
      'pre_content' => 'full_top',
      'sidebar_first', 'sidebar_second' => 'sidebar',
      'pre_footer' => 'pre_footer',
      'footer' => 'footer',
      default => 'content',
    };
  }

}
