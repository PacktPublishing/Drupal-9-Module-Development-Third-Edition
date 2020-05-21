<?php

namespace Drupal\Tests\user_types\Unit;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\Session\UserSession;
use Symfony\Component\Routing\Route;
use Drupal\user_types\Access\UserTypesAccess;

/**
 * Tests the UserTypesAccess class methods.
 *
 * @group user_types
 */
class UserTypesAccessTest extends UnitTestCase {

  /**
   * Tests the UserTypesAccess::access() method.
   */
  public function testAccess() {
    // User entity mock.
    $type = new \stdClass();
    $type->value = 'manager';
    $user = $this->getMockBuilder('Drupal\user\Entity\User')
      ->disableOriginalConstructor()
      ->getMock();
    $user->expects($this->any())
      ->method('get')
      ->will($this->returnValue($type));

    // User storage mock.
    $user_storage = $this->getMockBuilder('Drupal\user\UserStorage')
      ->disableOriginalConstructor()
      ->getMock();
    $user_storage->expects($this->any())
      ->method('load')
      ->will($this->returnValue($user));

    // Entity type manager mock.
    $entity_type_manager = $this->getMockBuilder('Drupal\Core\Entity\EntityTypeManager')
      ->disableOriginalConstructor()
      ->getMock();
    $entity_type_manager->expects($this->any())
      ->method('getStorage')
      ->will($this->returnValue($user_storage));

    // User accounts.
    $anonymous = new UserSession(['uid' => 0]);
    $registered = new UserSession(['uid' => 2]);

    // Route definitions.
    $manager_route = new Route('/test_manager', [], [], ['_user_types' => ['manager']]);
    $board_route = new Route('/test_board', [], [], ['_user_types' => ['board']]);
    $none_route = new Route('/test_board');

    $access = new UserTypesAccess($entity_type_manager);

    // Access denied due to lack of route option.
    $this->assertInstanceOf(AccessResultForbidden::class, $access->access($registered, $none_route));

    // Access denied due to user being anonymous on any of the routes.
    $this->assertInstanceOf(AccessResultForbidden::class, $access->access($anonymous, $manager_route));
    $this->assertInstanceOf(AccessResultForbidden::class, $access->access($anonymous, $board_route));

    // Access denied due to user not having proper field value.
    $this->assertInstanceOf(AccessResultForbidden::class, $access->access($registered, $board_route));

    // Access allowed due to user having the proper field value.
    $this->assertInstanceOf(AccessResultAllowed::class, $access->access($registered, $manager_route));
  }

}
