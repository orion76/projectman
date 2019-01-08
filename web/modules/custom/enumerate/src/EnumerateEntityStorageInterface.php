<?php

namespace Drupal\enumerate;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\enumerate\Entity\EnumerateEntityInterface;

/**
 * Defines the storage handler class for Enumerate entities.
 *
 * This extends the base storage class, adding required special handling for
 * Enumerate entities.
 *
 * @ingroup enumerate
 */
interface EnumerateEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Enumerate revision IDs for a specific Enumerate.
   *
   * @param \Drupal\enumerate\Entity\EnumerateEntityInterface $entity
   *   The Enumerate entity.
   *
   * @return int[]
   *   Enumerate revision IDs (in ascending order).
   */
  public function revisionIds(EnumerateEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Enumerate author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Enumerate revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\enumerate\Entity\EnumerateEntityInterface $entity
   *   The Enumerate entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(EnumerateEntityInterface $entity);

  /**
   * Unsets the language for all Enumerate with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
