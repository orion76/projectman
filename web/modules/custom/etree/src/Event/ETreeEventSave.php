<?php


namespace Drupal\etree\Event;


use Symfony\Component\EventDispatcher\Event;

class ETreeEventSave extends Event {

  /**
   * Called during hook_preprocess_html().
   */
  const POST_SAVE = 'etree.post_save';

  const POST_DELETE = 'etree.post_delete';


  /**
   * @var \Drupal\etree\Entity\ETreeInterface
   */
  private $values;

  /**
   * DummyFrontpageEvent constructor.
   */
  public function __construct(&$values) {

    $this->values = &$values;
  }

  /**
   * Returns variables array from preprocess.
   */
  public function &getValues() {
    return $this->values;
  }

  public static function dispatch($event_id, $values) {
    $dispatcher = \Drupal::service('event_dispatcher');
    $dispatcher->dispatch($event_id, new static($values));
  }

}