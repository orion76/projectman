<?php


namespace Drupal\etree_computed\Plugin\Field\FieldFormatter;


use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\etree_computed\Plugin\Field\FieldType\FieldTypeComputedInterface;
use Drupal\etree_computed\Time;
use Drupal\time_field\Plugin\Field\FieldFormatter\TimeFormatter;

/**
 * Class ComputedTime
 * @FieldFormatter(
 *   id = "etree_computed_time",
 *   label = @Translation("ETree computed Time"),
 *   field_types = {
 *     "etree_computed_time"
 *   }
 * )
 *
 * @package Drupal\etree_computed\Plugin\Field\FieldFormatter
 */
class ComputedTime extends TimeFormatter {

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    /** @var FieldTypeComputedInterface $item */
    $time = Time::createFromTimestamp($item->value);
    return $time->format($this->getSetting('time_format'));
  }

  protected function viewComputedValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    /** @var FieldTypeComputedInterface $item */
    $time = Time::createFromTimestamp($item->getComputedValue());
    return $time->format($this->getSetting('time_format'));
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];


    foreach ($items as $delta => $item) {

      $total=$this->viewComputedValue($item);
      $self=$this->viewValue($item);
      $elements[$delta] = [
        'value' => [
           '#markup' =>"{$total} ({$self})" ,
        ],
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['time_format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Time Format'),
      '#default_value' => (string) $this->getSetting('time_format'),
      '#description' => $this->getTimeDescription(),
    ];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'time_format' => 'h:i a',
      ] + parent::defaultSettings();
  }

  /**
   * Description of the time field.
   *
   * @return string
   *   Description of the time field.
   */
  private function getTimeDescription() {
    $output = '';
    $output .= $this->t('a - Lowercase am or pm') . '<br/>';
    $output .= $this->t('A - Uppercase AM or PM') . '<br/>';
    $output .= $this->t('B - Swatch Internet time (000 to 999)') . '<br/>';
    $output .= $this->t('g - 12-hour format of an hour (1 to 12)') . '<br/>';
    $output .= $this->t('G - 24-hour format of an hour (0 to 23)') . '<br/>';
    $output .= $this->t('h - 12-hour format of an hour (01 to 12)') . '<br/>';
    $output .= $this->t('H - 24-hour format of an hour (00 to 23)') . '<br/>';
    $output .= $this->t('i - Minutes with leading zeros (00 to 59)') . '<br/>';
    $output .= $this->t('s - Seconds, with leading zeros (00 to 59)') . '<br/>';
    return $output;
  }

}