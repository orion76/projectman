<?php


namespace Drupal\projectman\EventSubscriber;


use Drupal\etree\Event\ETreeEventCreateNew;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectmanEventSubscriber implements EventSubscriberInterface {

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
      ETreeEventCreateNew::AFTER_INIT_FROM_ROUTE => ['afterInitFromRoute'],

    ];
  }

  /**
   * Example for DummyFrontpageEvent::PREPROCESS_HTML.
   */
  public function afterInitFromRoute(ETreeEventCreateNew $event) {
    /** @var \Drupal\Core\Messenger\MessengerInterface $messenger */
    $values = &$event->getValues();

    $this->populateTitle($values);

  }

  /**
   * @param $values
   */
  protected function populateTitle(&$values) {
    if (isset($values['type']) && $values['type'] === 'pm_task') {
      if (isset($values['etree_parent'])) {
        /** @var \Drupal\etree\Entity\ETreeInterface $parent */
        $parent = $values['etree_parent'];
        $values['name'] = $parent->label();
      }

    }
  }

}