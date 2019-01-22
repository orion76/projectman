<?php

namespace Drupal\etree_time_tracker\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Time tracker entity entities.
 *
 * @ingroup etree_time_tracker
 */
interface TimeTrackerEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Time tracker entity name.
   *
   * @return string
   *   Name of the Time tracker entity.
   */
  public function getName();

  /**
   * Sets the Time tracker entity name.
   *
   * @param string $name
   *   The Time tracker entity name.
   *
   * @return \Drupal\etree_time_tracker\Entity\TimeTrackerEntityInterface
   *   The called Time tracker entity entity.
   */
  public function setName($name);

  /**
   * Gets the Time tracker entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Time tracker entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Time tracker entity creation timestamp.
   *
   * @param int $timestamp
   *   The Time tracker entity creation timestamp.
   *
   * @return \Drupal\etree_time_tracker\Entity\TimeTrackerEntityInterface
   *   The called Time tracker entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Time tracker entity published status indicator.
   *
   * Unpublished Time tracker entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Time tracker entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Time tracker entity.
   *
   * @param bool $published
   *   TRUE to set this Time tracker entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\etree_time_tracker\Entity\TimeTrackerEntityInterface
   *   The called Time tracker entity entity.
   */
  public function setPublished($published);

}
