<?php

namespace Drupal\og_to_group\Plugin\migrate\process;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_plus\Plugin\migrate\process\EntityLookup;

/**
 * This plugin looks for the taxonomy term by the title in the og_membership.
 *
 * @MigrateProcessPlugin(
 *   id = "og_entity_lookup",
 *   source_module = "og"
 * )
 *
 * To filter on a specific field name that the OG membership uses.
 *
 * @code
 * source:
 *   plugin: og_entity_lookup
 *   field_name: my_field_name
 * @endcode
 */
class OgEntityLookup extends EntityLookup implements ContainerFactoryPluginInterface {

  /**
   * The Migrate Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private Connection $migrateDb;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->migrateDb = Database::getConnection('default', 'migrate');
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $query = $this->migrateDb->select('og_membership', 'ogm');
    $query->innerJoin('node', 'n', 'n.nid = ogm.gid');
    $query->addField('n', 'title');
    $query->condition('ogm.etid', $value);
    $query->condition('ogm.entity_type', 'node');
    // Check if 'field_name' is provided in the configuration.
    if (isset($this->configuration['field_name'])) {
      $queryField = $this->configuration['field_name'];
      $query->condition('ogm.field_name', $queryField);
    }
    $title = $query->execute()->fetchField();

    if (empty($title)) {
      throw new MigrateException("Could not find source term $value.");
    }
    return $title;
  }

}
