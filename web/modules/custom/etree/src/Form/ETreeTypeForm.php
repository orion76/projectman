<?php

namespace Drupal\etree\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\etree\Entity\ETreeTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Class ETreeTypeForm.
 */
class ETreeTypeForm extends EntityForm {

  /**
   * ETreeTypeForm constructor.
   *
   * @param $routeProvider
   */
  public function __construct() {

  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var ETreeTypeInterface $etree_type */
    $etree_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $etree_type->label(),
      '#description' => $this->t("Label for the ETree type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $etree_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\etree\Entity\ETreeType::load',
      ],
      '#disabled' => !$etree_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */



    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $etree_type = $this->entity;
    $status = $etree_type->save();


    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label ETree type.', [
          '%label' => $etree_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label ETree type.', [
          '%label' => $etree_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($etree_type->toUrl('collection'));
  }

}
