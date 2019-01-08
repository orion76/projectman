<?php

namespace Drupal\etree_links\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\etree_group\Entity\ETreeGroupInterface;
use Drupal\etree\Entity\ETreeInterface;
use Drupal\etree\ETreeCommon;
use Drupal\etree_context\Context\ETreeRouteContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use function str_replace;

/**
 * Base class for Example plugin plugins.
 */
abstract class ETreeLinksPluginBase extends PluginBase implements ETreeLinksPluginInterface {

  use StringTranslationTrait;

  /**
   * The redirect destination.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  private $redirectDestination;

  private $contextProvider;

  /**
   * Constructs a MenuLinkAdd object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider to load routes by name.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination.
   */
  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              RouteProviderInterface $route_provider,
                              RedirectDestinationInterface $redirect_destination,
                              ETreeRouteContext $contextProvider) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $route_provider);

    $this->redirectDestination = $redirect_destination;
    $this->contextProvider = $contextProvider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('router.route_provider'),
      $container->get('redirect.destination'),
      $container->get('etree_context.context_route')
    );
  }

  public function getRouteName($group_id) {
    return ETreeCommon::getRouteName($group_id, $this->getLink());
  }

  public function getLink() {
    return str_replace('-', '_', $this->pluginDefinition['link']);
  }

  public function getBundle() {
    return $this->pluginDefinition['bundle'];
  }

  public function getRouteParameters(ETreeGroupInterface $group, $page, ETreeInterface $entity = NULL) {
    $parameters = ['group_id' => $group->id()];
    $link = $this->getLink();
    switch ($link) {
      case 'canonical':
      case 'edit_form':
        $parameters['etree'] = $entity;
        break;
      case 'add_form':
        if ($entity) {
          $parameters['parent_id'] = $entity->id();
        }
        $parameters['etree_type'] = $this->getBundle();
        break;
    }
    return $parameters;
  }

  public function getWeight() {
    return $this->pluginDefinition['weight'];
  }


  /**
   * {@inheritdoc}
   */
  public function getTitle(ETreeGroupInterface $group, $page, ETreeInterface $entity = NULL) {


    $definition = $this->getPluginDefinition();
    if ($page === 'collection') {
      return (string) $definition['title'];
    }

    if ($this->getLink() === 'add_form' && !empty($entity) && $entity->bundle() === $this->getBundle()) {
      $title = $definition['title_child'];
    }

    if (empty($title)) {
      $title = $definition['title'];
    }

    return (string) $title;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions(RouteMatchInterface $route_match) {

    // Append the current path as destination to the query string.
    $options['query']['destination'] = $this->redirectDestination->get();
    return $options;
  }

}
