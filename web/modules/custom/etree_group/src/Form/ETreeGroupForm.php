<?php

namespace Drupal\etree_group\Form;

use Drupal;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\etree_group\Entity\ETreeGroupInterface;
use Drupal\views\ViewEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function array_filter;
use function array_intersect_key;


/**
 * Class ETreeGroupForm.
 *
 * @property EntityTypeManager $entityTypeManager
 */
class ETreeGroupForm extends EntityForm {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $config_factory;

  /** @var \Drupal\etree_group\EtreeGroupHelper $groupHelper */
  private $groupHelper;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Drupal\etree_group\EtreeGroupHelper $groupHelper) {
    $this->config_factory = $config_factory;
    $this->groupHelper = $groupHelper;
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('etree_group.helper')
    );
  }

  /**
   * @param $id
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  protected static function getStorage($id) {
    return Drupal::service('entity_type.manager')->getStorage($id);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'etree.etree_group',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'etree_group';
  }

  protected function optionsBundles() {
    $storage = static::getStorage('etree_type');
    $options = [];
    foreach ($storage->loadMultiple() as $entity) {
      $options[$entity->id()] = $entity->label();
    }
    return $options;
  }

  protected function getDefaultValues() {
    return [
      'label' => '',
      'id' => '',
      'etree_types' => [],
      'child_types' => [],
      'collection_views' => [],
    ];
  }

  protected function getValues(ETreeGroupInterface $group, FormStateInterface $form_state) {
    $default_values = $this->getDefaultValues();
    if ($form_state->isProcessingInput()) {
      $values = $form_state->getValues();
    }
    else {
      $values = $this->entity->toArray();
    }
    if ($group->id()) {
      $values['id'] = $group->id();
    }

    $values = array_filter(array_intersect_key($values, $default_values));

    return $values + $default_values;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    //    $views=$this->groupHelper->getGroupViews();
/** @var ETreeGroupInterface $group   */
    $group = $this->entity;
    $values = $this->getValues($group, $form_state);

    /** @var \Drupal\Core\Config\Config $config */

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $values['label'],
      '#required' => TRUE,
      '#weight' => -30,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#required' => TRUE,
      '#default_value' => $values['id'],
      '#maxlength' => 255,
      '#machine_name' => [
        'exists' => [$this, 'existsId'],
        'source' => ['label'],
      ],
      '#disabled' => !$group->isNew(),
      '#weight' => -20,
    ];


    $form['etree_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Types for tree'),
      '#options' => $this->optionsBundles(),
      '#default_value' => $values['etree_types'],
      '#description' => $this->t('Allowed ETree bundles for tree.'),
    ];

    $form['child_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Types as child'),
      '#options' => $this->optionsBundles(),
      '#default_value' => $values['child_types'],
      '#description' => $this->t('Allowed ETree bundles as child.'),
    ];

    $container_id = Html::getId('views-collection');
    $this->addFormCollectionViews($form, $form_state, $container_id, $values['collection_views']);

    return parent::buildForm($form, $form_state);
  }

  protected function addFormCollectionViews(array &$form, FormStateInterface $form_state, $container_id, $collection_views) {

    $elements = [
      '#type' => 'details',
      '#attributes' => ['id' => $container_id],
      '#open' => TRUE,
      '#title' => t('Collections views'),
      '#tree' => TRUE,
    ];

    foreach (array_keys($collection_views) as $delta) {

      $elements[$delta] = $this->buildFormCollectionViewsItem($form_state, $container_id, $delta);
    }


    $form['collection_views'] = $elements;
    $form['add_view'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add'),
      //      '#submit' => ['::ajaxSubmit'],
      '#ajax' => [
        //        'trigger_as' => ['name' => 'add_view'],
        'wrapper' => $container_id,
        'callback' => '::ajaxAddView',
      ],
    ];

  }

  public function ajaxShowViewsDisplaySelect(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $parents = $trigger['#parents'];
    array_pop($parents);
    return NestedArray::getValue($form, $parents);
  }

  public function ajaxAddView(array &$form, FormStateInterface $form_state) {
    return $form['collection_views'];
  }

  public static function submitDeleteView(array &$form, FormStateInterface $form_state) {
    $action = static::getAjaxAction($form_state);

    $form_state->setRebuild();
  }

  public static function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $action = static::getAjaxAction($form_state);
    switch ($action['name']) {
      case 'add_view':
        $input = $form_state->getValues();
        if (!isset($input['collection_views'])) {
          $input['collection_views'] = [];
        }
        $input['collection_views'][] = ['view_id' => '', 'display_id' => ''];
        $form_state->setValues($input);
        break;
    }

    $form_state->setRebuild();
  }

  protected static function getAjaxAction(FormStateInterface $form_state) {

    $trigger = $form_state->getTriggeringElement();
    $parents = $trigger['#parents'];
    $data = [
      'action' => array_pop($parents),
      'path' => $parents,
    ];
    return $data;
  }

  protected function defaultViews() {
    return ['label' => '', 'id' => '', 'view_id' => '', 'display_id'];
  }

  protected function buildFormCollectionViewsItem(FormStateInterface $form_state, $container_id, $delta) {
    $group = $this->entity;
    $group_id = $group->id() ? $this->entity->id() : NULL;
    $values = $this->getValues($group, $form_state);

    $values_item = (isset($values['collection_views'][$delta]) ? $values['collection_views'][$delta] : []) + $this->defaultViews();

    $id = Html::getId("collection-view-item-{$delta}");
    $element = [
      '#attributes' => ['id' => $id, 'class' => ['form-row']],
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => 'Views',
      '#tree' => TRUE,
    ];

    $element['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $values_item['label'],
      '#required' => TRUE,
      '#weight' => -30,
    ];
    $element['id'] = [
      '#type' => 'machine_name',
      '#required' => TRUE,
      '#default_value' => $values_item['id'],
      '#maxlength' => 255,
      '#disabled' => FALSE,
      '#machine_name' => [
        'exists' => [$this, 'existsViewsId'],
        'source' => ['collection_views', $delta, 'label'],
      ],
      '#weight' => -20,
    ];


    $views_options = $this->optionsViews($group_id, $values['collection_views'], (boolean) $values_item['view_id']);
    $element['view_id'] = [
      '#type' => 'select',
      '#title' => t('View'),
      '#default_value' => $values_item['view_id'],
      '#options' => $views_options,
      '#title_display' => 'before',
      '#ajax' => [
        //        'trigger_as' => ['name' => 'add_display'],
        'callback' => '::ajaxShowViewsDisplaySelect',
        'wrapper' => $id,
      ],
      '#required' => TRUE,
    ];

    if ($values_item['view_id']) {
      $element['display_id'] = [
        '#type' => 'select',
        '#title' => t('Display'),
        '#default_value' => $values_item['display_id'],
        '#options' => $this->optionsViewDisplays($values_item['view_id'], $group_id, $values['collection_views']),
        '#title_display' => 'before',
        '#required' => TRUE,
      ];
    }

    $element['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#ajax' => [
        'wrapper' => $container_id,
        'callback' => '::ajaxAddView',
      ],
      '#prefix' => '<div class="form-item">',
      '#suffix' => '</div>',
    ];
    return $element;
  }

  protected static function filterByDisplay(ViewEntityInterface $view) {
    foreach ($view->get('display') as $display) {
      if ($display['display_plugin'] === 'etree') {
        return TRUE;
      }
    }
  }

  protected static function filterDisplayETree($display) {
    return $display['display_plugin'] === 'etree';
  }

  protected static function optionsViewsExists($self_group_id, $self_views, $include = TRUE) {
    $groups = Drupal::service('entity_type.manager')->getStorage('etree_group')->loadMultiple();
    if (!$groups) {
      return [];
    }

    $views_all = [];

    if (isset($self_views)) {
      $views_all[$self_group_id] = $self_views;
    }

    foreach ($groups as $group) {
      /** @var ETreeGroupInterface $group */
      /** @var ETreeGroupInterface $group */
      $views = $group->get('collection_views');
      if (!$views) {
        continue;
      }
      $views_all[$group->id()] = $views;
    }

    $exists = [];
    foreach ($views_all as $group_id => $views) {

      if ($include && $group_id === $self_group_id) {
        continue;
      }

      foreach ($views as $view) {
        $view_id = $view['view_id'];
        if (!isset($exists[$view_id])) {
          $exists[$view_id] = ['displays' => []];
        }
        $display_id = $view['display_id'];
        $exists[$view_id]['displays'][$display_id] = TRUE;
      }
    }
    return $exists;
  }

  protected static function prepareView(ViewEntityInterface $view) {
    $displays = $view->get('display');
    if (!$displays) {
      return FALSE;
    }

    $etree_displays = [];

    foreach ($displays as $display_id => $display) {
      if ($display['display_plugin'] !== 'etree') {
        continue;
      }
      $etree_displays[$display_id] = $display['display_title'];
    }
    return count($etree_displays) ? [
      'id' => $view->id(),
      'label' => $view->label(),
      'displays' => $etree_displays,
    ] : FALSE;
  }

  protected static function getViews($self_group_id, $self_views, $include = TRUE) {


    $views_exists = static::optionsViewsExists($self_group_id, $self_views, $include);
    $views = array_filter(array_map([static::class, 'prepareView'], static::getStorage('view')
      ->loadByProperties(['base_table' => 'etree_field_data'])));

    foreach (array_keys($views) as $view_id) {
      if (!isset($views_exists[$view_id])) {
        continue;
      }
      $view_exists = $views_exists[$view_id];
      $view = $views[$view_id];
      $view['displays'] = array_diff_key($view['displays'], $view_exists['displays']);
      if (empty($view['displays'])) {
        unset($views[$view_id]);
        continue;
      }
    }
    return $views;
  }

  protected static function optionsViews($self_group_id, $self_views, $include = TRUE) {
    $options = ['' => t('-- Select --')];
    return $options + array_column(self::getViews($self_group_id, $self_views, $include), 'label', 'id');
  }

  protected static function optionsViewDisplays($view_id, $self_group_id, $self_views) {

    $options = ['' => t('-- Select --')];
    /** @var ViewEntityInterface $view */
    $views = static::getViews($self_group_id, $self_views);

    if (isset($views[$view_id])) {
      $options += $views[$view_id]['displays'];
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    //    $this->createViews();
    $n = 0;
  }

  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {

    $values = &$form_state->getValues();
    if (!isset($values['collection_views'])) {
      $values['collection_views'] = [];
    }
    foreach (array_keys($values['collection_views']) as $delta) {
      if (isset($values['collection_views'][$delta]['delete'])) {
        unset($values['collection_views'][$delta]['delete']);
      }
    }
    parent::copyFormValuesToEntity($entity, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $action = self::getAjaxAction($form_state);
    $rebuild = FALSE;
    switch ($action['action']) {
      case 'add_view':
        $values = $form_state->getUserInput();
        if (!isset($values['collection_views'])) {
          $values['collection_views'] = [];
        }
        $values['collection_views'][] = $this->defaultViews();
        $form_state->setValues($values);
        $rebuild = TRUE;
        break;
      case 'view_id':
        $rebuild = TRUE;
        break;
      case 'delete':
        $form_state->clearErrors();
        $form_state->unsetValue($action['path']);
        $rebuild = TRUE;
        break;
    }

    $form_state->setRebuild($rebuild);
  }

  public function existsId($id) {
    return (boolean) $this->entityTypeManager->getStorage('etree_group')->load($id);

  }

  public function existsViewsId($id, $element, $form_state) {
    $group = $this->entity;
    $delta = $element['#parents'][1];

    /** @var ETreeGroupInterface $group */
    $values = array_filter($group->getCollectionViews(), function ($key) use ($delta) {
      return $key !== $delta;
    }, ARRAY_FILTER_USE_KEY);

    return (boolean) array_filter(array_column($values, 'id'), function ($exists) use ($id) {
      return $exists === $id;
    });

  }
}
