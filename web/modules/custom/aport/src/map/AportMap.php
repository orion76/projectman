<?php


namespace Drupal\aport\map;


class AportMap {

  public function map($data) {
    $plugin = $this->getPlugin();
    return $plugin->map($data);
  }

  /**
   * @return AportMapPluginInterface
   */
  protected function getPlugin() {

  }
}