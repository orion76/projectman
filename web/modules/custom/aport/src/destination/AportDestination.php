<?php


namespace Drupal\aport\destination;


class AportDestination implements AportDestinationInterface {

  public function save($data) {
    $plugin = $this->getPlugin();
    return $plugin->save($data);
  }

  /**
   * @return DestinationConfigPluginInterface
   */
  protected function getPlugin() {

  }
}