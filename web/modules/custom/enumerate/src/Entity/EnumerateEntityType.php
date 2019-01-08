<?php

namespace Drupal\enumerate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Enumerate type entity.
 *
 * @ConfigEntityType(
 *   id = "enumerate_type",
 *   label = @Translation("Enumerate type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\enumerate\ListBuilder\EnumerateEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\enumerate\Form\EnumerateEntityTypeForm",
 *       "edit" = "Drupal\enumerate\Form\EnumerateEntityTypeForm",
 *       "delete" = "Drupal\enumerate\Form\EnumerateEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\enumerate\RouteProvider\EnumerateEntityType",
 *     },
 *   },
 *   config_prefix = "enumerate_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "enumerate",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/enumerate/enumerate_type/{enumerate_type}",
 *     "add-form" = "/admin/structure/enumerate/enumerate_type/add",
 *     "edit-form" = "/admin/structure/enumerate/enumerate_type/{enumerate_type}/edit",
 *     "delete-form" = "/admin/structure/enumerate/enumerate_type/{enumerate_type}/delete",
 *     "collection" = "/admin/structure/enumerate/enumerate_type"
 *   }
 * )
 */
class EnumerateEntityType extends ConfigEntityBundleBase implements EnumerateEntityTypeInterface {

  /**
   * The Enumerate type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Enumerate type label.
   *
   * @var string
   */
  protected $label;

}
