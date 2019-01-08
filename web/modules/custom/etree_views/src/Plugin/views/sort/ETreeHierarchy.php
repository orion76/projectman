<?php

namespace Drupal\etree_views\Plugin\views\sort;

use Drupal\views\Plugin\views\sort\SortPluginBase;

/**
 * Default implementation of the base sort plugin.
 *
 * @ingroup views_sort_handlers
 *
 * @ViewsSort("etree_hierarchy")
 */
class ETreeHierarchy extends SortPluginBase {

  /**
   * Called to add the sort to a query.
   */
  public function query() {
    $this->ensureMyTable();
    /** @var \Drupal\views\Plugin\views\query\Sql $query */

    $expr = "IF(LENGTH(etree_hierarchy.etree_path),CONCAT(etree_hierarchy.etree_path, '.', etree_hierarchy.id),etree_hierarchy.id)";
    $this->query->addOrderBy(NULL, $expr, $this->options['order'], 'order_path');

    $this->query->addOrderBy($this->tableAlias, 'etree_weight', $this->options['order']);

    //    $this->query->orderby[] = [
    //      'field' => $exp,
    //      'direction' => $this->options['order'],
    //      'alias' => 'path_order',
    //    ];

    // Add the field.

  }

}
