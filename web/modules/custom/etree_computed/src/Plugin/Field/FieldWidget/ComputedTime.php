<?php

namespace Drupal\etree_computed\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\etree_computed\Plugin\Field\FieldType\FieldTypeComputedInterface;
use Drupal\etree_computed\Time;
use Drupal\time_field\Plugin\Field\FieldWidget\TimeWidget;

/**
 * Plugin implementation of the 'number' widget.
 *
 * @FieldWidget(
 *   id = "etree_computed_time",
 *   label = @Translation("ETree computed Time"),
 *   field_types = {
 *     "etree_computed_time",
 *   }
 * )
 */
class ComputedTime extends TimeWidget {

  use ComputedWidgetTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'enabled' => FALSE,
        'step' => 'any',
      ] + parent::defaultSettings();
  }


  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    //    $element['#type'] = 'item';
    //    $element['#tree'] = TRUE;

    $settings = $this->getFieldSettings();

    if ($settings['input_self']) {
      $element['value']['#type'] = 'time_long';

      $total_title = $this->t('Total');

      if (empty($element['value']['#default_value'])) {
        $element['value']['#default_value'] = Time::createFromTimestamp(0)->formatForWidget();
      }
    }
    else {
      $element['value']['#type'] = 'value';
      $total_title = $element['value']['#title'];
    }

    $element['total'] = [
      '#type' => 'item',
      '#title' => $total_title,
      '#markup' => $this->viewComputedValue($items[$delta]),
    ];

    $element['value_computed'] = ['#type' => 'value', '#value' => $items[$delta]->value_computed];

    return $element;
  }

  protected function viewComputedValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    /** @var FieldTypeComputedInterface $item */
    $time = Time::createFromTimestamp($item->getComputedValue());
    return $time->format('H:i:s');
  }
}
