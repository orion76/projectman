<?php

namespace Drupal\etree_group\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\etree_group\Entity\ETreeGroupInterface;
use Drupal\etree\Entity\ETreeTypeInterface;
use Drupal\etree\ETreeCommon;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UnexpectedValueException;
use function trim;

/**
 * Provides local action definitions for all entity bundles.
 */
class ETreeGroupLocalAction extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity manager
   *
   * @var ConfigEntityStorage
   */
  protected $storageGroup;

  /**
   * The entity manager
   *
   * @var ConfigEntityStorage
   */
  protected $storageTypes;

  /**
   * Constructs a FieldUiLocalAction object.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider to load routes by name.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(RouteProviderInterface $route_provider, ConfigEntityStorage $storageGroup, ConfigEntityStorage $storageTypes) {
    $this->routeProvider = $route_provider;
    $this->storageGroup = $storageGroup;
    $this->storageTypes = $storageTypes;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('router.route_provider'),
      $container->get('entity_type.manager')->getStorage('etree_group'),
      $container->get('entity_type.manager')->getStorage('etree_type')
    );
  }

  protected function normalizePath($path) {
    return '/' . trim($path, ' /');
  }

  protected function getRoutes() {
    $routes = [];
    foreach ($this->storageGroup->loadMultiple() as $id => $group) {
      $path = $group->get('path');
      /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
      $path = $this->normalizePath($path);
      try {
        $routes[$id] = [
          'group' => $group,
          'route_name' => Url::fromUserInput($path)->getRouteName(),
        ];
      } catch (UnexpectedValueException $e) {
        \Drupal::messenger()->addWarning('Route for group is empty:' . $group->label());
      }

    }
    return $routes;
  }

  protected function addActions($prefix, ETreeGroupInterface $group, ETreeTypeInterface $bundle, $appears_on) {
    $bundle_id = $bundle->id();
    $group_id = $group->id();

    $action = "etree.{$group_id}.{$prefix}.{$bundle_id}.add_form";

    $this->derivatives[$action] = [
      'route_name' => "entity.etree.{$group_id}.add_form",
      'route_parameters' => ['etree_type' => $bundle_id, 'group_id' => $group_id],
      'title' => $this->createTitle($prefix, $bundle),
      'appears_on' => $appears_on,
    ];

  }

  protected function createTitle($type, ETreeTypeInterface $bundle) {
    $label = $bundle->label();
    switch ($type) {
      case 'tree':
        $label = $this->t('Add @label', ['@label' => $label]);
        break;
      case 'child':
        break;
    }
    return $label;
  }

  protected function addTreeActions(ETreeGroupInterface $group) {
    $group_id = $group->id();
    $tree_bundles = $this->storageTypes->loadMultiple($group->getTreeTypes());
    $appears_on = [
      ETreeCommon::getGroupViewsRouteName($group_id),
    ];
    foreach ($tree_bundles as $bundle) {
      /** @var ETreeTypeInterface $bundle */
      $this->addActions('tree', $group, $bundle, $appears_on);
    }
  }


  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    foreach ($this->getRoutes() as $group_id => $data) {
      $group = $data['group'];
      /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
      $this->addTreeActions($group);
    }

    foreach ($this->derivatives as &$entry) {
      $entry += $base_plugin_definition;
    }

    return $this->derivatives;
  }

}
