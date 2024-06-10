<?php

namespace Drupal\osu_user_to_profiles\EventSubscriber;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Event\MigrateRollbackEvent;
use Drupal\redirect\RedirectRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * OSU User Profile Migration event subscriber.
 */
class OsuUserToProfilesSubscriber implements EventSubscriberInterface {

  /**
   * A database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private Connection $migrateConnection;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private LanguageManagerInterface $languageManager;

  /**
   * @var \Drupal\redirect\RedirectRepository
   */
  private RedirectRepository $redirectRepository;

  /**
   * Creates a new Event Subscriber.
   *
   * @param \Drupal\Core\Database\Connection $migrateConnection
   *   The Migrate Database Connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   * @param \Drupal\redirect\RedirectRepository $redirectRepository
   */
  public function __construct(Connection $migrateConnection, EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $languageManager, RedirectRepository $redirectRepository) {
    $this->migrateConnection = $migrateConnection;
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
    $this->redirectRepository = $redirectRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      MigrateEvents::POST_ROW_SAVE => 'onPostRowSave',
      MigrateEvents::POST_ROLLBACK => 'onPostRollback',
    ];
  }

  /**
   * Add redirect from users/name to directory/name.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   The migration post row save event.
   */
  public function onPostRowSave(MigratePostRowSaveEvent $event) {
    if ($event->getMigration()->getBaseId() === 'upgrade_d7_user_to_profile') {
      $uid = $event->getRow()->getSourceProperty('uid');
      // Get the new node id of the new profile page.
      $destinationNid = $event->getDestinationIdValues()[0];
      // Load the node entity, so we can get its url.
      /** @var \Drupal\node\Entity\Node $destinationNode */
      $destinationNode = $this->entityTypeManager->getStorage('node')
        ->load($destinationNid);
      $nodeUrl = $destinationNode->toUrl()->toString();
      // Find all old alias to the old user account.
      $query = $this->migrateConnection->select('url_alias', 'urla');
      $query->fields('urla', ['alias']);
      $query->condition('urla.source', 'user/' . $uid);
      $old_aliases = $query->execute()->fetchAll();

      // Get the language ID, so we can set it in the redirect.
      $currentLanguageId = $this->languageManager->getCurrentLanguage()
        ->getId();
      // Loop over each old alias to create a redirect to the profile page.
      foreach ($old_aliases as $old_alias) {
        // Create a new redirect Entity.
        $existingRedirect = $this->redirectRepository->findBySourcePath($old_alias->alias);
        if (empty($existingRedirect)) {
          $redirectEntity = $this->entityTypeManager->getStorage('redirect')
            ->create();
          $redirectEntity->setSource($old_alias->alias);
          $redirectEntity->setLanguage($currentLanguageId);
          $redirectEntity->setRedirect($nodeUrl);
          $redirectEntity->setStatusCode(301);
          $redirectEntity->save();
        }
      }
    }
  }

  /**
   * Remove the custom redirects we made.
   *
   * @param \Drupal\migrate\Event\MigrateRollbackEvent $migrateRollbackEvent
   *
   * @return void
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function onPostRollback(MigrateRollbackEvent $migrateRollbackEvent) {
    if ($migrateRollbackEvent->getMigration()
      ->getPluginId() === 'upgrade_d7_user_to_profile') {
      /** @var \Drupal\redirect\Entity\Redirect[] $userOldRedirects */
      $userOldRedirects = $this->redirectRepository->findBySourcePath('users/%');
      foreach ($userOldRedirects as $userOldRedirect) {
        $userOldRedirect->delete();
      }
    }
  }

}
