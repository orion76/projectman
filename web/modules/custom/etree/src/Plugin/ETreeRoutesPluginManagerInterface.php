<?php

namespace Drupal\etree\Plugin;


/**
 * Provides the Example plugin plugin manager.
 */
interface ETreeRoutesPluginManagerInterface {

  public function getRoutes();

  public function getRouteName($group_id, $view_name);
}