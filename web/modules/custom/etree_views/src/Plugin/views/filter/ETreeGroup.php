<?php

namespace Drupal\etree_views\Plugin\views\filter;

use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter by published status.
 *
 * @property ConfigEntityStorage $storageGroup
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("etree_group")
 */
class ETreeGroup extends FilterPluginBase {

  protected $storageGroup;

  /**
   * Constructs a PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigEntityStorage $storageGroup) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->storageGroup = $storageGroup;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('etree_group'));
  }


  public function query() {
    $this->ensureMyTable();

    $this->query->addWhere('AND', "{$this->tableAlias}.{$this->realField}", $this->value);

  }


  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();

    $contexts[] = 'user';

    return $contexts;
  }

  protected function optionsGroup() {
    $options = [];
    foreach ($this->storageGroup->loadMultiple() as $group) {
      $options[$group->id()] = $group->label();
    }
    return $options;
  }

  protected function valueForm(&$form, FormStateInterface $form_state) {


    $form['value'] = [
      '#type' => 'select',
      '#title' => $this->t('Group'),
      '#options' => $this->optionsGroup(),
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
