<?php

namespace Drupal\etree\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\etree\Entity\ETreeInterface;
use Drupal\etree\Event\ETreeEventCreateNew;


/**
 * Form controller for ETree edit forms.
 *
 * @ingroup etree
 *
 * @property ETreeInterface $entity
 */
class ETreeForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function getEntityFromRouteMatch(RouteMatchInterface $route_match, $entity_type_id) {
    if ($route_match->getRawParameter($entity_type_id) !== NULL) {
      $entity = $route_match->getParameter($entity_type_id);
    }
    else {
      $values = [];
      // If the entity has bundles, fetch it from the route match.
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      if ($bundle_key = $entity_type->getKey('bundle')) {
        if (($bundle_entity_type_id = $entity_type->getBundleEntityType()) && $route_match->getRawParameter($bundle_entity_type_id)) {
          $values[$bundle_key] = $route_match->getParameter($bundle_entity_type_id)->id();
        }
        elseif ($route_match->getRawParameter($bundle_key)) {
          $values[$bundle_key] = $route_match->getParameter($bundle_key);
        }
      }

      if ($this->isAddFormRoute($route_match->getRouteName())) {
        $this->setNewEntityValuesFromRequest($values, $route_match, $entity_type_id);
      }

      $entity = $this->entityTypeManager->getStorage($entity_type_id)->create($values);
    }

    return $entity;
  }

  protected function isAddFormRoute($route_name) {
    return strrchr($route_name, '.') === '.add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\etree\Entity\ETree */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    $form['links'] = $entity->buildLinks('');
    //    if (!$this->entity->isNew()) {
    //      $form['new_revision'] = [
    //        '#type' => 'checkbox',
    //        '#title' => $this->t('Create new revision'),
    //        '#default_value' => FALSE,
    //        '#weight' => 10,
    //      ];
    //    }


    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime(REQUEST_TIME);
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label ETree.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label ETree.', [
          '%label' => $entity->label(),
        ]));
    }

  }

  private function setNewEntityValuesFromRequest(&$values, $route_match, $entity_type_id) {

    $dispatcher = \Drupal::service('event_dispatcher');
    $event = new ETreeEventCreateNew($values);
    $dispatcher->dispatch(ETreeEventCreateNew::INIT_FROM_ROUTE, $event);

  }
}
