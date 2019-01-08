<?php

namespace Drupal\etree;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the ETree entity.
 *
 * @see \Drupal\etree\Entity\ETree.
 */
class ETreeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\etree\Entity\ETreeInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished etree entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published etree entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit etree entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete etree entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add etree entities');
  }

}
