<?php

namespace Drupal\etree\Plugin;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Factory\ContainerFactory;


/**
 * Provides the Example plugin plugin manager.
 */
class ETreeRoutesPluginManager extends DefaultPluginManager implements ETreeRoutesPluginManagerInterface {


  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ETreeRoutes',
      $namespaces,
      $module_handler,
      'Drupal\etree\ETreeRoutesPluginInterface',
      'Drupal\etree\Annotation\ETreeRoutes'

    );
    $this->factory = new ContainerFactory($this, 'Drupal\etree\Plugin\ETreeRoutesPluginInterface');


    $this->moduleHandler = $module_handler;
    $this->alterInfo('etree_plugin_routes');
    $this->setCacheBackend($cache_backend, 'etree_plugin_routes');

  }


  public function getRoutes() {
    $routes = [];
    foreach ($this->getDefinitions() as $plugin_id => $plugin) {
      $instance = $this->createInstance($plugin_id);
      $routes += $instance->getRoutes();
    }
    return $routes;
  }

  public function getRouteName($group_id, $view_name) {
    foreach ($this->getDefinitions() as $plugin_id => $plugin) {
      /** @var \Drupal\etree\Plugin\ETreeRoutesPluginInterface $instance */
      $instance = $this->createInstance($plugin_id);
      if ($name = $instance->getRouteName($group_id, $view_name)) {
        return $name;
      }
    }
  }

}
