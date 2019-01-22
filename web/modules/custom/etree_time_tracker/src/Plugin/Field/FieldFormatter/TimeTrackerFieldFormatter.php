<?php

namespace Drupal\etree_time_tracker\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'time_tracker_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "time_tracker_field_formatter",
 *   label = @Translation("Time tracker field formatter"),
 *   field_types = {
 *     "time_tracker_field_type"
 *   }
 * )
 */
class TimeTrackerFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      if (!empty($item->start_time) && !empty($item->end_time)) {
        $elements[$delta] = [
          '#theme' => 'item_list',
          '#items' => $this->viewValue($item),
        ];
      }
    }
    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return array
   *   The render item list output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    $values = [
      'start_time' => Html::escape($item->start_time),
      'end_time' => Html::escape($item->end_time),
    ];
    return $values;
  }

}
