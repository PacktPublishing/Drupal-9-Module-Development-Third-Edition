<?php

namespace Drupal\Tests\sports\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\sports\Plugin\QueueWorker\TeamCleaner;

/**
 * Test the TeamCleaner QueueWorker plugin.
 *
 * @group sports
 */
class TeamCleanerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['sports'];

  /**
   * Tests the TeamCleaner::processItem() method.
   */
  public function testProcessItem() {
    $this->installSchema('sports', 'teams');
    $database = $this->container->get('database');
    $fields = ['name' => 'Team name'];
    $id = $database->insert('teams')
      ->fields($fields)
      ->execute();

    $records = $database->query("SELECT id FROM {teams} WHERE id = :id", [':id' => $id])->fetchAll();
    $this->assertNotEmpty($records);

    $worker = new TeamCleaner([], NULL, NULL, $database);
    $data = new \stdClass();
    $data->id = $id;
    $worker->processItem($data);
    $records = $database->query("SELECT id FROM {teams} WHERE id = :id", [':id' => $id])->fetchAll();
    $this->assertEmpty($records);
  }

}
