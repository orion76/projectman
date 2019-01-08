<?php

namespace Drupal\etree_context\Plugin;

use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Factory\ContainerFactory;


/**
 * Provides the Example plugin plugin manager.
 */
class ETreeContextPluginManager extends DefaultPluginManager implements ETreeContextPluginManagerInterface {


  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ETreeContext',
      $namespaces,
      $module_handler,
      'Drupal\etree\ETreeRoutesPluginInterface',
      'Drupal\etree_context\Annotation\ETreeContext'

    );
    $this->factory = new ContainerFactory($this, 'Drupal\etree_context\Plugin\ETreeContextPluginInterface');

    $this->moduleHandler = $module_handler;
    $this->alterInfo('etree_plugin_context');
    $this->setCacheBackend($cache_backend, 'etree_plugin_context');

  }


  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {
    $context = [];
    foreach ($this->getDefinitions() as $plugin_id => $plugin) {
      /** @var \Drupal\etree_context\Plugin\ETreeContextPluginInterface $instance */
      $instance = $this->createInstance($plugin_id);
      $context += $instance->getRuntimeContexts($unqualified_context_ids);
    }

    $this->alterRuntimeContexts($unqualified_context_ids, $context);
    return $context;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRuntimeContexts(array $unqualified_context_ids, &$context) {

    foreach ($this->getDefinitions() as $plugin_id => $plugin) {
      /** @var \Drupal\etree_context\Plugin\ETreeContextPluginInterface $instance */
      $instance = $this->createInstance($plugin_id);
      $instance->alterRuntimeContexts($unqualified_context_ids, $context);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    $context = [];
    foreach ($this->getDefinitions() as $plugin_id => $plugin) {
      /** @var \Drupal\etree_context\Plugin\ETreeContextPluginInterface $instance */
      $instance = $this->createInstance($plugin_id);
      $context += $instance->getAvailableContexts();
    }
    return $context;
  }
}
