<?php

namespace Drupal\enumerate;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Enumerate entity.
 *
 * @see \Drupal\enumerate\Entity\EnumerateEntity.
 */
class EnumerateEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $n=0;
    /** @var \Drupal\enumerate\Entity\EnumerateEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished enumerate entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published enumerate entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit enumerate entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete enumerate entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add enumerate entities');
  }

}
