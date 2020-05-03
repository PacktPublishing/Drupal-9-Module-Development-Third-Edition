<?php

namespace Drupal\user_types\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;

/**
 * Access handler for the User Types routes.
 */
class UserTypesAccess implements AccessInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * UserTypesAccess constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Handles the access checking.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   * @param \Symfony\Component\Routing\Route $route
   *   The route.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(AccountInterface $account, Route $route) {
    $user_types = $route->getOption('_user_types');
    if (!$user_types) {
      return AccessResult::forbidden();
    }
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }
    $user = $this->entityTypeManager->getStorage('user')->load($account->id());
    $type = $user->get('field_user_type')->value;
    return in_array($type, $user_types) ? AccessResult::allowed() : AccessResult::forbidden();
  }

}
