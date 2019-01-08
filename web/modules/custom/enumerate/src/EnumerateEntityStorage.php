<?php

namespace Drupal\enumerate;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class EnumerateEntityStorage extends SqlContentEntityStorage implements EnumerateEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(EnumerateEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {enumerate_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {enumerate_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(EnumerateEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {enumerate_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('enumerate_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
