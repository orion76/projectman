<?php


namespace Drupal\aport\destination;


interface AportDestinationPluginInterface {

  public function save($data);
}