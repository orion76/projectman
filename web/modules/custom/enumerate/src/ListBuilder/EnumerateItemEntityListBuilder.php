<?php

namespace Drupal\enumerate\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Enumerate item entities.
 *
 * @ingroup enumerate
 */
class EnumerateItemEntityListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Enumerate item ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\enumerate\Entity\EnumerateItemEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.enumerate_item.edit_form',
      ['enumerate_item' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
