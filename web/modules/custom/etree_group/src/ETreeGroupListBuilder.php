<?php

namespace Drupal\etree_group;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Etree group entities.
 */
class ETreeGroupListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Etree group');
    $header['id'] = $this->t('Machine name');
    $header['views'] = $this->t('Views');
    $header['allowed_types'] = $this->t('Bundles');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $entity */
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();


    $row['views'] = implode(', ', array_keys($entity->getCollectionViews()));
    $row['allowed_types'] = implode(', ', $entity->getAllowedTypes());
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

}
