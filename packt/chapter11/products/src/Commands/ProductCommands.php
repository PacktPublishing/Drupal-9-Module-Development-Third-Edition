<?php

namespace Drupal\products\Commands;

use Drupal\products\Plugin\ImporterPluginInterface;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Input\InputOption;
use Drupal\products\Plugin\ImporterManager;

/**
 * Drush commands for products.
 */
class ProductCommands extends DrushCommands {

  /**
   * The importer plugin manager.
   *
   * @var \Drupal\products\Plugin\ImporterManager
   */
  protected $importerManager;

  /**
   * ProductCommands constructor.
   *
   * @param \Drupal\products\Plugin\ImporterManager $importerManager
   *   The importer plugin manager.
   */
  public function __construct(ImporterManager $importerManager) {
    $this->importerManager = $importerManager;
  }

  /**
   * Imports the Products.
   *
   * @param array $options
   *   The command options.
   *
   * @option importer
   *   The importer config ID to use.
   *
   * @command products-import-run
   * @aliases pir
   */
  public function import(array $options = ['importer' => InputOption::VALUE_OPTIONAL]) {
    $importer = $options['importer'];

    if (!is_null($importer)) {
      $plugin = $this->importerManager->createInstanceFromConfig($importer);
      if (is_null($plugin)) {
        $this->logger()->log('error', t('The specified importer does not exist.'));
        return;
      }

      $this->runPluginImport($plugin);
      return;
    }

    $plugins = $this->importerManager->createInstanceFromAllConfigs();
    if (!$plugins) {
      $this->logger()->log('error', t('There are no importers to run.'));
      return;
    }

    foreach ($plugins as $plugin) {
      $this->runPluginImport($plugin);
    }
  }

  /**
   * Runs an individual Importer plugin.
   */
  protected function runPluginImport(ImporterPluginInterface $plugin) {
    $result = $plugin->import();
    $message_values = ['@importer' => $plugin->getConfig()->label()];
    if ($result) {
      $this->logger()->log('status', t('The "@importer" importer has been run.', $message_values));
      return;
    }

    $this->logger()->log('error', t('There was a problem running the "@importer" importer.', $message_values));
  }

}
