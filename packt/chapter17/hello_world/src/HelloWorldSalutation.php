<?php

namespace Drupal\hello_world;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Prepares the salutation to the world.
 */
class HelloWorldSalutation {

  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The cache kill switch.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * HelloWorldSalutation constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $killSwitch
   *   The cache kill switch.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EventDispatcherInterface $eventDispatcher, KillSwitch $killSwitch) {
    $this->configFactory = $config_factory;
    $this->eventDispatcher = $eventDispatcher;
    $this->killSwitch = $killSwitch;
  }

  /**
   * Returns the salutation.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The salutation message.
   */
  public function getSalutation() {
    $this->killSwitch->trigger();
    $config = $this->configFactory->get('hello_world.custom_salutation');
    $salutation = $config->get('salutation');
    if ($salutation !== "" && $salutation) {
      $event = new SalutationEvent();
      $event->setValue($salutation);
      $this->eventDispatcher->dispatch(SalutationEvent::EVENT, $event);
      return $event->getValue();
    }

    $time = new \DateTime();
    if ((int) $time->format('G') >= 00 && (int) $time->format('G') < 12) {
      return $this->t('Good morning world');
    }

    if ((int) $time->format('G') >= 12 && (int) $time->format('G') < 18) {
      return $this->t('Good afternoon world');
    }

    if ((int) $time->format('G') >= 18) {
      return $this->t('Good evening world');
    }
  }

  /**
   * Returns the Salutation render array.
   */
  public function getSalutationComponent() {
    $this->killSwitch->trigger();
    $render = [
      '#theme' => 'hello_world_salutation',
      '#salutation' => [
        '#contextual_links' => [
          'hello_world' => [
            'route_parameters' => [],
          ],
        ],
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    $config = $this->configFactory->get('hello_world.custom_salutation');
    $salutation = $config->get('salutation');

    if ($salutation !== "" && $salutation) {
      $event = new SalutationEvent();
      $event->setValue($salutation);
      $this->eventDispatcher->dispatch(SalutationEvent::EVENT, $event);
      $render['#salutation']['#markup'] = $event->getValue();
      $render['#overridden'] = TRUE;
      return $render;
    }

    $time = new \DateTime();
    $render['#target'] = $this->t('world');
    $render['#attached'] = [
      'library' => [
        'hello_world/hello_world_clock',
      ],
    ];

    if ((int) $time->format('G') >= 00 && (int) $time->format('G') < 12) {
      $render['#salutation']['#markup'] = $this->t('Good morning');
      return $render;
    }

    if ((int) $time->format('G') >= 12 && (int) $time->format('G') < 18) {
      $render['#salutation']['#markup'] = $this->t('Good afternoon');
      $render['#attached']['drupalSettings']['hello_world']['hello_world_clock']['afternoon'] = TRUE;
      return $render;
    }

    if ((int) $time->format('G') >= 18) {
      $render['#salutation']['#markup'] = $this->t('Good evening');
      return $render;
    }
  }

}
