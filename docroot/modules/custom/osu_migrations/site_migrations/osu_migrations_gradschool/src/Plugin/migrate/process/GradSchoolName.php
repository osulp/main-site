<?php

namespace Drupal\osu_migrations_gradschool\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Forces the Name into a split that will actually work for our needs.
 *
 * @MigrateProcessPlugin(
 *   id = "gradschool_name",
 *   handle_multiples = TRUE
 * )
 */
class GradSchoolName extends ProcessPluginBase {

  /**
   * {@inheritDoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $name = explode(' ', $value, 2);
    if ($this->configuration['name'] === 'first') {
      return $name[0];
    }
    elseif ($this->configuration['name'] === 'last') {
      if (count($name) < 2) {
        return 'Last Name Blank';
      }
      return $name[1];
    }
    throw new MigrateException('delimiter is empty');
  }

}
