<?php

namespace Drupal\hello_world;

use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Lazy builder for the Hello World salutation.
 */
class HelloWorldLazyBuilder implements TrustedCallbackInterface {

  /**
   * @var \Drupal\hello_world\HelloWorldSalutation
   */
  protected $salutation;

  /**
   * HelloWorldLazyBuilder constructor.
   *
   * @param \Drupal\hello_world\HelloWorldSalutation $salutation
   */
  public function __construct(HelloWorldSalutation $salutation) {
    $this->salutation = $salutation;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['renderSalutation'];
  }

  /**
   * Renders the Hello World salutation message.
   */
  public function renderSalutation() {
    return $this->salutation->getSalutationComponent();
  }

}
