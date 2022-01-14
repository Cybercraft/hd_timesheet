<?php

namespace Drupal\healthdata_timesheet\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\healthdata_timesheet\HealthdataTimesheetInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the healthdata timesheet entity class.
 *
 * @ContentEntityType(
 *   id = "timesheet",
 *   label = @Translation("Healthdata timesheet"),
 *   label_collection = @Translation("Healthdata timesheets"),
 *   label_singular = @Translation("healthdata timesheet"),
 *   label_plural = @Translation("healthdata timesheets"),
 *   label_count = @PluralTranslation(
 *     singular = "@count healthdata timesheets",
 *     plural = "@count healthdata timesheets",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\healthdata_timesheet\HealthdataTimesheetStorage",
 *     "list_builder" = "Drupal\healthdata_timesheet\HealthdataTimesheetListBuilder",
 *     "views_data" = "Drupal\healthdata_timesheet\Entity\HealthdataTimesheetViewsData",
 *     "access" = "Drupal\healthdata_timesheet\HealthdataTimesheetAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\healthdata_timesheet\Form\HealthdataTimesheetForm",
 *       "edit" = "Drupal\healthdata_timesheet\Form\HealthdataTimesheetForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\healthdata_timesheet\HealthdataTimesheetHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "timesheet",
 *   data_table = "timesheet_field_data",
 *   revision_table = "timesheet_revision",
 *   revision_data_table = "timesheet_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer healthdata timesheet",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "langcode" = "langcode",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "collection" = "/admin/content/timesheet",
 *     "add-form" = "/admin/content/timesheet/add",
 *     "canonical" = "/timesheet/{timesheet}",
 *     "edit-form" = "/admin/content/timesheet/{timesheet}/edit",
 *     "delete-form" = "/admin/content/timesheet/{timesheet}/delete",
 *     "version-history" = "/timesheet/{timesheet}/revisions",
 *     "revision" = "/timesheet/{timesheet}/revisions/{timesheet_revision}/view",
 *     "revision_revert" = "/timesheet/{timesheet}/revisions/{timesheet_revision}/revert",
 *     "revision_delete" = "/timesheet/{timesheet}/revisions/{timesheet_revision}/delete",
 *   },
 *   field_ui_base_route = "entity.timesheet.settings",
 * )
 */
class HealthdataTimesheet extends RevisionableContentEntityBase implements HealthdataTimesheetInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['tsid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('TSID'))
      ->setDescription(t('The timesheet ID'))
      ->setRevisionable(TRUE)
      ->setRequired(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The timesheet UUID.'))
      ->setReadOnly(TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDescription(t('The timesheet title'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string_textfield',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['year'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Year'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDescription(t('The timesheet year'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['week'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Week'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDescription(t('The timesheet week'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['total_time'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Total time'))
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setDescription(t('The timesheet week'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('User'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 25,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 25,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the healthdata timesheet was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 30,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the healthdata timesheet was last edited.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 35,
      ])
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
