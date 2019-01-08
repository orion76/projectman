<?php

namespace Drupal\enumerate\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Enumerate item entities.
 *
 * @ingroup enumerate
 */
interface EnumerateItemEntityInterface extends ContentEntityInterface, RevisionLogInterface   {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Enumerate item name.
   *
   * @return string
   *   Name of the Enumerate item.
   */
  public function getName();

  /**
   * Sets the Enumerate item name.
   *
   * @param string $name
   *   The Enumerate item name.
   *
   * @return \Drupal\enumerate\Entity\EnumerateItemEntityInterface
   *   The called Enumerate item entity.
   */
  public function setName($name);

}
