<?php

namespace Drupal\healthdata_timesheet;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\healthdata_timesheet\HealthdataTimesheetInterface;

/**
 * Defines the storage handler class for Timesheet entities.
 *
 * This extends the base storage class, adding required special handling for
 * Timesheet entities.
 *
 * @ingroup healthdata_timesheet
 */
class HealthdataTimesheetStorage extends SqlContentEntityStorage {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(HealthdataTimesheetInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {timesheet_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {timesheet_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(HealthdataTimesheetInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {timesheet_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('timesheet_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
