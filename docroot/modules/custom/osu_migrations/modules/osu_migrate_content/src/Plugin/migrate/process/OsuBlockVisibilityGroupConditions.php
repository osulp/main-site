<?php

namespace Drupal\osu_migrate_content\Plugin\migrate\process;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Process Plugin to Work on Context to Block Visibility Group Conditions.
 *
 * @MigrateProcessPlugin (
 *   id = "osu_block_visibility_group_conditions"
 * )
 */
class OsuBlockVisibilityGroupConditions extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The Entity Type Bundle.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private EntityStorageInterface $nodeType;

  /**
   * The UUID Service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  private UuidInterface $uuid;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $nodeType, UuidInterface $uuid) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->nodeType = $nodeType;
    $this->uuid = $uuid;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get("entity_type.manager")->getStorage("node_type"),
      $container->get('uuid')
    );
  }

  /**
   * {@inheritDoc}
   *
   * Convert the context conditions into block visibility group conditions.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $context_condition = unserialize($value, ['allowed_classes' => FALSE]);
    $node_bundles = $this->nodeType->loadMultiple();
    foreach ($node_bundles as $node_name => $node_type) {
      $node_bundles[$node_name] = $node_name;
    }
    $bvg_conditions = [];
    foreach ($context_condition as $context_type => $condition) {
      switch ($context_type) {
        case "path":
          $negatedPaths = preg_replace("/^~/", '/',
            preg_grep("/^~/", $condition["values"]));
          // $0 matches the entire string that contains our match of not
          // starting with a /
          $positivePaths = preg_replace("/^[^\/]/", "/$0",
            preg_grep("/^~/", $condition["values"], PREG_GREP_INVERT));
          if (count($positivePaths) > 0) {
            $uuid = $this->uuid->generate();
            $bvg_conditions[$uuid] = [
              "id" => "request_path",
              "pages" => implode("\r\n", $positivePaths),
              "negate" => FALSE,
              "uuid" => $uuid,
            ];
          }
          if (count($negatedPaths) > 0) {
            $uuid = $this->uuid->generate();
            $bvg_conditions[$uuid] = [
              "id" => "request_path",
              "pages" => implode("\r\n", $negatedPaths),
              "negate" => TRUE,
              "uuid" => $uuid,
            ];
          }
          break;

        case "node":
          $old_node_bundle = $condition["values"];
          if (array_key_exists("book", $old_node_bundle)) {
            unset($old_node_bundle["book"]);
            $old_node_bundle["page"] = "page";
          }
          $new_node_bundles = array_intersect($node_bundles, $old_node_bundle);
          if (count($new_node_bundles) > 0) {
            $uuid = $this->uuid->generate();
            $bvg_conditions[$uuid] = [
              "id" => "entity_bundle:node",
              "context_mapping" => [
                "node" => "@node.node_route_context:node",
              ],
              "negate" => FALSE,
              "uuid" => $uuid,
              "bundles" => $new_node_bundles,
            ];
          }
          break;

        case "user":
          $uuid = $this->uuid->generate();
          $bvg_conditions[$uuid] = [
            "id" => "user_role",
            "negate" => FALSE,
            "uuid" => $uuid,
            "context_mapping" => [
              "user" => "@user.current_user_context:current_user",
            ],
            "roles" => [
              "authenticated" => "authenticated",
            ],
          ];
          break;

        case "sitewide":
          $uuid = $this->uuid->generate();
          $bvg_conditions[$uuid] = [
            "id" => "request_path",
            "pages" => "*",
            "negate" => FALSE,
            "uuid" => $uuid,
          ];
          break;
      }
    }
    return $bvg_conditions;
  }

}
