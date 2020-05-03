<?php

namespace Drupal\products\Plugin\views\field;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\products\Plugin\ImporterManager;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Field plugin that renders data about the Importer that imported the Product.
 *
 * @ViewsField("product_importer")
 */
class ProductImporter extends FieldPluginBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The importer plugin manager.
   *
   * @var \Drupal\products\Plugin\ImporterManager
   */
  protected $importerManager;

  /**
   * Constructs a ProductImporter object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\products\Plugin\ImporterManager $importerManager
   *   The importer plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, ImporterManager $importerManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->importerManager = $importerManager;
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
      $container->get('products.importer_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['importer'] = ['default' => 'entity'];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    $form['importer'] = [
      '#type' => 'select',
      '#title' => $this->t('Importer'),
      '#description' => $this->t('Which importer label to use?'),
      '#options' => [
        'entity' => $this->t('Entity'),
        'plugin' => $this->t('Plugin'),
      ],
      '#default_value' => $this->options['importer'],
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\products\Entity\ProductInterface $product */
    $product = $values->_entity;
    $source = $product->getSource();
    $importers = $this->entityTypeManager->getStorage('importer')->loadByProperties(['source' => $source]);
    if (!$importers) {
      return NULL;
    }

    // We'll assume one importer per source.
    /** @var \Drupal\products\Entity\ImporterInterface $importer */
    $importer = reset($importers);
    // If we want to show the entity label.
    if ($this->options['importer'] == 'entity') {
      return $this->sanitizeValue($importer->label());
    }

    // Otherwise we show the plugin label.
    $definition = $this->importerManager->getDefinition($importer->getPluginId());
    return $this->sanitizeValue($definition['label']);

  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

}
