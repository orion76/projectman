<?php


namespace Drupal\etree_computed\EventSubscriber;


use Drupal\etree\Event\ETreeEventSave;
use Drupal\etree_computed\Event\ComputedEventFieldsChange;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ComputedEventSubscriber implements EventSubscriberInterface {

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * The array keys are event names and the value can be:
   *
   *  * The method name to call (priority defaults to 0)
   *  * An array composed of the method name to call and the priority
   *  * An array of arrays composed of the method names to call and respective
   *    priorities, or 0 if unset
   *
   * For instance:
   *
   *  * array('eventName' => 'methodName')
   *  * array('eventName' => array('methodName', $priority))
   *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
   *
   * @return array The event names to listen to
   */

  public static function getSubscribedEvents() {
    return [
      ETreeEventSave::POST_SAVE => ['postSave'],
      ETreeEventSave::POST_DELETE => ['postDelete'],
      ComputedEventFieldsChange::FIELDS_CHANGED => ['fieldsChanged'],
    ];
  }

  /**
   * Example for DummyFrontpageEvent::PREPROCESS_HTML.
   */
  public function postSave(ETreeEventSave $event) {
    /** @var \Drupal\etree\Entity\ETreeInterface $entity */
    $entity = &$event->getValues();

    /** @var \Drupal\etree\Entity\ETreeInterface $original */
    $original = $entity->original;

    if ($fields = ComputedEventFieldsChange::getChangedFields($original, $entity)) {
      ComputedEventFieldsChange::dispatch(ComputedEventFieldsChange::FIELDS_CHANGED, $entity, $fields);
    }

  }

  /**
   * Example for DummyFrontpageEvent::PREPROCESS_HTML.
   */
  public function postDelete(ETreeEventSave $event) {
    /** @var \Drupal\etree\Entity\ETreeInterface $entity */
    $entity = &$event->getValues();

    if ($fields = ComputedEventFieldsChange::getComputedFieldsDefinitions($entity)) {
      ComputedEventFieldsChange::dispatch(ComputedEventFieldsChange::FIELDS_CHANGED, $entity, $fields);
    }

  }


  public function fieldsChanged(ComputedEventFieldsChange $event) {
    $values = &$event->getValues();
    /** @var \Drupal\etree\Entity\ETreeInterface $entity */
    $entity = $values['entity'];
    if (!empty($entity) && $parent = $entity->getParent()) {
      /** @var \Drupal\etree\Entity\ETreeInterface $parent */
      foreach (array_keys($values['fields']) as $field_name) {
        if ($fieldList = $parent->get($field_name)) {
          /** @var \Drupal\Core\Field\FieldItemListInterface $fieldList */

          /** @var \Drupal\etree_computed\Plugin\Field\FieldType\FieldTypeComputedInterface $field */
          $field = $fieldList->get(0);
          if (!$field) {
            $parent->set($field_name, '');
            $field = $parent->get($field_name)->get(0);
          }
          $field->calculate();
          $parent->save();
        }
      }
    }

  }

  public function fieldsDeleted(ComputedEventFieldsChange $event) {
    $values = &$event->getValues();
    /** @var \Drupal\etree\Entity\ETreeInterface $entity */
    $entity = $values['entity'];
    if (!empty($entity) && $parent = $entity->getParent()) {
      /** @var \Drupal\etree\Entity\ETreeInterface $parent */
      foreach (array_keys($values['fields']) as $field_name) {
        if ($fieldList = $parent->get($field_name)) {
          /** @var \Drupal\Core\Field\FieldItemListInterface $fieldList */

          /** @var \Drupal\etree_computed\Plugin\Field\FieldType\FieldTypeComputedInterface $field */
          $field = $fieldList->get(0);
          if (!$field) {
            $parent->set($field_name, '');
            $field = $parent->get($field_name)->get(0);
          }
          $field->calculate();
          $parent->save();
        }
      }
    }

  }

}