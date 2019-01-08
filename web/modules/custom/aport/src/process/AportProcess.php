<?php


namespace Drupal\aport\porcess;


use Drupal\aport\process\AportProcessInterface;
use Drupal\aport\process\AportProcessPluginInterface;

class AportProcess implements AportProcessInterface {

  public function process($data) {
    $plugin = $this->getPlugin();
    return $plugin->process($data);
  }

  /**
   * @return AportProcessPluginInterface
   */
  protected function getPlugin() {

  }
}