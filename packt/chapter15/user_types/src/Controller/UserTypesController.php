<?php

namespace Drupal\user_types\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * User types controller.
 */
class UserTypesController extends ControllerBase {

  /**
   * Callback for board members.
   */
  public function boardMember() {
    return [
      '#markup' => $this->t('Board member'),
    ];
  }

  /**
   * Callback for managers.
   */
  public function manager() {
    return [
      '#markup' => $this->t('Manager'),
    ];
  }

  /**
   * Callback for employees.
   */
  public function employee() {
    return [
      '#markup' => $this->t('Employee'),
    ];
  }

  /**
   * Callback for leadership.
   */
  public function leadership() {
    return [
      '#markup' => $this->t('Leadership'),
    ];
  }

}
