<?php

namespace Drupal\etree_computed\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\etree_computed\ComputedFieldTypeBase;

trait ComputedWidgetTrait {

  function getInputValue($field) {
    return $field->{ComputedFieldTypeBase::COMPUTED_PROPERTY};
  }

  function getComputedValue(FieldItemInterface $field) {
    return $field->value;
  }


  public function createComputedElement(FieldItemListInterface $items, $delta, array &$element, array &$form, FormStateInterface $form_state) {

    $additional = [
      '#type' => 'value',
      '#value' => $this->getComputedValue($items[$delta]),
    ];
    return $element + $additional;
  }
}
