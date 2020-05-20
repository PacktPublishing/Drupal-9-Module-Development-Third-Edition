<?php

namespace Drupal\products\Plugin\Importer;

use Drupal\products\Plugin\ImporterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\products\Entity\ProductInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 * Product importer from a JSON format.
 *
 * @Importer(
 *   id = "json",
 *   label = @Translation("JSON Importer")
 * )
 */
class JsonImporter extends ImporterBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'url' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['url'] = [
      '#type' => 'url',
      '#default_value' => $this->configuration['url'],
      '#title' => $this->t('Url'),
      '#description' => $this->t('The URL to the import resource'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['url'] = $form_state->getValue('url');
  }

  /**
   * {@inheritdoc}
   */
  public function import() {
    $data = $this->getData();
    if (!$data) {
      return FALSE;
    }

    if (!isset($data->products)) {
      return FALSE;
    }

    $products = $data->products;

    $batch_builder = (new BatchBuilder())
      ->setTitle($this->t('Importing products'))
      ->setFinishCallback([$this, 'importProductsFinished']);

    $batch_builder->addOperation([$this, 'clearMissing'], [$products]);
    $batch_builder->addOperation([$this, 'importProducts'], [$products]);
    batch_set($batch_builder->toArray());
    if (PHP_SAPI == 'cli') {
      drush_backend_batch_process();
    }

    return TRUE;
  }

  /**
   * Loads the product data from the remote URL.
   *
   * @return array
   *   The loaded data.
   */
  private function getData() {
    $request = $this->httpClient->get($this->configuration['url']);
    $string = $request->getBody()->getContents();
    return json_decode($string);
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

    $existing = $this->entityTypeManager->getStorage('product')->loadByProperties(['remote_id' => $data->id, 'source' => $config->getSource()]);
    if (!$existing) {
      $values = [
        'remote_id' => $data->id,
        'source' => $config->getSource(),
        'type' => $config->getBundle(),
      ];
      /** @var \Drupal\products\Entity\ProductInterface $product */
      $product = $this->entityTypeManager->getStorage('product')->create($values);
      $product->setName($data->name);
      $product->setProductNumber($data->number);
      $this->handleProductImage($data, $product);
      $product->save();
      return;
    }

    if (!$config->updateExisting()) {
      return;
    }

    /** @var \Drupal\products\Entity\ProductInterface $product */
    $product = reset($existing);
    $product->setName($data->name);
    $product->setProductNumber($data->number);
    $this->handleProductImage($data, $product);
    $product->save();
  }

  /**
   * Imports the image of the product and adds it to the Product entity.
   *
   * @param object $data
   *   The product data.
   * @param \Drupal\products\Entity\ProductInterface $product
   *   The product entity.
   */
  protected function handleProductImage($data, ProductInterface $product) {
    $name = $data->image;
    // This needs to be hardcoded for the moment.
    $image = file_get_contents('products://' . $name);
    if (!$image) {
      // Perhaps log something.
      return;
    }

    /** @var \Drupal\file\FileInterface $file */
    $file = file_save_data($image, 'public://product_images/' . $name, FileSystemInterface::EXISTS_REPLACE);
    if (!$file) {
      // Something went wrong, perhaps log it.
      return;
    }

    $product->setImage($file->id());
  }

  /**
   * Removes products that no longer exist in the remote source.
   *
   * @param array $products
   *   The products.
   * @param object $context
   *   The context.
   */
  public function clearMissing(array $products, object &$context) {
    if (!isset($context['results']['cleared'])) {
      $context['results']['cleared'] = [];
    }

    if (!$products) {
      return;
    }

    $ids = [];
    foreach ($products as $product) {
      $ids[] = $product->id;
    }

    $ids = $this->entityTypeManager->getStorage('product')->getQuery()
      ->condition('remote_id', $ids, 'NOT IN')
      ->execute();
    if (!$ids) {
      $context['results']['cleared'] = [];
      return;
    }

    $entities = $this->entityTypeManager->getStorage('product')->loadMultiple($ids);

    /** @var \Drupal\products\Entity\ProductInterface $entity */
    foreach ($entities as $entity) {
      $context['results']['cleared'][] = $entity->getName();
    }
    $context['message'] = $this->t('Removing @count products', ['@count' => count($entities)]);
    $this->entityTypeManager->getStorage('product')->delete($entities);
  }

  /**
   * Batch operation to import the products from the JSON file.
   *
   * @param array $products
   *   The products.
   * @param object $context
   *   The context.
   */
  public function importProducts(array $products, &$context) {
    if (!isset($context['results']['imported'])) {
      $context['results']['imported'] = [];
    }

    if (!$products) {
      return;
    }

    $sandbox = &$context['sandbox'];
    if (!$sandbox) {
      $sandbox['progress'] = 0;
      $sandbox['max'] = count($products);
      $sandbox['products'] = $products;
    }

    $slice = array_splice($sandbox['products'], 0, 3);
    foreach ($slice as $product) {
      $context['message'] = $this->t('Importing product @name', ['@name' => $product->name]);
      $this->persistProduct($product);
      $context['results']['imported'][] = $product->name;
      $sandbox['progress']++;
    }

    $context['finished'] = $sandbox['progress'] / $sandbox['max'];
  }

  /**
   * Callback for when the batch processing completes.
   *
   * @param bool $success
   *   Whether the batch was successful.
   * @param array $results
   *   The batch results.
   * @param array $operations
   *   The batch operations.
   */
  public function importProductsFinished($success, array $results, array $operations) {
    if (!$success) {
      $this->messenger->addStatus($this->t('There was a problem with the batch'), 'error');
      return;
    }

    $cleared = count($results['cleared']);
    if ($cleared == 0) {
      $this->messenger->addStatus($this->t('No products had to be deleted.'));
    }
    else {
      $this->messenger->addStatus($this->formatPlural($cleared, '1 product had to be deleted.', '@count products had to be deleted.'));
    }

    $imported = count($results['imported']);
    if ($imported == 0) {
      $this->messenger->addStatus($this->t('No products found to be imported.'));
    }
    else {
      $this->messenger->addStatus($this->formatPlural($imported, '1 product imported.', '@count products imported.'));
    }
  }

}
