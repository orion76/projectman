<?php

namespace Drupal\etree_views\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteProvider;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handler for showing etree_item_path.
 *
 * @ingroup views_field_handlers
 * @property \Drupal\Core\Routing\RouteProvider routeProvider
 *
 * @ViewsField("etree_link_collection_preview")
 */
class ETreeLinkCollectionPreview extends FieldPluginBase {

  protected $routeProvider;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteProvider $routeProvider) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeProvider = $routeProvider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('router.route_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['route'] = [
      'default' => '',
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {

  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    parent::buildOptionsForm($form, $form_state);


    $form['route'] = [
      '#type' => 'details',
      '#title' => $this->t('Route'),
      '#tree' => TRUE,
      '#open' => TRUE,
    ];
    $form['route']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('name'),
      '#descriptions' => $this->t('id: "#id-selector", class:".class-selector"'),
      '#default_value' => $this->options['route']['name'],
    ];


  }

  public function link($title, $id) {


    $route_name = $this->options['route']['name'];
    $route = $this->routeProvider->getRouteByName($route_name);
    $variables = $route->compile()->getPathVariables();

    $url = Url::fromRoute($route_name, [$variables[0] => $id]);

    $link = [
      '#type' => 'link',
      '#url' => $url,
      '#title' => $title,
      '#attributes' => [
        'class' => ['use-ajax', 'load-content'],
      ],
    ];
    return render($link);
  }

  /**
   * TODO Релизовать генерацию пути по дркгим полям сущности (КОД и т.д.)
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    return $this->link($values->_entity->label(), $values->_entity->id());
  }

  /**
   * {@inheritdoc}
   */
  public function usesOptions() {
    return TRUE;
  }
}
