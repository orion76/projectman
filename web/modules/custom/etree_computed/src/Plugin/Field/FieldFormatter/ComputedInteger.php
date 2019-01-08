<?php


namespace Drupal\etree_computed\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\NumericFormatterBase;

/**
 * Class ETreeComputedFormatterInteger
 * @FieldFormatter(
 *   id = "etree_computed_integer",
 *   label = @Translation("ETree computed Integer"),
 *   field_types = {
 *     "etree_computed_integer"
 *   }
 * )
 *
 * @package Drupal\etree_computed\Plugin\Field\FieldFormatter
 */
class ComputedInteger extends NumericFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'thousand_separator' => '',
        'prefix_suffix' => TRUE,
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  protected function numberFormat($number) {
    return number_format($number, 0, '', $this->getSetting('thousand_separator'));
  }

}