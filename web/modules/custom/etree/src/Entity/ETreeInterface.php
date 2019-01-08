<?php

namespace Drupal\etree\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining ETree entities.
 *
 * @ingroup etree
 */
interface ETreeInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  public function setHierarchyData($hierarchy_data);

  /**
   * @return boolean
   */
  public function isWeightChanged();

  public function buildLinks($view_mode);

  /**
   * @return boolean
   */
  public function isParentChanged();

  /**
   * @return \Drupal\Core\Entity\FieldableEntityInterface
   */
  public function getParentId();

  public function getParent();

  //  public function getHierarchy();

  /**
   * @return \Drupal\etree\ETreeHierarchyData
   */
  public function getHierarchyData();

  public function getParentIds();

  public function getGroupId();

  public function getGroup();

  public function getLevel();

  public function getWeight();

  /**
   * @return boolean
   */
  //  public function hasHierarchy();

  /**
   * Gets the ETree name.
   *
   * @return string
   *   Name of the ETree.
   */
  public function getName();

  /**
   * Sets the ETree name.
   *
   * @param string $name
   *   The ETree name.
   *
   * @return \Drupal\etree\Entity\ETreeInterface
   *   The called ETree entity.
   */
  public function setName($name);

  /**
   * Gets the ETree creation timestamp.
   *
   * @return int
   *   Creation timestamp of the ETree.
   */
  public function getCreatedTime();

  /**
   * Sets the ETree creation timestamp.
   *
   * @param int $timestamp
   *   The ETree creation timestamp.
   *
   * @return \Drupal\etree\Entity\ETreeInterface
   *   The called ETree entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the ETree published status indicator.
   *
   * Unpublished ETree are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the ETree is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a ETree.
   *
   * @param bool $published
   *   TRUE to set this ETree to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\etree\Entity\ETreeInterface
   *   The called ETree entity.
   */
  public function setPublished($published);

  /**
   * Gets the ETree revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the ETree revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\etree\Entity\ETreeInterface
   *   The called ETree entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the ETree revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the ETree revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\etree\Entity\ETreeInterface
   *   The called ETree entity.
   */
  public function setRevisionUserId($uid);

  public function getOriginal();
}
