<?php


namespace Drupal\etree\Entity;


use Drupal;

trait ETreeRoutesTrait {

  /** @var \Drupal\etree\Plugin\ETreeRoutesPluginManagerInterface $etreeRoutesPluginManager */
  private $etreeRoutesPluginManager;

  protected function getRoutesPluginManager() {
    if (!$this->etreeRoutesPluginManager) {
      $this->etreeRoutesPluginManager = Drupal::service('plugin.manager.etree_routes');
    }

    return $this->etreeRoutesPluginManager;
  }

}