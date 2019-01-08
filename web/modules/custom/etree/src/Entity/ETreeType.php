<?php

namespace Drupal\etree\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the ETree type entity.
 *
 * @ConfigEntityType(
 *   id = "etree_type",
 *   label = @Translation("ETree type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\etree\ETreeTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\etree\Form\ETreeTypeForm",
 *       "edit" = "Drupal\etree\Form\ETreeTypeForm",
 *       "delete" = "Drupal\etree\Form\ETreeTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\etree\Routing\ETreeTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "etree_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "etree",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/etree_type/{etree_type}",
 *     "add-form" = "/admin/structure/etree_type/add",
 *     "edit-form" = "/admin/structure/etree_type/{etree_type}/edit",
 *     "delete-form" = "/admin/structure/etree_type/{etree_type}/delete",
 *     "collection" = "/admin/structure/etree_type"
 *   }
 * )
 */
class ETreeType extends ConfigEntityBundleBase implements ETreeTypeInterface {

  /**
   * The ETree type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The ETree type label.
   *
   * @var string
   */
  protected $label;

}
