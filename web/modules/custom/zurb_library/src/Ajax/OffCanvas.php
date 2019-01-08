<?php

namespace Drupal\zurb_library\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Class OffCanvasOpen.
 */
class OffCanvas implements CommandInterface {


  private $action;

  private $selector;

  public function __construct($action, $selector) {
    $this->action = $action;
    $this->selector = $selector;
  }

  /**
   * Render custom ajax command.
   *
   * @return []
   *   Command function.
   */
  public function render() {
    return [
      'command' => 'ZurbFoundationOffCanvas',
      'action' => $this->action,
      'selector' => $this->selector,
    ];
  }

}
