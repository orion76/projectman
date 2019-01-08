<?php

namespace Drupal\etree;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\etree\Entity\ETreeAjaxOverview;

/**
 * Defines a class to build a listing of ETree entities.
 *
 * @ingroup etree
 */
class ETreeListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return [];
  }

  public function load() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    return [];
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function getOperations(EntityInterface $entity) {
    /** @var \Drupal\etree\Entity\ETreeInterface $entity */
    $operations = parent::getOperations($entity);
    if ($entity->access('create') && $entity->hasLinkTemplate('add-form')) {
      $url = $this->ensureDestination($entity->toUrl('add-form'));
      $url->mergeOptions(['query' => ['parent_id' => $entity->id()]]);
      $operations['add-child'] = [
        'title' => $this->t('Add child'),
        'weight' => 100,
        'url' => $url,
      ];
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   *
   * Builds the entity listing as renderable array for table.html.twig.
   *
   * @todo Add a link to add a new item to the #empty text.
   */
  public function render() {
    $collection = views_embed_view('etree', 'block_1');
//    $overview = ETreeAjaxOverview::Container();
    $page = [
      '#theme' => 'etree_collection_page',
      '#collection' => $collection,
//      '#overview' => $overview,
    ];

    return $page;
  }

}
