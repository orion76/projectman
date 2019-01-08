<?php

namespace Drupal\zurb_off_canvas\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

abstract class OffCanvasButtonBase extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['button_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Toggle button title'),
      '#default_value' => $this->configuration['button_title'],

    ];
    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['button_title'] = $form_state->getValue('button_title');

  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    // By default, the block will contain 10 feed items.
    return [
      'button_title' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#type' => 'html_tag',
      '#tag' => 'button',
      '#attached' => [
        'library' => [
//          'zurb_library/core',
          'zurb_library/offcanvas',
        ],
      ],
      '#attributes' => [
        'class' => ['button'],
        'type' => 'button',
        'data-toggle' => static::getContainerId(),
      ],
      '#value' => $this->configuration['button_title'],
    ];


    return [$this->pluginId => $build];
  }

  static function getContainerId() {
    return 'none';
  }

}
