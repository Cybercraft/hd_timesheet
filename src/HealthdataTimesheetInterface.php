<?php

namespace Drupal\healthdata_timesheet;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a healthdata timesheet entity type.
 */
interface HealthdataTimesheetInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
