<?php

namespace Drupal\aport\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Process plugin plugin manager.
 */
class ProcessPluginManager extends DefaultPluginManager {


  /**
   * Constructs a new ProcessPluginManager object.
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
    parent::__construct('Plugin/ProcessPlugin', $namespaces, $module_handler, 'Drupal\aport\Plugin\ProcessPluginInterface', 'Drupal\aport\Annotation\ProcessPlugin');

    $this->alterInfo('aport_process_plugin_info');
    $this->setCacheBackend($cache_backend, 'aport_process_plugin_plugins');
  }

}
