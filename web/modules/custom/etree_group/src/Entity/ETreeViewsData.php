<?php

namespace Drupal\etree_group\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for ETree entities.
 */
class ETreeViewsData extends EntityViewsData {


  protected function fieldETreeItemGroup(&$data) {

    $data['etree_hierarchy']['etree_item_group'] = [
      'title' => $this->t('ETree group'),
      'help' => $this->t('ETree group.'),
    ];

    $data['etree_hierarchy']['etree_item_group']['field'] = [
      'title' => $this->t('ETree group'),
      'help' => $this->t('ETree group'),
      'id' => 'etree_item_group',
    ];
    $data['etree_hierarchy']['etree_item_group']['filter'] = [
      'id' => 'etree_item_group',
      'title' => $this->t('Group'),
      'help' => $this->t('Filter items by group'),
    ];


  }


  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $this->fieldETreeItemGroup($data);

    return $data;
  }


}
