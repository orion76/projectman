<?php

namespace Drupal\etree_links\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\etree\ETreeCommon;
use Drupal\etree\Routing\ETreeHtmlRouteProvider;
use Drupal\etree_links\Plugin\ETreeLinksPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function str_replace;

/**
 * Provides local action definitions for all entity bundles.
 */
class ETreeLinksLocalActionDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity manager
   *
   * @var ConfigEntityStorage
   */
  protected $pluginManager;

  /**
   * @var \Drupal\Core\Entity\EntityStorageBase
   */
  protected $groupStorage;


  /**
   * Constructs a FieldUiLocalAction object.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider to load routes by name.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(RouteProviderInterface $route_provider,
                              ETreeLinksPluginManager $pluginManager,
                              EntityStorageInterface $groupStorage) {
    $this->routeProvider = $route_provider;
    $this->pluginManager = $pluginManager;

    $this->groupStorage = $groupStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('router.route_provider'),
      $container->get('plugin.manager.etree_links'),
      $container->get('entity_type.manager')->getStorage('etree_group')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];

    foreach ($this->pluginManager->getDefinitions() as $id => $definition) {
      $groups = $this->getGroups($definition);
      $appear_on = $definition['appears_on'];

      foreach ($groups as $group) {
        $group_id = $group->id();

        $appear_on_route = $this->createRouteName($group_id, $appear_on['bundle'], $appear_on['page']);

        /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
        $action_id = str_replace('-', '_', "{$group->id()}.{$id}.");

        $router_parameters = [
          'group_id' => $group_id,
          'etree_type' => $appear_on['bundle'],
          'etree_type_child' => $definition['bundle'],
        ];

        $this->derivatives[$action_id] = [
          'route_name' => $this->createRouteName($group_id, $definition['bundle'], $definition['link']),
          'route_parameters' => $router_parameters,
          'title' => $definition['title'],
          'title_child' => $definition['title_child'],
          'title_context' => isset($definition['title_context']) ? $definition['title_context'] : NULL,
          'appears_on' => [$appear_on],
        ];

      }
    }

    foreach ($this->derivatives as &$entry) {
      $entry += $base_plugin_definition;
    }
    return $this->derivatives;
  }


  /**
   * @param $group_id
   * @param $action
   *
   * @return string
   */
  protected function createRouteName($group_id, $bundle_id, $action) {
    if ($action === 'collection') {
      $route_name = ETreeCommon::getGroupViewsRouteName($group_id);
    }
    else {
      $route_name = ETreeHtmlRouteProvider::createRouteName($group_id, $bundle_id, $action);
    }
    return $route_name;
  }

  protected function getGroups($definition) {
    $groups = isset($definition['groups']) ? $definition['groups'] : NULL;

    return $this->groupStorage->loadMultiple($groups);
  }


}
