<?php

namespace Drupal\zurb_off_canvas\Plugin\Block;

/**
 * Provides a 'OffCanvasButton' block.
 *
 * @Block(
 *  id = "zurb_off_canvas_button_left",
 *  admin_label = @Translation("Zurb Off-canvas button Left"),
 * )
 */
class OffCanvasButtonLeft extends OffCanvasButtonBase {

  static function getContainerId() {
    return 'offCanvasLeft';
  }
}
