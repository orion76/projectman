<?php

namespace Drupal\etree_computed;


use Drupal\Core\Form\FormStateInterface;
use function array_diff;

trait ComputedFieldTypeTrait {


  protected $entityTypeManager;

  protected function getEntityTypedManager() {
    if (empty($this->entityTypeManager)) {
      $this->entityTypeManager = \Drupal::entityTypeManager();
    }

    return $this->entityTypeManager;
  }


  protected function groupStorage() {
    return $this->getEntityTypedManager()->getStorage('etree_group');
  }

  protected function typeStorage() {
    return $this->getEntityTypedManager()->getStorage('etree_type');
  }

  protected function optionsBundles($names) {
    $bundles = $this->typeStorage()->loadMultiple($names);
    $options = [];
    foreach ($bundles as $bundle) {
      $options[$bundle->id()] = $bundle->label();
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefaultFieldSettings() {
    return [
      'input_self' => TRUE,
      'bundles' => [],
      'operation' => '',
    ];
  }

  public function addFieldSettingsForm(&$element, array $form, FormStateInterface $form_state) {
    $allowed_bundles = [];
    foreach ($this->groupStorage()->loadMultiple() as $group_id => $group) {
      /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
      $new_bundles = array_diff($group->getAllowedTypes(), $allowed_bundles);
      $allowed_bundles = array_merge($allowed_bundles, $new_bundles);
    }
    $options_bundles = $this->optionsBundles($allowed_bundles);

    $element['input_self'] = [
      '#type' => 'checkbox',
      '#open' => TRUE,
      '#title' => t('Input self'),
      '#default_value' => $this->getSetting('input_self'),
    ];


    $element['bundles'] = [
      '#type' => 'checkboxes',
      '#title' => t('Bundles'),
      '#options' => $options_bundles,
      '#default_value' => $this->getSetting('bundles'),
    ];

    $element['operation'] = [
      '#type' => 'select',
      '#title' => t('Operation'),
      '#default_value' => $this->getSetting('operation'),
      '#options' => ChildCalculateOperations::getOptions(),
    ];

    return $element;
  }
}
