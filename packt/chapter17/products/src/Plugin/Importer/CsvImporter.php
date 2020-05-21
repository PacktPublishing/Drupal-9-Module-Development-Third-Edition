<?php

namespace Drupal\products\Plugin\Importer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\products\Plugin\ImporterBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Product importer from a CSV format.
 *
 * @Importer(
 *   id = "csv",
 *   label = @Translation("CSV Importer")
 * )
 */
class CsvImporter extends ImporterBase {

  use StringTranslationTrait;

  /**
   * The stream wrapper manager.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface
   */
  protected $streamWrapperManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, ClientInterface $httpClient, MessengerInterface $messenger, StreamWrapperManagerInterface $streamWrapperManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entityTypeManager, $httpClient, $messenger);
    $this->streamWrapperManager = $streamWrapperManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('http_client'),
      $container->get('messenger'),
      $container->get('stream_wrapper_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'file' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['file'] = [
      '#type' => 'managed_file',
      '#default_value' => $this->configuration['file'],
      '#title' => $this->t('File'),
      '#description' => $this->t('The CSV file containing the product records.'),
      '#required' => TRUE,
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['file'] = $form_state->getValue('file');
  }

  /**
   * {@inheritdoc}
   */
  public function import() {
    $products = $this->getData();
    if (!$products) {
      return FALSE;
    }

    foreach ($products as $product) {
      $this->persistProduct($product);
    }

    return TRUE;
  }

  /**
   * Saves a Product entity from the remote data.
   *
   * @param object $data
   *   The loaded data to import.
   */
  private function persistProduct($data) {
    /** @var \Drupal\products\Entity\ImporterInterface $config */
    $config = $this->configuration['config'];

    $existing = $this->entityTypeManager->getStorage('product')
      ->loadByProperties([
        'remote_id' => $data->id,
        'source' => $config->getSource(),
      ]);
    if (!$existing) {
      $values = [
        'remote_id' => $data->id,
        'source' => $config->getSource(),
        'type' => $config->getBundle(),
      ];
      /** @var \Drupal\products\Entity\ProductInterface $product */
      $product = $this->entityTypeManager->getStorage('product')
        ->create($values);
      $product->setName($data->name);
      $product->setProductNumber($data->product_number);
      $product->save();

      return;
    }

    if (!$config->updateExisting()) {
      return;
    }

    /** @var \Drupal\products\Entity\ProductInterface $product */
    $product = reset($existing);
    $product->setName($data->name);
    $product->setProductNumber($data->product_number);
    $product->save();
  }

  /**
   * Loads the product data from the CSV file.
   *
   * @return array
   *   The product data.
   */
  private function getData() {
    /** @var \Drupal\products\Entity\ImporterInterface $importer_config */
    $importer_config = $this->configuration['config'];
    $fids = $this->configuration['file'];
    if (!$fids) {
      return [];
    }

    $fid = reset($fids);
    /** @var \Drupal\file\FileInterface $file */
    $file = $this->entityTypeManager->getStorage('file')->load($fid);
    $wrapper = $this->streamWrapperManager->getViaUri($file->getFileUri());
    if (!$wrapper) {
      return [];
    }

    $url = $wrapper->realpath();
    $spl = new \SplFileObject($url, 'r');
    $data = [];
    while (!$spl->eof()) {
      $data[] = $spl->fgetcsv();
    }

    $products = [];
    $header = [];
    foreach ($data as $key => $row) {
      if ($key == 0) {
        $header = $row;
        continue;
      }

      if ($row[0] == "") {
        continue;
      }

      $product = new \stdClass();
      foreach ($header as $header_key => $label) {
        $product->{$label} = $row[$header_key];
      }
      $products[] = $product;
    }

    return $products;
  }

}
