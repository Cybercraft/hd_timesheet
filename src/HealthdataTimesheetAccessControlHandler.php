<?php

namespace Drupal\healthdata_timesheet;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the healthdata timesheet entity type.
 */
class HealthdataTimesheetAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view healthdata timesheet');

      case 'update':
        return AccessResult::allowedIfHasPermissions(
          $account,
          ['edit healthdata timesheet', 'administer healthdata timesheet'],
          'OR',
        );

      case 'delete':
        return AccessResult::allowedIfHasPermissions(
          $account,
          ['delete healthdata timesheet', 'administer healthdata timesheet'],
          'OR',
        );

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions(
      $account,
      ['create healthdata timesheet', 'administer healthdata timesheet'],
      'OR',
    );
  }

}
