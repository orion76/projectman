<?php

namespace Drupal\etree\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Defines an interface for Example plugin plugins.
 */
interface ETreeRoutesPluginInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {


  public function getRoutes();

  public function getRouteName($group_id, $action);

}
