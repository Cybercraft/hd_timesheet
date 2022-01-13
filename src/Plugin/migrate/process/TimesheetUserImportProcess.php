<?php

namespace Drupal\healthdata_timesheet\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\user\Entity\User;

/**
 * @MigrateProcessPlugin(
 *   id = "timesheet_user_import_process",
 * )
 */
class TimesheetUserImportProcess extends ProcessPluginBase
{

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $username = $row->get('user');
    $user = user_load_by_name($username);

    if ($user instanceof User) {
      return $user->id();
    }

    return (User::load(1))->id();
  }
}
