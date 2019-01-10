<?php

namespace Drupal\etree_time_tracker;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Time tracker entity entities.
 *
 * @ingroup etree_time_tracker
 */
class TimeTrackerEntityListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Time tracker entity ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\etree_time_tracker\Entity\TimeTrackerEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.time_tracker_entity.edit_form',
      ['time_tracker_entity' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
