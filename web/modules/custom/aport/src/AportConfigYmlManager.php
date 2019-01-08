<?php

namespace Drupal\aport;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;

/**
 * Provides the default aport_config_yml manager.
 */
class AportConfigYmlManager extends DefaultPluginManager implements AportConfigYmlManagerInterface {

  /**
   * Provides default values for all aport_config_yml plugins.
   *
   * @var array
   */
  protected $defaults = [
    // Add required and optional plugin properties.
    'id' => '',
    'label' => '',
    'enabled' => FALSE,
    'source' => ['plugin' => '', 'config' => ''],
    'parser' => ['plugin' => '', 'config' => ''],
    'process' => ['plugin' => '', 'config' => ''],
    'idMap' => ['plugin' => '', 'config' => ''],
    'map' => ['plugin' => '', 'config' => ''],
    'report' => ['plugin' => '', 'config' => ''],
  ];

  /**
   * Constructs a new AportConfigYmlManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   */
  public function __construct(ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend) {
    // Add more services as required.
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache_backend, 'aport_config_yml', ['aport_config_yml']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new YamlDiscovery('aport.yml', $this->moduleHandler->getModuleDirectories());
      $this->discovery->addTranslatableProperty('label', 'label_context');
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);

    // You can add validation of the plugin definition here.
    if (empty($definition['id'])) {
      throw new PluginException(sprintf('Example plugin property (%s) definition "is" is required.', $plugin_id));
    }
  }


  protected static function filterEnabled($definition) {
    return $definition->enabled;
  }


  /**
   * Finds plugin definitions.
   *
   * @return array
   *   List of definitions to store in cache.
   */
  protected function findDefinitions() {

    $definitions = array_filter(parent::findDefinitions(), [$this, 'filterEnabled']);

    return $definitions;
  }

}
