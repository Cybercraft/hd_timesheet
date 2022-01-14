<?php

namespace Drupal\healthdata_timesheet\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\healthdata_timesheet\HealthdataTimesheetInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HealthdataTimesheetController.
 *
 *  Returns responses for Timesheet routes.
 */
class HealthdataTimesheetController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Timesheet revision.
   *
   * @param int $timesheet_revision
   *   The Homepage revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($timesheet_revision) {
    $timesheet = $this->entityTypeManager()->getStorage('timesheet')
      ->loadRevision($timesheet_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('timesheet');

    return $view_builder->view($timesheet);
  }

  /**
   * Page title callback for a Timesheet revision.
   *
   * @param int $timesheet_revision
   *   The Timesheet revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($timesheet_revision) {
    $homepage = $this->entityTypeManager()->getStorage('timesheet')
      ->loadRevision($timesheet_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $homepage->label(),
      '%date' => $this->dateFormatter->format($homepage->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Timesheet.
   *
   * @param \Drupal\healthdata_timesheet\HealthdataTimesheetInterface $timesheet
   *   A Timesheet object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(HealthdataTimesheetInterface $timesheet) {
    $account = $this->currentUser();
    $timesheet_storage = $this->entityTypeManager()->getStorage('timesheet');

    $langcode = $timesheet->language()->getId();
    $langname = $timesheet->language()->getName();
    $languages = $timesheet->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $timesheet->label()]) : $this->t('Revisions for %title', ['%title' => $timesheet->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all timesheet revisions") || $account->hasPermission('administer timesheet entities')));
    $delete_permission = (($account->hasPermission("delete all timesheet revisions") || $account->hasPermission('administer timesheet entities')));

    $rows = [];

    $vids = $timesheet_storage->revisionIds($timesheet);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\healthdata_timesheet\HealthdataTimesheetInterface $revision */
      $revision = $timesheet_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $timesheet->getRevisionId()) {
          $link = (new Link($date, new Url('entity.timesheet.revision', [
            'timesheet' => $timesheet->id(),
            'timesheet_revision' => $vid,
          ])))->toString();
        }
        else {
          $link = (new Link($date, new Url('entity.timesheet.canonical', [
            'timesheet' => $timesheet->id()
          ])))->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
                Url::fromRoute('entity.timesheet.translation_revert', [
                  'timesheet' => $timesheet->id(),
                  'timesheet_revision' => $vid,
                  'langcode' => $langcode,
                ]) :
                Url::fromRoute('entity.timesheet.revision_revert', [
                  'timesheet' => $timesheet->id(),
                  'timesheet_revision' => $vid,
                ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.timesheet.revision_delete', [
                'timesheet' => $timesheet->id(),
                'timesheet_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['timesheet_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
