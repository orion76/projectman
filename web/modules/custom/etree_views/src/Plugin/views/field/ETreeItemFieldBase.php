<?php

namespace Drupal\etree_views\Plugin\views\field;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;


class ETreeItemFieldBase extends FieldPluginBase {

  /**



  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    // @todo: Wouldn't it be possible to use $this->base_table and no if here?

//    $this->additional_fields['id'] = ['table' => 'etree_hierarchy', 'field' => 'id'];
//
//    foreach (ETreeCommon::HIERARCHY_FIELDS as $field_name) {
//      $this->additional_fields[$field_name] = ['table' => 'etree_hierarchy', 'field' => $field_name];
//    }
  }


  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
  }

  protected function getFieldName($name) {
    return "{$this->table}_{$name}";
  }

  /**
   * TODO Релизовать генерацию пути по дркгим полям сущности (КОД и т.д.)
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    return $values->{$this->getFieldName($this->realField)};
  }

}
