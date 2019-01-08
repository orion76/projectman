<?php

namespace Drupal\aport\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Id map plugin plugin manager.
 */
class IdMapPluginManager extends DefaultPluginManager {


  /**
   * Constructs a new IdMapPluginManager object.
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
    parent::__construct('Plugin/IdMapPlugin', $namespaces, $module_handler, 'Drupal\aport\Plugin\IdMapPluginInterface', 'Drupal\aport\Annotation\IdMapPlugin');

    $this->alterInfo('aport_id_map_plugin_info');
    $this->setCacheBackend($cache_backend, 'aport_id_map_plugin_plugins');
  }

}
