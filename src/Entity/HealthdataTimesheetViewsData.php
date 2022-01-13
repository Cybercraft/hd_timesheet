<?php

namespace Drupal\wvn_homepage\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Timesheet entities.
 */
class HealthdataTimesheetViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
