<?php

namespace Drupal\etree_views\Plugin\views\field;

use Drupal\etree\ETreeCommon;
use Drupal\views\ResultRow;

/**
 * Handler for showing etree_item_path.
 *
 * @property \Drupal\views\Plugin\views\query\Sql query
 * @ingroup views_field_handlers
 *
 * @ViewsField("etree_item_path")
 */
class ETreeItemPath extends ETreeItemFieldBase {

  /**
   * TODO Релизовать генерацию пути по дркгим полям сущности (КОД и т.д.)
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    //    $entity = $this->getEntity($values);
    /** @var array $ids */
    $path = [];
    foreach (ETreeCommon::getParentFields([ETreeCommon::PARENT_ID]) as $parent_field) {
      $view_field = "{$this->tableAlias}_{$parent_field}";
      if (!isset($values->{$view_field}) || empty($values->{$view_field})) {
        break;
      }
      $path[$parent_field] = $values->{$view_field};
    }

    $path[$parent_field] = $values->id;
    return ETreeCommon::createPath($path);
    //    return $values->order_path;
  }

  public function query($group_by = FALSE) {
    $this->ensureMyTable();

    foreach (ETreeCommon::getParentFields([ETreeCommon::PARENT_ID]) as $parent_field) {
      $this->query->addField($this->tableAlias, $parent_field);
    }

  }

}
