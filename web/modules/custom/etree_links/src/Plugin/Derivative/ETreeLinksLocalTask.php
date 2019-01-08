<?php

namespace Drupal\etree\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\etree_group\Entity\ETreeGroupInterface;
use Drupal\etree\Entity\ETreeTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UnexpectedValueException;
use function str_replace;
use function trim;

/**
 * Provides local action definitions for all entity bundles.
 */
class ETreeLinksLocalTask extends DeriverBase implements ContainerDeriverInterface {

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


  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

//    foreach ($this->getRoutes() as $group_id => $data) {
//      $group = $data['group'];
//      /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
//      $this->addTasks($group);
//
//    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

  protected function addTasks(ETreeGroupInterface $group) {
    $group_id = $group->id();
    $bundles = $this->storageTypes->loadMultiple($group->getAllowedTypes());
    foreach ($bundles as $bundle) {
      $base_route = "entity.etree.{$group_id}.canonical";
      /** @var ETreeTypeInterface $bundle */
      $this->addActions($group_id, $bundle, $base_route);
    }
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


  protected static function createTaskName($group_id, $bundle_id, $action) {
    $action = str_replace('-', '_', $action);
    return "etree.{$group_id}.{$bundle_id}.{$action}";
  }

  protected function addActions($group_id, ETreeTypeInterface $bundle, $base_route) {
    $bundle_id = $bundle->id();

    $routes = [
      "entity.etree.{$group_id}.canonical" => $this->t('View'),
      "entity.etree.{$group_id}.edit_form" => $this->t('Edit'),
    ];

    foreach ($routes as $route_name => $title) {
      $this->derivatives[static::createTaskName($group_id, $bundle_id, $route_name)] = [
        'title' => $title,
        'route_name' => $route_name,
        'base_route' => $base_route,
      ];
    }


  }

  protected function normalizePath($path) {
    return '/' . trim($path, ' /');
  }

}
