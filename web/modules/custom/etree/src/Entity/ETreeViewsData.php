<?php

namespace Drupal\etree\Entity;

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

  protected function fieldETreeParent(&$data) {
    $data['etree_hierarchy']['etree_parent_id'] = [
      'title' => $this->t('ETree Parent ID'),
      'help' => $this->t('ETree Parent ID.'),
    ];

    $data['etree_hierarchy']['etree_parent_id']['field'] = [
      'title' => $this->t('ETree Parent ID'),
      'help' => $this->t('ETree Parent ID'),
      'id' => 'etree_parent_id',
    ];

    $data['etree_hierarchy']['etree_parent_id']['filter'] = [
      'id' => 'etree_parent_id',
      'title' => $this->t('ETree Parent ID'),
      'help' => $this->t('Filter items by parent ID'),
    ];

    $data['etree_hierarchy']['etree_parent_id']['argument'] = [
      'id' => 'etree_parent_id',
      'title' => $this->t('ETree Parent ID'),
      'help' => $this->t('Filter items by parent ID'),
      'numeric' => TRUE,
    ];

  }

  protected function fieldETreeItemPath(&$data) {

    $data['etree_hierarchy']['etree_item_path'] = [
      'title' => $this->t('ETree item path'),
      'help' => $this->t('ETree item path.'),
    ];


    $data['etree_hierarchy']['etree_item_path']['field'] = [

      'title' => $this->t('ETree item path'),
      'help' => $this->t('ETree item path'),
      'id' => 'etree_item_path',
    ];

    $data['etree_hierarchy']['etree_item_path']['sort'] = [
      'id' => 'etree_hierarchy',
      'title' => $this->t('ETree item path'),
      'help' => $this->t('Sort by ETree hierarchy'),

    ];

  }

  protected function fieldETreeWeight(&$data) {

    $data['etree_hierarchy']['etree_weight'] = [
      'title' => $this->t('ETree Weight'),
      'help' => $this->t('ETree Weight.'),
    ];
    $data['etree_hierarchy']['etree_weight']['field'] = [
      'title' => $this->t('ETree Weight'),
      'help' => $this->t('ETree Weight'),
      'id' => 'etree_item_weight',
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['etree_field_data']['table']['join']['etree_hierarchy'] = [
      'field' => 'id',
      'left_field' => 'id',
      'type' => 'INNER',
    ];

    $data['etree_hierarchy']['table']['group'] = $this->t('ETree');

    $data['etree_hierarchy']['table']['provider'] = 'etree';

    $data['etree_hierarchy']['table']['join'] = [
      'etree_field_data' => [
        'left_field' => 'id',
        'field' => 'id',
      ],
    ];

    //    $data['etree_hierarchy']['table']['join'] = [
    //      'etree_hierarchy' => [
    //        'left_field' => 'id',
    //        'field' => 'id',
    //        'left_table' => 'etree_hierarchy',
    //      ],
    //      'etree_field_data' => [
    //        'left_field' => 'id',
    //        'field' => 'id',
    //      ],
    //    ];

    $this->fieldETreeItemGroup($data);
    $this->fieldETreeItemPath($data);
    $this->fieldETreeParent($data);
    $this->fieldETreeWeight($data);

    return $data;
  }


}
