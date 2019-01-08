<?php

namespace Drupal\enumerate\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Enumerate item type entity.
 *
 * @ConfigEntityType(
 *   id = "enumerate_item_type",
 *   label = @Translation("Enumerate item type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\enumerate\ListBuilder\EnumerateItemEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\enumerate\Form\EnumerateItemEntityTypeForm",
 *       "edit" = "Drupal\enumerate\Form\EnumerateItemEntityTypeForm",
 *       "delete" = "Drupal\enumerate\Form\EnumerateItemEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\enumerate\RouteProvider\EnumerateItemEntityType",
 *     },
 *   },
 *   config_prefix = "enumerate_item_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "enumerate_item",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/enumerate/enumerate-item-type/{enumerate_item_type}",
 *     "add-form" = "/admin/structure/enumerate/enumerate-item-type/add",
 *     "edit-form" = "/admin/structure/enumerate/enumerate-item-type/{enumerate_item_type}/edit",
 *     "delete-form" = "/admin/structure/enumerate/enumerate-item-type/{enumerate_item_type}/delete",
 *     "collection" = "/admin/structure/enumerate/enumerate-item-type"
 *   }
 * )
 */
class EnumerateItemEntityType extends ConfigEntityBundleBase implements EnumerateItemEntityTypeInterface {

  /**
   * The Enumerate item type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Enumerate item type label.
   *
   * @var string
   */
  protected $label;

}
