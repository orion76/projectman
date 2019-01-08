<?php

namespace Drupal\etree_computed;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;


class ComputedFieldTypeBase {

  const COMPUTED_PROPERTY = 'value_computed';

  /**
   * {@inheritdoc}
   */
  public static function addPropertyDefinitions(&$properties, FieldStorageDefinitionInterface $field_definition) {
    $value_property = $field_definition->getMainPropertyName();
    /** @var \Drupal\Core\TypedData\DataDefinition $property */
    $property = $properties[$value_property];
    $property->setRequired(FALSE);
    $properties['value_computed'] = new DataDefinition($property->toArray());
  }


  /**
   * {@inheritdoc}
   */
  public static function addDefaultFieldSettings(&$settings) {
    $settings += [
      'input_self' => TRUE,
      'bundles' => [],
      'operation' => NULL,
    ];
  }


  /**
   * {@inheritdoc}
   */
  public static function addSchema(FieldStorageDefinitionInterface $field_definition, &$schema) {

    $value_property = $field_definition->getMainPropertyName();

    $schema['columns'][static::COMPUTED_PROPERTY] = $schema['columns'][$value_property];

    if (!isset($schema['indexes'])) {
      $schema['indexes'] = [];
    }

    $schema['indexes'] [static::COMPUTED_PROPERTY] = [static::COMPUTED_PROPERTY];

    return $schema;
  }


}
