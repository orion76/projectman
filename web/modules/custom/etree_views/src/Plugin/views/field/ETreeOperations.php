<?php

namespace Drupal\etree_views\Plugin\views\field;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
 * @property EntityStorageInterface groupStorage
 *
 * @ViewsField("etree_operations")
 */
class ETreeOperations extends FieldPluginBase {

  protected $routeProvider;

  protected $entityTypeManager;

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
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              RouteProvider $routeProvider,
                              EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeProvider = $routeProvider;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('router.route_provider'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['route'] = ['default' => ''];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {

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
    /** @var \Drupal\etree\Entity\ETreeInterface $entity */
    $entity = $values->_entity;

    $operation_default = [
      '#icon_only' => TRUE,
    ];

    $operations[] = [
        '#name' => 'canonical',
        '#icon' => '<i class="fas fa-eye"></i>',
        '#description' => $this->t('View'),
        '#url' => $entity->toUrl('canonical'),
        '#style' => 'view',
      ] + $operation_default;

    $operations[] = [
        '#name' => 'group-edit-form',
        '#icon' => '<i class="fas fa-edit"></i>',
        '#description' => $this->t('Edit'),
        '#url' => $entity->toUrl('edit-form'),
        '#style' => 'edit',
      ] + $operation_default;

    /** @var \Drupal\etree_group\Entity\ETreeGroup $group */


    $groupStorage = $this->entityTypeManager()->getStorage('etree_group');
    $typeStorage = $this->entityTypeManager()->getStorage('etree_type');

    $group = $groupStorage->load($entity->getGroupId());

    foreach ($group->getTreeTypes() as $bundle_name) {
      $bundle = $typeStorage->load($bundle_name);

      $destination = \Drupal::destination()->getAsArray();

      $url = $entity->toUrl('add-form', ['query' => ['parent_id' => $entity->id()] + $destination]);
      $url->setRouteParameter('etree_type', $bundle_name);

      $operations[] = [
          '#name' => 'add-form',
          '#icon' => '<i class="fas fa-plus"></i>',
          '#description' => $this->t('Add child @bundle', ['@bundle' => $bundle->label()]),
          '#url' => $url,
          '#style' => 'add',
        ] + $operation_default;

    }

    $button = ['#type' => 'icon_buttons', '#links' => $operations];

    return render($button);
  }

  /**
   * Gets the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected function entityTypeManager() {
    return \Drupal::entityTypeManager();
  }

  /**
   * {@inheritdoc}
   */
  public function usesOptions() {
    return TRUE;
  }
}
