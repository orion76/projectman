<?php


namespace Drupal\aport\parser;


class AportParser implements AportParserInterface {

  public function parse($data) {
    $plugin = $this->getPlugin();
    return $plugin->parse($data);
  }

  /**
   * @return AportParserPluginInterface
   */
  protected function getPlugin() {

  }
}