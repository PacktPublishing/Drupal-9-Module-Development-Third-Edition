<?php

namespace Drupal\products\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\products\Plugin\ImporterManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for creating/editing Importer entities.
 */
class ImporterForm extends EntityForm {

  /**
   * The importer plugin manager.
   *
   * @var \Drupal\products\Plugin\ImporterManager
   */
  protected $importerManager;

  /**
   * ImporterForm constructor.
   *
   * @param \Drupal\products\Plugin\ImporterManager $importerManager
   *   The importer plugin manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(ImporterManager $importerManager, MessengerInterface $messenger, EntityTypeManagerInterface $entityTypeManager) {
    $this->importerManager = $importerManager;
    $this->messenger = $messenger;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('products.importer_manager'),
      $container->get('messenger'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\products\Entity\Importer $importer */
    $importer = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#maxlength' => 255,
      '#default_value' => $importer->label(),
      '#description' => $this->t('Name of the Importer.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $importer->id(),
      '#machine_name' => [
        'exists' => '\Drupal\products\Entity\Importer::load',
      ],
      '#disabled' => !$importer->isNew(),
    ];

    $form['bundle'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'product_type',
      '#title' => $this->t('Product type'),
      '#default_value' => $importer->getBundle() ? $this->entityTypeManager->getStorage('product_type')->load($importer->getBundle()) : NULL,
      '#description' => $this->t('The type of products that need to be created.'),
      '#required' => TRUE,
    ];

    $form['url'] = [
      '#type' => 'url',
      '#default_value' => $importer->getUrl() instanceof Url ? $importer->getUrl()->toString() : '',
      '#title' => $this->t('Url'),
      '#description' => $this->t('The URL to the import resource'),
      '#required' => TRUE,
    ];

    $definitions = $this->importerManager->getDefinitions();
    $options = [];
    foreach ($definitions as $id => $definition) {
      $options[$id] = $definition['label'];
    }

    $form['plugin'] = [
      '#type' => 'select',
      '#title' => $this->t('Plugin'),
      '#default_value' => $importer->getPluginId(),
      '#options' => $options,
      '#description' => $this->t('The plugin to be used with this importer.'),
      '#required' => TRUE,
    ];

    $form['update_existing'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Update existing'),
      '#description' => $this->t('Whether to update existing products if already imported.'),
      '#default_value' => $importer->updateExisting(),
    ];

    $form['source'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source'),
      '#description' => $this->t('The source of the products.'),
      '#default_value' => $importer->getSource(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\products\Entity\Importer $importer */
    $importer = $this->entity;
    $status = $importer->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger->addMessage($this->t('Created the %label Importer.', [
          '%label' => $importer->label(),
        ]));
        break;

      default:
        $this->messenger->addMessage($this->t('Saved the %label Importer.', [
          '%label' => $importer->label(),
        ]));
    }

    $form_state->setRedirectUrl($importer->toUrl('collection'));
  }

}
