<?php

namespace Drupal\healthdata_timesheet\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Timesheet revision.
 *
 * @ingroup healthdata_timesheet
 */
class HealthdataTimesheetRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Timesheet revision.
   *
   * @var \Drupal\healthdata_timesheet\HealthdataTimesheetInterface
   */
  protected $revision;

  /**
   * The Timesheet storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $timesheetStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->timesheetStorage = $container->get('entity_type.manager')->getStorage('timesheet');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'timesheet_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.timesheet.version_history', ['timesheet' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $timesheet_revision = NULL) {
    $this->revision = $this->timesheetStorage->loadRevision($timesheet_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->timesheetStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Timesheet: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of Timesheet %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.timesheet.canonical',
      ['timesheet' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {timesheet_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.timesheet.version_history',
        ['timesheet' => $this->revision->id()]
      );
    }
  }

}
