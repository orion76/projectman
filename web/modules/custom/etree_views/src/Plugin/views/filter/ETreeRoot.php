<?php

namespace Drupal\etree_views\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Filter handler for etree parent.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("etree_root")
 */
class ETreeRoot extends FilterPluginBase {


  public function query() {
    $this->ensureMyTable();
    if (count($this->value) == 0) {
      return;
    }
    elseif (count($this->value) == 1) {
      if (is_array($this->value)) {
        $this->value = current($this->value);
      }

    }
    $this->query->addWhere('AND', "$this->tableAlias.$this->realField", $this->value);
  }

  protected function valueForm(&$form, FormStateInterface $form_state) {


    $form['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Root ID'),
      '#default_value' => $this->value,
    ];

    $identifier = $this->options['expose']['identifier'];

    $user_input = $form_state->getUserInput();
    if ($form_state->get('exposed') && isset($identifier) && !isset($user_input[$identifier])) {
      $user_input[$identifier] = $this->value;
      $form_state->setUserInput($user_input);
    }


  }

}
