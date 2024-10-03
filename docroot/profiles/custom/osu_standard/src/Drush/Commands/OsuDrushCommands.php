<?php

namespace Drupal\osu_standard\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class OsuDrushCommands
 *
 * Provides custom Drush commands for handling aliases in a Drupal site.
 */
class OsuDrushCommands extends DrushCommands
{
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
    protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The batch size to use.
   *
   * @var int
   */
    private int $batchSize = 50;

  /**
   * Construct an OSU Commands object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
    public function __construct(EntityTypeManagerInterface $entityTypeManager)
    {
        $this->entityTypeManager = $entityTypeManager;
    }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager')
        );
    }

  /**
   * Set the "generate aliases automatically" setting for nodes.
   *
   * @param string $entity_type
   *   The entity type (e.g. 'node').
   * @param string|NULL $bundle
   *   (optional) The Bundle to filter by.
   * @param string|NULL $ids
   *   (optional) A CSV string of entity ID's to update.
   *
   * @return void
   * @command osu:set-generate-alias
   * @aliases osugalias
   */
    public function setGenerateAlias(string $entity_type, string $bundle = null, string $ids = null): void
    {
        $this->updateGenerateAlias($entity_type, $bundle, $ids, true);
    }

  /**
   * @param string $entity_type
   * @param string|NULL $bundle
   * @param string|NULL $ids
   * @param bool $generate_alias
   *
   * @return void
   */
    private function updateGenerateAlias(
        string $entity_type,
        string $bundle = null,
        string $ids = null,
        bool $generate_alias
    ): void {
        $storage = $this->entityTypeManager->getStorage($entity_type);
        $query = $storage->getQuery();
      // No access checks needed.
        $query->accessCheck(false);

        if ($bundle) {
            $query->condition('type', $bundle);
        }
        if ($ids) {
            $id_array = array_map('trim', explode(',', $ids));
            switch ($entity_type) {
                case 'node':
                    $query->condition('nid', $id_array, 'IN');
                    break;
                case 'user':
                    $query->condition('uid', $id_array, 'IN');
                    break;
                case 'taxonomy_term':
                    $query->condition('tid', $id_array, 'IN');
                    break;
                default:
                    $query->condition('id', $id_array, 'IN');
                    break;
            }
        }
        $query->range(0, $this->batchSize);
        $total = 0;
        while ($entitie_ids = $query->execute()) {
            $entities = $storage->loadMultiple($entitie_ids);

            foreach ($entities as $entity) {
                $path = $entity->get('path');
                $path->pathauto = $generate_alias;
                $entity->save();
                $total++;
            }
            $this->output->writeln('Processed ' . $total . ' ' . $entity_type . '.');

          // Reset the query for the next batch.
            $query->range($total, $this->batchSize);
        }
        $this->output()
        ->writeln('Total ' . $entity_type . ' processed: ' . $total .
          '. Generate aliases automatically set to ' .
          ($generate_alias ? 'true' : 'false') . '.');
    }

  /**
   * Set the "generate aliases automatically" setting for nodes.
   *
   * @param string $entity_type
   *   The entity type (e.g. 'node').
   * @param string|NULL $bundle
   *   (optional) The Bundle to filter by.
   * @param string|NULL $ids
   *   (optional) A CSV string of entity ID's to update.
   *
   * @return void
   *
   * @command osu:unset-generate-alias
   * @aliases osuusgalias
   */
    public function unsetGenerateAlias(string $entity_type, string $bundle = null, string $ids = null): void
    {
        $this->updateGenerateAlias($entity_type, $bundle, $ids, false);
    }
}
