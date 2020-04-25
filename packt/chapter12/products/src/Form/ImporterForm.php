<?php

namespace Drupal\products\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Form\SubformState;
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
      '#empty_option' => $this->t('Please select a plugin'),
      '#ajax' => [
        'callback' => [$this, 'pluginConfigAjaxCallback'],
        'wrapper' => 'plugin-configuration-wrapper',
      ],
    ];

    $form['plugin_configuration'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'plugin-configuration-wrapper',
      ],
      '#tree' => TRUE,
      '#open' => TRUE,
    ];

    $plugin_id = NULL;
    if ($importer->getPluginId()) {
      $plugin_id = $importer->getPluginId();
    }
    if ($form_state->getValue('plugin') && $plugin_id !== $form_state->getValue('plugin')) {
      $plugin_id = $form_state->getValue('plugin');
    }

    if ($plugin_id) {
      $existing_config = [
        'config' => $importer,
      ] + $importer->getPluginConfiguration();
      $plugin = $this->importerManager->createInstance($plugin_id, $existing_config);

      $form['plugin_configuration']['#type'] = 'details';
      $form['plugin_configuration']['#title'] = $this->t('Plugin configuration for <em>@plugin</em>', ['@plugin' => $plugin->getPluginDefinition()['label']]);
      $form['plugin_configuration'][$plugin_id] = [
        '#process' => [[get_class($this), 'processPluginConfiguration']],
        '#plugin' => $plugin,
      ];
    }

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

  /**
   * Ajax callback for the plugin configuration form elements.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form element.
   */
  public function pluginConfigAjaxCallback(array $form, FormStateInterface $form_state) {
    return $form['plugin_configuration'];
  }

  /**
   * Process callback for the plugin configuration form.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The plugin configuration form.
   */
  public static function processPluginConfiguration(array &$element, FormStateInterface $form_state) {
    /** @var \Drupal\products\Plugin\ImporterPluginInterface $plugin */
    $plugin = $element['#plugin'];
    $subform_state = SubformState::createForSubform($element, $form_state->getCompleteForm(), $form_state);
    return $plugin->buildConfigurationForm($element, $subform_state);
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    if ($form_state->getValue('plugin_configuration') == "") {
      $form_state->setValue('plugin_configuration', []);
    }

    /** @var \Drupal\products\Entity\ImporterInterface $entity */
    $entity = parent::buildEntity($form, $form_state);

    $plugin_id = $form_state->getValue('plugin');
    if ($plugin_id) {
      $configuration = ['config' => $entity];
      $plugin_configuration = $form_state->getValue(['plugin_configuration', $plugin_id]);
      if ($plugin_configuration) {
        $configuration += $plugin_configuration;
      }
      /** @var \Drupal\Core\Plugin\PluginFormInterface $plugin */
      $plugin = $this->importerManager->createInstance($plugin_id, $configuration);

      if (isset($form['plugin_configuration'][$plugin_id])) {
        $subform_state = SubformState::createForSubform($form['plugin_configuration'][$plugin_id], $form_state->getCompleteForm(), $form_state);
        $plugin->submitConfigurationForm($form['plugin_configuration'][$plugin_id], $subform_state);
      }

      $configuration = $plugin->getConfiguration();
      unset($configuration['config']);
      $entity->setPluginConfiguration($configuration);
    }

    return $entity;
  }

}
