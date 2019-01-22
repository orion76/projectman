<?php

namespace Drupal\etree_time_tracker;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Time tracker entity entity.
 *
 * @see \Drupal\etree_time_tracker\Entity\TimeTrackerEntity.
 */
class TimeTrackerEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\etree_time_tracker\Entity\TimeTrackerEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished time tracker entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published time tracker entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit time tracker entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete time tracker entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add time tracker entity entities');
  }

}
