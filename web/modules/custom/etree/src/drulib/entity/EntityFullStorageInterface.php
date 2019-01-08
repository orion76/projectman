<?php

namespace Drupal\etree\drulib\entity;

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
interface EntityFullStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(ETreeInterface $entity);

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(ETreeInterface $entity);

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language);
}