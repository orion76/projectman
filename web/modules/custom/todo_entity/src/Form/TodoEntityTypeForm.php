<?php

namespace Drupal\todo_entity\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TodoEntityTypeForm.
 */
class TodoEntityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $todo_entity_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $todo_entity_type->label(),
      '#description' => $this->t("Label for the Todo entity type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $todo_entity_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\todo_entity\Entity\TodoEntityType::load',
      ],
      '#disabled' => !$todo_entity_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $todo_entity_type = $this->entity;
    $status = $todo_entity_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Todo entity type.', [
          '%label' => $todo_entity_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Todo entity type.', [
          '%label' => $todo_entity_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($todo_entity_type->toUrl('collection'));
  }

}
