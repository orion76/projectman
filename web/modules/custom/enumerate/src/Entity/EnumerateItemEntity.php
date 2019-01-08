<?php

namespace Drupal\enumerate\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\enumerate\fields\HumanReadableID;

/**
 * Defines the Enumerate item entity.
 *
 * @ingroup enumerate
 *
 * @ContentEntityType(
 *   id = "enumerate_item",
 *   label = @Translation("Enumerate item"),
 *   bundle_label = @Translation("Enumerate item type"),
 *   handlers = {
 *     "storage" = "Drupal\enumerate\EnumerateItemEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\enumerate\ListBuilder\EnumerateItemEntityListBuilder",
 *     "views_data" = "Drupal\enumerate\Entity\EnumerateItemEntityViewsData",
 *     "translation" = "Drupal\enumerate\EnumerateItemEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\enumerate\Form\EnumerateItemEntityForm",
 *       "add" = "Drupal\enumerate\Form\EnumerateItemEntityForm",
 *       "edit" = "Drupal\enumerate\Form\EnumerateItemEntityForm",
 *       "delete" = "Drupal\enumerate\Form\EnumerateItemEntityDeleteForm",
 *     },
 *     "access" = "Drupal\enumerate\EnumerateItemEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\enumerate\RouteProvider\EnumerateItemEntity",
 *     },
 *   },
 *   base_table = "enumerate_item",
 *   data_table = "enumerate_item_field_data",
 *   revision_table = "enumerate_item_revision",
 *   revision_data_table = "enumerate_item_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer enumerate item entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/enumerate_item/{enumerate_item}",
 *     "add-page" = "/admin/structure/enumerate_item/add",
 *     "add-form" = "/admin/structure/enumerate_item/add/{enumerate_item_type}",
 *     "edit-form" = "/admin/structure/enumerate_item/{enumerate_item}/edit",
 *     "delete-form" = "/admin/structure/enumerate_item/{enumerate_item}/delete",
 *     "version-history" = "/admin/structure/enumerate_item/{enumerate_item}/revisions",
 *     "revision" = "/admin/structure/enumerate_item/{enumerate_item}/revisions/{enumerate_item_revision}/view",
 *     "revision_revert" =
 *   "/admin/structure/enumerate_item/{enumerate_item}/revisions/{enumerate_item_revision}/revert",
 *     "revision_delete" =
 *   "/admin/structure/enumerate_item/{enumerate_item}/revisions/{enumerate_item_revision}/delete",
 *     "translation_revert" =
 *   "/admin/structure/enumerate_item/{enumerate_item}/revisions/{enumerate_item_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/enumerate_item",
 *   },
 *   bundle_entity_type = "enumerate_item_type",
 *   field_ui_base_route = "entity.enumerate_item_type.edit_form"
 * )
 */
class EnumerateItemEntity extends RevisionableContentEntityBase implements EnumerateItemEntityInterface {

  use EntityChangedTrait;


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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Enumerate item entity.'))
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
      ->setDescription(t('The name of the Enumerate item entity.'))
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


    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
