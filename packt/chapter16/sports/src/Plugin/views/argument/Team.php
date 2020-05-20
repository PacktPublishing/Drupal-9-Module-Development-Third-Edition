<?php

namespace Drupal\sports\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\ArgumentPluginBase;

/**
 * Argument for filtering by a team.
 *
 * @ViewsArgument("team")
 */
class Team extends ArgumentPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {
    $this->ensureMyTable();
    $field = is_numeric($this->argument) ? 'id' : 'name';
    $this->query->addWhere(0, "$this->tableAlias.$field", $this->argument);
  }

}
