<?php

namespace Drupal\etree_time_tracker\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'time_tracker_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "time_tracker_field_widget",
 *   label = @Translation("Time tracker field widget"),
 *   field_types = {
 *     "time_tracker_field_type"
 *   }
 * )
 */
class TimeTrackerFieldWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element['rows']['#description'] = $this->t('Time tracker');
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['start_time'] = [
      '#type' => 'textfield',
      '#default_value' => NULL,
      '#required' => $element['#required'],
    ];

    if ($items[$delta]->start_time) {
      $start_time = $items[$delta]->start_time;
      $element['start_time']['#default_value'] = $start_time;
    }

    $element['end_time'] = [
      '#type' => 'textfield',
      '#default_value' => NULL,
      '#required' => $element['#required'],
    ];

    if ($items[$delta]->end_time) {
      $end_time = $items[$delta]->end_time;
      $element['end_time']['#default_value'] = $end_time;
    }

    $element['start_time']['#title'] = $this->t('Start time');
    $element['end_time']['#title'] = $this->t('End time');

    return $element;
  }

  /**
   * #element_validate callback to ensure that the start time <= the end time.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
  public function validateStartEnd(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $start_date = $element['start_time']['#value'];
    $end_date = $element['end_time']['#value'];
    if ($start_date > $end_date) {
      $form_state->setError($element, $this->t('The @title end time cannot be before the start time', ['@title' => $element['#title']]));
    }
  }

}
