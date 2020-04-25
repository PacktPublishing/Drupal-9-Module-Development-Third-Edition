<?php

namespace Drupal\license_plate\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'default_license_plate_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "default_license_plate_formatter",
 *   label = @Translation("Default license plate formatter"),
 *   field_types = {
 *     "license_plate"
 *   }
 * )
 */
class DefaultLicensePlateFormatter extends FormatterBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'concatenated' => 1,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      'concatenated' => [
        '#type' => 'checkbox',
        '#title' => $this->t('Concatenated'),
        '#description' => $this->t('Whether to concatenate the code and number into a single string separated by a space. Otherwise the two are broken up into separate span tags.'),
        '#default_value' => $this->getSetting('concatenated'),
      ],
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Concatenated: @value', ['@value' => (bool) $this->getSetting('concatenated') ? $this->t('Yes') : $this->t('No')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->viewValue($item);
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return array
   *   The field value render array.
   */
  protected function viewValue(FieldItemInterface $item) {
    $code = $item->get('code')->getValue();
    $number = $item->get('number')->getValue();
    return [
      '#theme' => 'license_plate',
      '#code' => $code,
      '#number' => $number,
      '#concatenated' => $this->getSetting('concatenated'),
    ];
  }

}
