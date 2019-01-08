<?php

namespace Drupal\etree_views\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Filter handler for etree parent.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("etree_parent_id")
 */
class ETreeParentId extends FilterPluginBase {


  public function query() {
    $this->ensureMyTable();
    if (count($this->value) == 0) {
      return;
    }
    elseif (count($this->value) == 1) {
      // Sometimes $this->value is an array with a single element so convert it.
      if (is_array($this->value)) {
        $this->value = current($this->value);
      }
      $operator = '=';
    }
    else {
      $operator = 'IN';
    }
    $this->query->addWhere('AND', "$this->tableAlias.parent_id_0", $this->value);
  }

  protected function valueForm(&$form, FormStateInterface $form_state) {


    $form['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Parent ID'),
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
