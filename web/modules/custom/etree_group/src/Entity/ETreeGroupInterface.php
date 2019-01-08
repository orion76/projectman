<?php

namespace Drupal\etree_group\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Etree group entities.
 */
interface ETreeGroupInterface extends ConfigEntityInterface {

  public function getAllowedTypes();

  public function getPathParams();

  public function getTreeTypes();

  public function getChildTypes();

}
