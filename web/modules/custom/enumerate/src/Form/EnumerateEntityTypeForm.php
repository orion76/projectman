<?php

namespace Drupal\enumerate\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EnumerateEntityTypeForm.
 */
class EnumerateEntityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $enumerate_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $enumerate_type->label(),
      '#description' => $this->t("Label for the Enumerate type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $enumerate_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\enumerate\Entity\EnumerateEntityType::load',
      ],
      '#disabled' => !$enumerate_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $enumerate_type = $this->entity;
    $status = $enumerate_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Enumerate type.', [
          '%label' => $enumerate_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Enumerate type.', [
          '%label' => $enumerate_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($enumerate_type->toUrl('collection'));
  }

}
