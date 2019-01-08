<?php


namespace Drupal\etree_computed\Plugin\Field\FieldType;


interface FieldTypeComputedInterface {

  public function calculate();
  public function getComputedValue();
}