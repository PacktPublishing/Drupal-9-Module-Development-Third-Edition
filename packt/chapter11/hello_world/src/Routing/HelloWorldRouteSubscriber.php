<?php

namespace Drupal\hello_world\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscribes to route events for the Hello World module.
 */
class HelloWorldRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $route = $collection->get('user.register');
    if (!$route) {
      return;
    }

    // Example 1:
    // We deny access to the Register page in all cases. With this requirement,
    // it doesn't matter anymore what other access requirements exist or if they
    // evaluate positively.
    $route->setRequirement('_access', 'FALSE');

    // Example 2:
    // We check for the presence of a specific access requirement and, if it
    // exists,  we clear all the access requirements on the route and set our
    // own.
    if ($route->hasRequirement('_access_user_register')) {
      $route->setRequirements([]);
      $route->setRequirement('_user_types_access_check', 'TRUE');
    }
  }

}
