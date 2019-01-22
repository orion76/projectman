<?php

namespace Drupal\etree_time_tracker\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'time_tracker_field_type' field type.
 *
 * @FieldType(
 *   id = "time_tracker_field_type",
 *   label = @Translation("Time tracker field type"),
 *   description = @Translation("Time Tracker double field type"),
 *   default_formatter = "time_tracker_field_formatter",
 *   default_widget = "time_tracker_field_widget",
 * )
 */
class TimeTrackerFieldType extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    $schema = [
      'columns' => [
        'start_time' => [
          'type' => 'varchar',
          'length' => 20,
          'not null' => FALSE,
        ],
        'end_time' => [
          'type' => 'varchar',
          'length' => 20,
          'not null' => FALSE,
        ],
      ],
      'indexes' => [
        'start_time' => ['start_time'],
        'end_time' => ['end_time'],
      ],
    ];

    return $schema;

  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $start_time = $this->get('start_time')->getValue();
    $end_time = $this->get('end_time')->getValue();
    return ($start_time === NULL || $start_time === '') && ($end_time === NULL || $end_time === '');
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['start_time'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Start time value'));

    $properties['end_time'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('End time value'));

    return $properties;
  }



}
