<?php

namespace Drupal\etree_links\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\etree_group\Entity\ETreeGroupInterface;
use Drupal\etree\Entity\ETreeInterface;

/**
 * Defines an interface for Example plugin plugins.
 */
interface ETreeLinksPluginInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  public function getRouteName(ETreeGroupInterface $group);

  public function getRouteParameters(ETreeGroupInterface $group, $page, ETreeInterface $entity = NULL);

  public function getLink();

  public function getBundle();

  public function getWeight();

  public function getTitle(ETreeGroupInterface $group, $page, ETreeInterface $entity = NULL);
}
