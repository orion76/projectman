<?php

namespace Drupal\etree_time_tracker\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Time tracker entity edit forms.
 *
 * @ingroup etree_time_tracker
 */
class TimeTrackerEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\etree_time_tracker\Entity\TimeTrackerEntity */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Time tracker entity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Time tracker entity.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.time_tracker_entity.canonical', ['time_tracker_entity' => $entity->id()]);
  }

}
