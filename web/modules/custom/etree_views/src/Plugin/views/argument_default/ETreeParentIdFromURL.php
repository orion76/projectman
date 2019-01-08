<?php

namespace Drupal\etree_views\Plugin\views\argument_default;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\etree\Entity\ETreeInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Taxonomy tid default argument.
 *
 * @ViewsArgumentDefault(
 *   id = "etree_parent_id_from_url",
 *   title = @Translation("ETree parent ID from URL")
 * )
 */
class ETreeParentIdFromURL extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {


  /**
   * The group entity from the route.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new GroupIdFromUrl instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Plugin\Context\ContextProviderInterface $context_provider
   *   The group route context.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              RouteMatchInterface $routeMatch) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition
  ) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    if (($etree = $this->routeMatch->getParameter('etree')) && $etree instanceof ETreeInterface) {
      return $etree->id();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // We cache the result on the route instead of the URL so that path aliases
    // can all use the same cache context. If you look at ::getArgument() you'll
    // see that we actually get the group ID from the route, not the URL.
    return ['route'];
  }


}
