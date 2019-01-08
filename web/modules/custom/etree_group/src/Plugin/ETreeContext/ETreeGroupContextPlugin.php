<?php


namespace Drupal\etree_group\Plugin\ETreeContext;


use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\etree\Entity\ETreeInterface;
use Drupal\etree\ETreeCommon;
use Drupal\etree_context\Context\ETreeRouteContextInterface;
use Drupal\etree_context\Plugin\ETreeContextPluginInterface;
use Drupal\etree_context\Plugin\ETreeContextPluginBase;
use Drupal\etree_context\Plugin\ETreeContextPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ETreeGroupContextPlugin
 *
 * @ETreeContext(
 *   id = "etree_group",
 *   label = @Translation("ETree Group")
 * )
 *
 * @package Drupal\etree_group
 */
class ETreeGroupContextPlugin extends ETreeContextPluginBase implements ETreeContextPluginInterface {

  use StringTranslationTrait;

  protected $routeParts;

  /**
   * @var \Drupal\etree_context\Context\ETreeRouteContextInterface
   */
  private $contextService;


  /**
   * Constructs a new GroupRouteContext.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match object.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ETreeRouteContextInterface $contextService,
                              RouteMatchInterface $current_route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentRouteMatch = $current_route_match;
    $this->contextService = $contextService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('etree_context.context_route'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {

    $context = [];

    foreach ($unqualified_context_ids as $name) {
      switch ($name) {
        case 'etree_group':

          /** @var \Drupal\Core\Plugin\Context\ContextInterface[] $context */
          $etree = $this->contextService->getRuntimeContext('etree');
          $context[$name] = $this->createContext('entity:etree_group', $this->getETreeGroupFromRoute($etree));
          break;
        case 'etree_action':
          $context[$name] = $this->createContext('string', str_replace('_', '-', $this->getETreeActionFromRoute()));
          break;
        case 'etree_group_view':
          $context[$name] = $this->createContext('string', str_replace('_', '-', $this->getETreeGroupViewFromRoute()));
          break;
      }
    }
    return $context;
  }

  public function getETreeActionFromRoute() {

    if ($this->isETreeCollectionRoute()) {
      return 'collection';
    }
  }


  protected function createContext($type, $value) {
    $context_definition = new ContextDefinition($type, NULL, FALSE);

    // Cache this context on the route.
    $cacheability = new CacheableMetadata();
    $cacheability->setCacheContexts(['route']);

    // Create a context from the definition and retrieved or created group.
    $context = new Context($context_definition, $value);
    $context->addCacheableDependency($cacheability);

    return $context;
  }


  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    $context = [];

    $context['etree_group'] = new Context(new ContextDefinition('config_entity:etree_group', $this->t('ETree Group from URL')));
    $context['etree_group_view'] = new Context(new ContextDefinition('string', $this->t('ETree Group View from URL')));


    return $context;
  }


  /**
   * The current route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  protected $groupStorage;

  /**
   * Gets the current route match object.
   *
   * @return \Drupal\Core\Routing\RouteMatchInterface
   *   The current route match object.
   */
  protected function getCurrentRouteMatch() {
    if (!$this->currentRouteMatch) {
      $this->currentRouteMatch = \Drupal::service('current_route_match');
    }
    return $this->currentRouteMatch;
  }

  /**
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *
   */
  protected function getGroupStorage() {
    if (!$this->groupStorage) {
      $this->groupStorage = \Drupal::service('entity_type.manager')->getStorage('etree_group');
    }
    return $this->groupStorage;
  }


  /**
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\etree_group\Entity\ETreeGroupInterface|null
   */
  public function getETreeGroupFromRoute(ETreeInterface $etree = NULL) {

    $route_name = $this->getCurrentRouteMatch()->getRouteName();

    if ($etree instanceof ETreeInterface) {
      return $etree->getGroup();
    }

    if ($this->isETreeCollectionRoute()) {
      foreach ($this->getGroupStorage()->loadMultiple() as $group) {
        /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
        if ($group->isCollectionRoute($route_name)) {
          return $group;
        }
      };
    }

    return NULL;

  }

  public function getCurrentRoute() {
    return $this->getCurrentRouteMatch()->getRouteName();
  }

  public function isETreeCollectionRoute() {

    $route_name = $this->getCurrentRoute();

    foreach ($this->getGroupStorage()->loadMultiple() as $group) {
      /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
      if ($group->isCollectionRoute($route_name)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  public function getETreeGroupViewFromRoute() {

    $route_name = $this->getCurrentRoute();

    foreach ($this->getGroupStorage()->loadMultiple() as $group) {
      /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
      if ($view_name = $group->getCollectionViewByRoute($route_name)) {
        return $view_name;
      }
    }
    return FALSE;
  }

  public function alterRuntimeContexts(array $unqualified_context_ids, &$context) {
    foreach ($unqualified_context_ids as $name) {
      switch ($name) {
        case 'etree_action':
          if ($action = $this->getETreeActionFromRoute()) {
            $context[$name] = $this->createContext('string', str_replace('_', '-', $action));
          }
          break;
      }
    }
    return $context;
  }
}