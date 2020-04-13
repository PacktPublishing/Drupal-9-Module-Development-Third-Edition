<?php

namespace Drupal\sports\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the Sports module.
 */
class SportsController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * SportsController constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Renders a table of players.
   *
   * @return array
   *   The players list.
   */
  public function players() {
    // The number of items per page.
    $limit = 5;
    $query = $this->database->select('players', 'p')
      ->fields('p')
      ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit($limit);

    $result = $query->execute()->fetchAll();
    $header = [$this->t('Name')];
    $rows = [];

    foreach ($result as $row) {
      $rows[] = [
        $row->name,
      ];
    }

    $build = [];
    $build[] = [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];

    $build[] = [
      '#type' => 'pager',
    ];

    return $build;
  }

}
