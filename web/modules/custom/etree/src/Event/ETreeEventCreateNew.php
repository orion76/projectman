<?php


namespace Drupal\etree\Event;


use Symfony\Component\EventDispatcher\Event;

class ETreeEventCreateNew extends Event {

  /**
   * Called during hook_preprocess_html().
   */
  const INIT_FROM_ROUTE = 'etree.init_from_route';

  const AFTER_INIT_FROM_ROUTE = 'etree.after.init_from_route';

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


}