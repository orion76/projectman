<?php

namespace Drupal\aport\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Map plugin plugin manager.
 */
class MapPluginManager extends DefaultPluginManager {


  /**
   * Constructs a new MapPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/MapPlugin', $namespaces, $module_handler, 'Drupal\aport\Plugin\MapPluginInterface', 'Drupal\aport\Annotation\MapPlugin');

    $this->alterInfo('aport_map_plugin_info');
    $this->setCacheBackend($cache_backend, 'aport_map_plugin_plugins');
  }

}
