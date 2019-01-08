<?php

namespace Drupal\etree_links\Plugin;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\Plugin\Factory\ContainerFactory;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\etree_group\Entity\ETreeGroupInterface;
use Drupal\etree\Entity\ETreeInterface;
use Drupal\etree_links\Plugin\Menu\LocalAction\ETreeLinksLocalActionInterface;
use function in_array;
use function str_replace;


/**
 * Provides the Example plugin plugin manager.
 */
class ETreeLinksPluginManager extends DefaultPluginManager {

  /**
   * Provides some default values for all local action plugins.
   * title: 'task'
   * title_child: 'subtask'
   * title_context: 'pm-plan-page'
   * appears_on:
   *
   * @var array
   */
  protected $defaults = [
    // The plugin id. Set by the plugin system based on the top-level YAML key.
    'id' => NULL,
    // The static title for the local action.
    'title' => '',
    'title_child' => '',
    'bundle' => '',
    'link' => '',
    // The weight of the local action.
    'weight' => NULL,
    // Associative array of link options.
    'options' => [],
    // The route names where this local action appears.
    'appears_on' => [],
    // Default class for local action implementations.
    'class' => 'Drupal\etree_links\Plugin\Menu\LocalAction\ETreeLinksLocalAction',
  ];

  /**
   * An argument resolver object.
   *
   * @var \Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface
   */
  protected $argumentResolver;


  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The route provider to load routes by name.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The access manager.
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected $accessManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The plugin instances.
   *
   * @var \Drupal\Core\Menu\LocalActionInterface[]
   */
  protected $instances = [];


  public function __construct(RouteMatchInterface $route_match,
                              RouteProviderInterface $route_provider,
                              ModuleHandlerInterface $module_handler,
                              CacheBackendInterface $cache_backend,
                              LanguageManagerInterface $language_manager,
                              AccessManagerInterface $access_manager,
                              AccountInterface $account) {
    // Skip calling the parent constructor, since that assumes annotation-based
    // discovery.
    $this->factory = new ContainerFactory($this, 'Drupal\etree_links\Plugin\ETreeLinksPluginInterface');


    $this->routeMatch = $route_match;
    $this->routeProvider = $route_provider;
    $this->accessManager = $access_manager;
    $this->moduleHandler = $module_handler;
    $this->account = $account;
    $this->alterInfo('etree_local_actions');
    $this->setCacheBackend($cache_backend, 'etree_local_action_plugins:' . $language_manager->getCurrentLanguage()
        ->getId(), ['etree_local_action']);

  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $yaml_discovery = new YamlDiscovery('etree_links.action', $this->moduleHandler->getModuleDirectories());
      $yaml_discovery->addTranslatableProperty('title', 'title_context');
      $yaml_discovery->addTranslatableProperty('title_child', 'title_context');
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($yaml_discovery);
    }
    return $this->discovery;
  }


  protected function createAppearsOn(ETreeGroupInterface $group, $page, ETreeInterface $entity = NULL) {

    $args = [$group->id()];
    if ($entity) {
      $args[] = $entity->bundle();
    }
    $args[] = $page;
    return str_replace('-', '_', implode('.', $args));
  }


  protected function getContextDefinitions($context) {

    return array_filter($this->getDefinitions(), function ($action_info) use ($context) {
      $appears_on = $action_info['appears_on'];

      foreach (array_keys($appears_on) as $key) {

        if (!isset($context[$key])) {
          return FALSE;
        }

        if ($context[$key] !== $appears_on[$key]) {
          return FALSE;
        }
      }
      return TRUE;
    });
  }

  public function getETreeActionsFromContext($appearson_parameters) {
    $links = [];

    if (empty($appearson_parameters['group'])) {
      return $links;
    }




      $route_names = [];

      foreach ($this->getContextDefinitions($appearson_parameters) as $plugin_id => $action_info) {
        /** @var \Drupal\etree_links\Plugin\ETreeLinksPluginInterface $plugin */
        $plugin = $this->createInstance($plugin_id);
        $route_names[] = $plugin->getRouteName($appearson_parameters['group_id']);
        $this->instances[$plugin_id] = $plugin;

      }
      // Pre-fetch all the action route objects. This reduces the number of SQL
      // queries that would otherwise be triggered by the access manager.
      if (!empty($route_names)) {
        $this->routeProvider->getRoutesByNames($route_names);
      }


    $cacheability = new CacheableMetadata();
    $cacheability->addCacheContexts(['route']);

    $destination = \Drupal::destination()->getAsArray();
    /** @var $plugin ETreeLinksPluginInterface */
    foreach ($this->instances as $plugin_id => $plugin) {
      $route_name = $plugin->getRouteName($group);
      $route_parameters = $plugin->getRouteParameters($group, $page, $entity);
      $access = $this->accessManager->checkNamedRoute($route_name, $route_parameters, $this->account, TRUE);
      $links[$plugin_id] = [
        '#theme' => 'menu_local_action',
        '#link' => [
          'title' => $this->getTitle($plugin, $group, $page, $entity),
          'url' => Url::fromRoute($route_name, $route_parameters, ['query' => $destination]),
        ],
        '#access' => $access,
        '#weight' => $plugin->getWeight(),
      ];
      $cacheability->addCacheableDependency($access)->addCacheableDependency($plugin);
    }
    $cacheability->applyTo($links);

    return $links;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(ETreeLinksPluginInterface $local_action, ETreeGroupInterface $group, $page, ETreeInterface $entity = NULL) {
    $controller = [$local_action, 'getTitle'];
    $args = [$group, $page, $entity];
    return call_user_func_array($controller, $args);
  }

}
