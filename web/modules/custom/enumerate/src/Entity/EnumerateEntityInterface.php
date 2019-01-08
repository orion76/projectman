<?php

namespace Drupal\enumerate\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Enumerate entities.
 *
 * @ingroup enumerate
 */
interface EnumerateEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Enumerate name.
   *
   * @return string
   *   Name of the Enumerate.
   */
  public function getName();

  /**
   * Sets the Enumerate name.
   *
   * @param string $name
   *   The Enumerate name.
   *
   * @return \Drupal\enumerate\Entity\EnumerateEntityInterface
   *   The called Enumerate entity.
   */
  public function setName($name);

  /**
   * Gets the Enumerate creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Enumerate.
   */
  public function getCreatedTime();

  /**
   * Sets the Enumerate creation timestamp.
   *
   * @param int $timestamp
   *   The Enumerate creation timestamp.
   *
   * @return \Drupal\enumerate\Entity\EnumerateEntityInterface
   *   The called Enumerate entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Enumerate published status indicator.
   *
   * Unpublished Enumerate are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Enumerate is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Enumerate.
   *
   * @param bool $published
   *   TRUE to set this Enumerate to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\enumerate\Entity\EnumerateEntityInterface
   *   The called Enumerate entity.
   */
  public function setPublished($published);

  /**
   * Gets the Enumerate revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Enumerate revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\enumerate\Entity\EnumerateEntityInterface
   *   The called Enumerate entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Enumerate revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Enumerate revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\enumerate\Entity\EnumerateEntityInterface
   *   The called Enumerate entity.
   */
  public function setRevisionUserId($uid);

}
