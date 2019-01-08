<?php

namespace Drupal\aport\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Report plugin plugin manager.
 */
class ReportPluginManager extends DefaultPluginManager {


  /**
   * Constructs a new ReportPluginManager object.
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
    parent::__construct('Plugin/ReportPlugin', $namespaces, $module_handler, 'Drupal\aport\Plugin\ReportPluginInterface', 'Drupal\aport\Annotation\ReportPlugin');

    $this->alterInfo('aport_report_plugin_info');
    $this->setCacheBackend($cache_backend, 'aport_report_plugin_plugins');
  }

}
