<?php

namespace Drupal\etree_computed\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\IntegerItem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\etree_computed\ComputedFieldTypeBase;
use Drupal\etree_computed\ComputedFieldTypeTrait;

/**
 * Plugin implementation of the 'etree_computed_field_type' field type.
 *
 * @FieldType(
 *   id = "etree_computed_integer",
 *   label = @Translation("Integer"),
 *   description = @Translation("My Field Type"),
 *   category = @Translation("ETree computed"),
 *   default_widget = "etree_computed_integer",
 *   default_formatter = "etree_computed_integer"
 * )
 */
class ComputedInteger extends IntegerItem implements FieldTypeComputedInterface {

  use ComputedFieldTypeTrait;


  public function calculate() {

  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties = parent::propertyDefinitions($field_definition);
    ComputedFieldTypeBase::addPropertyDefinitions($properties, $field_definition);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);
    ComputedFieldTypeBase::addSchema($field_definition, $schema);
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::fieldSettingsForm($form, $form_state);
    $this->addFieldSettingsForm($element, $form, $form_state);
    return $element;
  }
}
