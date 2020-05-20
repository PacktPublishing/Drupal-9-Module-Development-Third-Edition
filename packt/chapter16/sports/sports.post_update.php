<?php

/**
 * @file
 * Sports post updates file.
 */

/**
 * Update all the players to mark them as retired.
 */
function sports_post_update_retire_players(&$sandbox) {
  $database = \Drupal::database();

  if (empty($sandbox)) {
    $results = $database->query("SELECT [id] FROM {players}")->fetchAllAssoc('id');
    $sandbox['progress'] = 0;
    $sandbox['ids'] = array_keys($results);
    $sandbox['max'] = count($results);
  }

  $id = $sandbox['ids'] ? array_shift($sandbox['ids']) : NULL;

  $player = $database->query("SELECT * FROM {players} WHERE [id] = :id", [':id' => $id])->fetch();
  $data = $player->data ? unserialize($player->data) : [];
  $data['retired'] = TRUE;
  $database->update('players')
    ->fields(['data' => serialize($data)])
    ->condition('id', $id)
    ->execute();
  $sandbox['progress']++;
  $sandbox['#finished'] = $sandbox['progress'] / $sandbox['max'];
}
