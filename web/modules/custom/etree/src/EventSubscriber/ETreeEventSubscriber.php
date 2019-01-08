<?php


namespace Drupal\etree\EventSubscriber;


use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Utility\Html;
use Drupal\etree\Entity\ETree;
use Drupal\etree\Event\ETreeEventCreateNew;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ETreeEventSubscriber implements EventSubscriberInterface {

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
      ETreeEventCreateNew::INIT_FROM_ROUTE => ['afterInitFromRoute'],

    ];
  }


  /**
   * Example for DummyFrontpageEvent::PREPROCESS_HTML.
   */
  public function afterInitFromRoute(ETreeEventCreateNew $event) {
    /** @var \Drupal\Core\Messenger\MessengerInterface $messenger */
    $values = &$event->getValues();

    $this->addGroup($values);
    $this->addParent($values);

    $dispatcher = \Drupal::service('event_dispatcher');
    $event = new ETreeEventCreateNew($values);
    $dispatcher->dispatch(ETreeEventCreateNew::AFTER_INIT_FROM_ROUTE, $event);

  }

  protected function addParent(&$values) {
    if (\Drupal::request()->query->has('parent_id')) {
      $parent_id = Html::escape(\Drupal::request()->query->get('parent_id'));
      /** @var ETree $parent */
      try {
        $parent = \Drupal::entityTypeManager()->getStorage('etree')->load($parent_id);
      } catch (InvalidPluginDefinitionException $e) {
      } catch (PluginNotFoundException $e) {
      }
      if ($parent) {
        $values['etree_parent'] = $parent;
        $values['etree_group'] = $parent->get('etree_group');
      }
    }
  }

  protected function addGroup(&$values) {
    if (\Drupal::request()->query->has('group_id')) {
      $group_id = Html::escape(\Drupal::request()->query->get('group_id'));
      $values['etree_group'] = $group_id;
    }
  }

}