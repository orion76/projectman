<?php

namespace Drupal\etree_group\Entity;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;
use function array_column;
use function array_combine;
use function array_unique;

/**
 * Defines the Etree group entity.
 *
 * @ConfigEntityType(
 *   id = "etree_group",
 *   label = @Translation("Etree group"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\etree_group\ETreeGroupListBuilder",
 *     "form" = {
 *       "add" = "Drupal\etree_group\Form\ETreeGroupForm",
 *       "edit" = "Drupal\etree_group\Form\ETreeGroupForm",
 *       "delete" = "Drupal\etree_group\Form\ETreeGroupDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\etree_group\Routing\ETreeGroupHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "etree_group",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/etree/etree_group/{etree_group}",
 *     "add-form" = "/admin/structure/etree/etree_group/add",
 *     "edit-form" = "/admin/structure/etree/etree_group/{etree_group}/edit",
 *     "delete-form" = "/admin/structure/etree/etree_group/{etree_group}/delete",
 *     "collection" = "/admin/structure/etree/etree_group"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "collection_views",
 *     "etree_types",
 *     "child_types",
 *   }
 * )
 */
class ETreeGroup extends ConfigEntityBase implements ETreeGroupInterface {

  use MessengerTrait;

  /**
   * The Etree group ID.
   *
   * @var string
   */
  protected $id;

  public function getCollectionViews() {
    $views = $this->get('collection_views');
    $views_keyed = array_combine(array_column($views, 'id'), $views);
    return $views_keyed ? $views_keyed : [];
  }

  public function getPathParams() {
    $parts = [];
    list($parts['etree_group_root'], $parts['etree_group_alias']) = explode('/', $this->getPath());
    return $parts;
  }


  public function getAllowedTypes() {
    return array_merge($this->getTreeTypes(), $this->getChildTypes());
  }

  public function getTreeTypes() {
    $types = $this->get('tree_types');
    return $types ? array_values(array_filter($this->get('tree_types'))) : [];
  }

  public function getChildTypes() {
    return array_values(array_filter($this->get('child_types')));
  }
}
