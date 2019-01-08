<?php


namespace Drupal\etree_computed;


use function array_sum;
use function array_unshift;
use function call_user_func_array;
use function get_called_class;

class ChildCalculateOperations {

  static function getOptions() {
    return array_map([static::class, 'getTitle'], static::getOperations());
  }


  static function getTitle($item) {
    return $item['title'];
  }

  static function getOperations() {
    return [
      'sum' => [
        'title' => t('Sum'),
        'method' => 'calculateSum',
      ],
    ];
  }

  static function calculate($operation, $self_value, $child_values) {
    $operations = static::getOperations();
    $method = [get_called_class(), $operations[$operation]['method']];
    if (!empty($self_value)) {
      array_unshift($child_values, $self_value);
    }
    return call_user_func_array($method, [$child_values]);
  }

  static function calculateSum(array $values) {
    return array_sum($values);
  }
}