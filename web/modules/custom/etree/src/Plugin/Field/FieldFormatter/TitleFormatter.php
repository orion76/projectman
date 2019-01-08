<?php

namespace Drupal\etree\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'uri_link' formatter.
 *
 * @FieldFormatter(
 *   id = "title",
 *   label = @Translation("Title"),
 *   field_types = {
 *     "string",
 *   }
 * )
 */
class TitleFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if (!$item->isEmpty()) {
        $elements[$delta] = [
          '#type' => 'html_tag',
          '#tag' => 'h2',
          '#value' => $item->value,
        ];
      }
    }

    return $elements;
  }

}
