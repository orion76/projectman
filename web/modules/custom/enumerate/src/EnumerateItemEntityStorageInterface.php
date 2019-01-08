<?php

namespace Drupal\enumerate;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\enumerate\Entity\EnumerateItemEntityInterface;

/**
 * Defines the storage handler class for Enumerate item entities.
 *
 * This extends the base storage class, adding required special handling for
 * Enumerate item entities.
 *
 * @ingroup enumerate
 */
interface EnumerateItemEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Enumerate item revision IDs for a specific Enumerate item.
   *
   * @param \Drupal\enumerate\Entity\EnumerateItemEntityInterface $entity
   *   The Enumerate item entity.
   *
   * @return int[]
   *   Enumerate item revision IDs (in ascending order).
   */
  public function revisionIds(EnumerateItemEntityInterface $entity);


  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\enumerate\Entity\EnumerateItemEntityInterface $entity
   *   The Enumerate item entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(EnumerateItemEntityInterface $entity);

  /**
   * Unsets the language for all Enumerate item with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
