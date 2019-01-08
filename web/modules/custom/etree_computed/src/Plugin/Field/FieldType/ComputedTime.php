<?php

namespace Drupal\etree_computed\Plugin\Field\FieldType;

use Drupal;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\etree\ETreeStorageInterface;
use Drupal\etree_computed\ChildCalculateOperations;
use Drupal\etree_computed\ComputedFieldTypeBase;
use Drupal\etree_computed\ComputedFieldTypeTrait;
use Drupal\time_field\Plugin\Field\FieldType\TimeType;

/**
 * Plugin implementation of the 'etree_computed_field_type' field type.
 *
 * @FieldType(
 *   id = "etree_computed_time",
 *   label = @Translation("Time"),
 *   description = @Translation("My Field Type"),
 *   category = @Translation("ETree computed"),
 *   default_widget = "etree_computed_time",
 *   default_formatter = "etree_computed_time"
 * )
 */
class ComputedTime extends TimeType implements FieldTypeComputedInterface {

  use ComputedFieldTypeTrait;

  protected $storage;

  public function calculate() {
    $entity = $this->getEntity();
    $settings = $this->getSettings();
    $field_name = $this->getParent()->getName();
    $children = $this->getStorage()->loadChildren($entity->id(), $entity->getLevel());

    $child_values = [];
    foreach ($children as $child) {
      if (!isset($child->{$field_name})) {
        continue;
      }

      $field = $child->{$field_name}->get(0);

      if (!isset($field)) {
        continue;
      }
t();
      /** @var \Drupal\etree_computed\Plugin\Field\FieldType\FieldTypeComputedInterface $field */
      $child_values[] = $field->getComputedValue();
    }

    $self_value = NULL;
    if ($settings['input_self']) {
      $self_value = $this->value;
    }

    $this->value_computed = ChildCalculateOperations::calculate($settings['operation'], $self_value, $child_values);
  }

  public function getComputedValue() {
    $settings = $this->getSettings();
    $self_value = NULL;
    if ($settings['input_self']) {
      $self_value = $this->value;
    }
    $child_values = [$this->value_computed];
    return ChildCalculateOperations::calculate($settings['operation'], $self_value, $child_values);
  }

  /**
   * @return ETreeStorageInterface
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getStorage() {
    if (!isset($this->storage)) {
      $this->storage = Drupal::entityTypeManager()->getStorage('etree');
    }
    return $this->storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return static::getDefaultFieldSettings() + parent::defaultFieldSettings();
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

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    $value_computed = $this->get('value_computed')->getValue();
    return empty($value) && empty($value_computed);

  }

  /**
   * {@inheritdoc}
   */
  public static function fieldSettingsToConfigData(array $settings) {
    return $settings;
  }
}
