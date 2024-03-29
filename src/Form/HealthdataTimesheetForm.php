<?php

namespace Drupal\healthdata_timesheet\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the healthdata timesheet entity edit forms.
 */
class HealthdataTimesheetForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New healthdata timesheet %label has been created.', $message_arguments));
        $this->logger('timesheet')->notice('Created new healthdata timesheet %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The healthdata timesheet %label has been updated.', $message_arguments));
        $this->logger('timesheet')->notice('Updated healthdata timesheet %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.timesheet.canonical', ['timesheet' => $entity->id()]);

    return $result;
  }

}
