<?php


namespace Drupal\etree_computed\Event;


use Drupal;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\etree\Entity\ETreeInterface;
use Drupal\field\FieldStorageConfigInterface;
use Symfony\Component\EventDispatcher\Event;
use function is_null;

class ComputedEventFieldsChange extends Event {

  /**
   * Called during hook_preprocess_html().
   */
  const FIELDS_CHANGED = 'etree_computed.fields_changed';


  /**
   * @var \Drupal\etree\Entity\ETreeInterface
   */
  private $values;


  public function __construct(&$values) {

    $this->values = &$values;
  }

  /**
   * Returns variables array from preprocess.
   */
  public function &getValues() {
    return $this->values;
  }

  public static function dispatch($event_id, &$entity, &$fields) {
    $dispatcher = \Drupal::service('event_dispatcher');
    $values = ['entity' => &$entity, 'fields' => &$fields];
    $dispatcher->dispatch($event_id, new static($values));
  }

  public static function getChangedFields(ETreeInterface $original = NULL, ETreeInterface $entity) {
    $fields = [];
    $computed = static::getComputedFieldsDefinitions($entity);

    if (is_null($original)) {
      foreach ($computed as $field_name => $definition) {
        /** @var \Drupal\Core\Field\FieldItemInterface $field */
        $field = $entity->{$field_name};
        if ($field->isEmpty() === FALSE) {
          $fields[$field_name] = $definition;
        }

      }
    }
    else {
      foreach ($computed as $field_name => $definition) {
        if (!isset($original->{$field_name}) && !isset($entity->{$field_name})) {
          continue;
        }

        if (static::changedFields($field_name, $original, $entity, $definition)) {
          $fields[$field_name] = $definition;
        }

      }
    }

    return $fields;
  }


  protected static function changedInteger($field_name, ETreeInterface $original, ETreeInterface $entity) {
    foreach (['value', 'value_computed'] as $property) {
      $original_value = (integer) $original->{$field_name}->{$property};
      $entity_value = (integer) $entity->{$field_name}->{$property};
      if ($original_value !== $entity_value) {
        return TRUE;
      }
    }
    return FALSE;
  }

  protected static function changedFields($field_name, ETreeInterface $original, ETreeInterface $entity, FieldConfigInterface $definition) {
    /** @var FieldStorageConfigInterface $storage */
    $storage = $definition->getFieldStorageDefinition();
    $value_type = $storage->getPropertyDefinition('value')->getDataType();
    $changed = FALSE;
    switch ($value_type) {
      case 'integer':
        $changed = static::changedInteger($field_name, $original, $entity);
        break;
      default:
        Drupal::messenger()->addWarning(t('Property type not handled: @type', ['@type' => $value_type]));
        return FALSE;
    }
    return $changed;
  }

  static function filterComputed(FieldDefinitionInterface $definition) {
    return strpos($definition->getType(), 'etree_computed') === 0;
  }

  static function getComputedFieldsDefinitions(ETreeInterface $entity) {
    $definitions = $entity->getFieldDefinitions();
    return array_filter($definitions,
      function ($definition) {
        return strpos($definition->getType(), 'etree_computed') === 0;
      });
  }
}