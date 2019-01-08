<?php

namespace Drupal\enumerate;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class EnumerateItemEntityStorage extends SqlContentEntityStorage implements EnumerateItemEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(EnumerateItemEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {enumerate_item_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(EnumerateItemEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {enumerate_item_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('enumerate_item_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
