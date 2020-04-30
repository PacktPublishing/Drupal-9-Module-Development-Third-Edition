<?php

namespace Drupal\license_plate\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'default_license_plate_widget' widget.
 *
 * @FieldWidget(
 *   id = "default_license_plate_widget",
 *   label = @Translation("Default license plate widget"),
 *   field_types = {
 *     "license_plate"
 *   }
 * )
 */
class DefaultLicensePlateWidget extends WidgetBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'number_size' => 60,
      'code_size' => 5,
      'fieldset_state' => 'open',
      'placeholder' => [
        'number' => '',
        'code' => '',
      ],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['number_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Size of plate number textfield'),
      '#default_value' => $this->getSetting('number_size'),
      '#required' => TRUE,
      '#min' => 1,
      '#max' => $this->getFieldSetting('number_max_length'),
    ];

    $elements['code_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Size of plate code textfield'),
      '#default_value' => $this->getSetting('code_size'),
      '#required' => TRUE,
      '#min' => 1,
      '#max' => $this->getFieldSetting('code_max_length'),
    ];

    $elements['fieldset_state'] = [
      '#type' => 'select',
      '#title' => $this->t('Fieldset default state'),
      '#options' => [
        'open' => $this->t('Open'),
        'closed' => $this->t('Closed'),
      ],
      '#default_value' => $this->getSetting('fieldset_state'),
      '#description' => $this->t('The default state of the fieldset which contains the two plate fields: open or closed'),
    ];

    $elements['placeholder'] = [
      '#type' => 'details',
      '#title' => $this->t('Placeholder'),
      '#description' => $this->t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];

    $placeholder_settings = $this->getSetting('placeholder');
    $elements['placeholder']['number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number field'),
      '#default_value' => $placeholder_settings['number'],
    ];
    $elements['placeholder']['code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Code field'),
      '#default_value' => $placeholder_settings['code'],
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('License plate size: @number (for number) and @code (for code)', ['@number' => $this->getSetting('number_size'), '@code' => $this->getSetting('code_size')]);
    $placeholder_settings = $this->getSetting('placeholder');
    if (!empty($placeholder_settings['number']) && !empty($placeholder_settings['code'])) {
      $placeholder = $placeholder_settings['number'] . ' ' . $placeholder_settings['code'];
      $summary[] = $this->t('Placeholder: @placeholder', ['@placeholder' => $placeholder]);
    }
    $summary[] = $this->t('Fieldset state: @state', ['@state' => $this->getSetting('fieldset_state')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['details'] = [
      '#type' => 'details',
      '#title' => $element['#title'],
      '#open' => $this->getSetting('fieldset_state') == 'open' ? TRUE : FALSE,
      '#description' => $element['#description'],
    ] + $element;

    $placeholder_settings = $this->getSetting('placeholder');

    $this->addCodeField($element, $items, $delta, $placeholder_settings);

    $element['details']['number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Plate number'),
      '#default_value' => isset($items[$delta]->number) ? $items[$delta]->number : NULL,
      '#size' => $this->getSetting('number_size'),
      '#placeholder' => $placeholder_settings['number'],
      '#maxlength' => $this->getFieldSetting('number_max_length'),
      '#description' => '',
      '#required' => $element['#required'],
    ];

    return $element;
  }

  /**
   * Adds the license plate code field to the form element.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The items list.
   * @param int $delta
   *   The field delta.
   * @param array $placeholder_settings
   *   The placeholder settings.
   */
  protected function addCodeField(array &$element, FieldItemListInterface $items, $delta, array $placeholder_settings) {
    $element['details']['code'] = [
      '#title' => $this->t('Plate code'),
      '#default_value' => isset($items[$delta]->code) ? $items[$delta]->code : NULL,
      '#description' => '',
      '#required' => $element['#required'],
    ];

    $codes = $this->getFieldSetting('codes');
    if (!$codes) {
      $element['details']['code'] += [
        '#type' => 'textfield',
        '#placeholder' => $placeholder_settings['code'],
        '#maxlength' => $this->getFieldSetting('code_max_length'),
        '#size' => $this->getSetting('code_size'),
      ];
      return;
    }

    $codes = explode("\r\n", $codes);
    $element['details']['code'] += [
      '#type' => 'select',
      '#options' => array_combine($codes, $codes),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      $value['number'] = $value['details']['number'];
      $value['code'] = $value['details']['code'];
      unset($value['details']);
    }

    return $values;
  }

}
