<?php

namespace Drupal\hello_world\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access handler for the Hello World route.
 */
class HelloWorldAccess implements AccessInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * HelloWorldAccess constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Handles the access checking.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(AccountInterface $account) {
    $config = $this->configFactory->get('hello_world.custom_salutation');
    $salutation = $config->get('salutation');
    $access = in_array('editor', $account->getRoles()) && $salutation != "" ? AccessResult::forbidden() : AccessResult::allowed();
    $access->addCacheableDependency($config);
    $access->addCacheContexts(['user.roles']);
    return $access;
  }

}
