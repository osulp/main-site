<?php

namespace Drupal\osu_user_accounts\Plugin\migrate\source;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\user\Plugin\migrate\source\d7\User;

/**
 * Provide a source plugin to limit the user migration to those who have roles.
 *
 * @MigrateSource(
 *   id = "d7_user_filtered",
 *   source_module = "user"
 * )
 *
 * Examples:
 *
 * Example usage that requires users to have roles "editor" or "author".
 *
 * @code
 * source:
 *   plugin: d7_user_role_filter
 *   roles_name:
 *     - editor
 *     - author
 * @endcode
 */
class UserFiltered extends User {

  /**
   * List of roles ('name' values) which will be migrated.
   *
   * @var array
   */
  protected $requiredRolesName = [];

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager);
    $this->requiredRolesName = (array) $configuration['role_names'];
  }

  /**
   * {@inheritDoc}
   */
  public function query() {
    $query = parent::query();
    $query->innerJoin('users_roles', 'ur', 'u.uid = ur.uid');
    $query->innerJoin('role', 'r', 'ur.rid = r.rid');
    $query->condition('r.name', $this->requiredRolesName, 'IN');
    $query->distinct(TRUE);
    return $query;
  }

}
