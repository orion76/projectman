<?php

namespace Drupal\etree;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\etree\Entity\ETreeInterface;

/**
 * Defines the storage handler class for ETree entities.
 *
 * This extends the base storage class, adding required special handling for
 * ETree entities.
 *
 * @ingroup etree
 */
interface ETreeStorageInterface extends ContentEntityStorageInterface {

  public function load($id);

  public function loadChildren($id, $level);

  public function updateHierarchy(array $hierarchy_data);

  public function updateWeight(ETreeInterface $item);

  /**
   * @return \Drupal\etree\ETreeStorageHierarchy
   */
  public function getHierarchy(): \Drupal\etree\ETreeStorageHierarchy;

  /**
   * Gets a list of ETree revision IDs for a specific ETree.
   *
   * @param \Drupal\etree\Entity\ETreeInterface $entity
   *   The ETree entity.
   *
   * @return int[]
   *   ETree revision IDs (in ascending order).
   */
  public function revisionIds(ETreeInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as ETree author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   ETree revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\etree\Entity\ETreeInterface $entity
   *   The ETree entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(ETreeInterface $entity);

  /**
   * Unsets the language for all ETree with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);


}
