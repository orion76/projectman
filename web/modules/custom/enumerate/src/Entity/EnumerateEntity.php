<?php

namespace Drupal\enumerate\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\enumerate\fields\HumanReadableID;
use Drupal\user\UserInterface;

/**
 * Defines the Enumerate entity.
 *
 * @ingroup enumerate
 *
 * @ContentEntityType(
 *   id = "enumerate",
 *   label = @Translation("Enumerate"),
 *   bundle_label = @Translation("Enumerate type"),
 *   handlers = {
 *     "storage" = "Drupal\enumerate\EnumerateEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\enumerate\ListBuilder\EnumerateEntityListBuilder",
 *     "views_data" = "Drupal\enumerate\Entity\EnumerateEntityViewsData",
 *     "translation" = "Drupal\enumerate\EnumerateEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\enumerate\Form\EnumerateEntityForm",
 *       "add" = "Drupal\enumerate\Form\EnumerateEntityForm",
 *       "edit" = "Drupal\enumerate\Form\EnumerateEntityForm",
 *       "delete" = "Drupal\enumerate\Form\EnumerateEntityDeleteForm",
 *     },
 *     "access" = "Drupal\enumerate\EnumerateEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\enumerate\RouteProvider\EnumerateEntity",
 *     },
 *   },
 *   base_table = "enumerate",
 *   data_table = "enumerate_field_data",
 *   revision_table = "enumerate_revision",
 *   revision_data_table = "enumerate_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer enumerate entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/enumerate/{enumerate}",
 *     "add-page" = "/admin/structure/enumerate/add",
 *     "add-form" = "/admin/structure/enumerate/add/{enumerate_type}",
 *     "edit-form" = "/admin/structure/enumerate/{enumerate}/edit",
 *     "delete-form" = "/admin/structure/enumerate/{enumerate}/delete",
 *     "version-history" = "/admin/structure/enumerate/{enumerate}/revisions",
 *     "revision" = "/admin/structure/enumerate/{enumerate}/revisions/{enumerate_revision}/view",
 *     "revision_revert" = "/admin/structure/enumerate/{enumerate}/revisions/{enumerate_revision}/revert",
 *     "revision_delete" = "/admin/structure/enumerate/{enumerate}/revisions/{enumerate_revision}/delete",
 *     "translation_revert" = "/admin/structure/enumerate/{enumerate}/revisions/{enumerate_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/enumerate",
 *   },
 *   bundle_entity_type = "enumerate_type",
 *   field_ui_base_route = "entity.enumerate_type.edit_form"
 * )
 */
class EnumerateEntity extends RevisionableContentEntityBase implements EnumerateEntityInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
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

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the enumerate owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Enumerate entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    HumanReadableID::addField($fields);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Enumerate entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Enumerate is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));


    $fields['category'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Category'))
      ->setDescription(t('The Enumerate category.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setCardinality(1)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['enumerate' => 'enumerate',]])
      ->setDisplayOptions('view', ['label' => 'above'])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => ['match_operator' => 'CONTAINS', 'size' => 60, 'placeholder' => ''],
      ]);


    $fields['items'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Items'))
      ->setDescription(t('The Enumerate items.'))
      ->setRevisionable(TRUE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'enumerate_item')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('view', FALSE)
      ->setDisplayOptions('form', [
        'type' => 'inline_entity_form_complex',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
