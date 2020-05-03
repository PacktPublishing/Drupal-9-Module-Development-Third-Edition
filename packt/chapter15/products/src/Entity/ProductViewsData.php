<?php

namespace Drupal\products\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Product entities.
 */
class ProductViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['product']['importer'] = [
      'title' => $this->t('Importer'),
      'help' => $this->t('Information about the Product importer.'),
      'field' => [
        'id' => 'product_importer',
      ],
    ];

    return $data;
  }

}
