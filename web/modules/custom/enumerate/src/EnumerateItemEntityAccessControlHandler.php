<?php

namespace Drupal\enumerate;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Enumerate item entity.
 *
 * @see \Drupal\enumerate\Entity\EnumerateItemEntity.
 */
class EnumerateItemEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\enumerate\Entity\EnumerateItemEntityInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view enumerate item entities');
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit enumerate item entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete enumerate item entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add enumerate item entities');
  }

}
