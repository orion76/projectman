<?php

namespace Drupal\todo_entity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Todo entity type entity.
 *
 * @ConfigEntityType(
 *   id = "todo_entity_type",
 *   label = @Translation("Todo entity type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\todo_entity\TodoEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\todo_entity\Form\TodoEntityTypeForm",
 *       "edit" = "Drupal\todo_entity\Form\TodoEntityTypeForm",
 *       "delete" = "Drupal\todo_entity\Form\TodoEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\todo_entity\TodoEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "todo_entity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "todo_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/todo_entity_type/{todo_entity_type}",
 *     "add-form" = "/admin/structure/todo_entity_type/add",
 *     "edit-form" = "/admin/structure/todo_entity_type/{todo_entity_type}/edit",
 *     "delete-form" = "/admin/structure/todo_entity_type/{todo_entity_type}/delete",
 *     "collection" = "/admin/structure/todo_entity_type"
 *   }
 * )
 */
class TodoEntityType extends ConfigEntityBundleBase implements TodoEntityTypeInterface {

  /**
   * The Todo entity type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Todo entity type label.
   *
   * @var string
   */
  protected $label;

}
