<?php

namespace Drupal\Tests\products\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Core\File\FileSystemInterface;

/**
 * Tests the CSV Product Importer.
 *
 * @group products
 */
class CsvImporterTest extends KernelTestBase {


  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'csv_importer_test',
    'products',
    'image',
    'file',
    'user',
  ];

  /**
   * Tests the import of the CSV based plugin.
   */
  public function testImport() {
    $this->installEntitySchema('product');
    $this->installEntitySchema('file');
    $this->installSchema('file', 'file_usage');
    $entity_type_manager = $this->container->get('entity_type.manager');
    // Assert we have no products in the system.
    $products = $entity_type_manager->getStorage('product')->loadMultiple();
    $this->assertEmpty($products);

    $csv_path = drupal_get_path('module', 'csv_importer_test') . '/products.csv';
    $csv_contents = file_get_contents($csv_path);
    $file = file_save_data($csv_contents, 'public://simpletest-products.csv', FileSystemInterface::EXISTS_REPLACE);
    $config = $entity_type_manager->getStorage('importer')->create([
      'id' => 'csv',
      'label' => 'CSV',
      'plugin' => 'csv',
      'plugin_configuration' => [
        'file' => [$file->id()],
      ],
      'source' => 'Testing',
      'bundle' => 'goods',
      'update_existing' => TRUE,
    ]);
    $config->save();

    $plugin = $this->container->get('products.importer_manager')->createInstanceFromConfig('csv');
    $plugin->import();
    $products = $entity_type_manager->getStorage('product')->loadMultiple();
    $this->assertCount(2, $products);

    $products = $entity_type_manager->getStorage('product')->loadByProperties(['number' => 45345]);
    $this->assertNotEmpty($products);
    $this->assertCount(1, $products);
  }

}
