<?php


namespace Drupal\etree_context;


use Drupal;

trait ETreeContextTrait {

  /** @var \Drupal\etree_context\Plugin\ETreeContextPluginManagerInterface $etreeContextPluginManager */
  private $etreeContextPluginManager;

  protected function getContextPluginManager() {
    if (!$this->etreeContextPluginManager) {
      $this->etreeContextPluginManager = Drupal::service('plugin.manager.etree_context');
    }

    return $this->etreeContextPluginManager;
  }

}