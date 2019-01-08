<?php

namespace Drupal\etree\Plugin\ETreeContext;

use function count;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\etree\Entity\ETreeInterface;

use Drupal\etree_context\ETreeContextTrait;
use Drupal\etree_context\Plugin\ETreeContextPluginBase;
use Drupal\etree_context\Plugin\ETreeContextPluginInterface;
use function str_replace;

/**
 * Class ETreeContextPlugin
 *
 * @ETreeContext(
 *   id = "etree",
 *   label = @Translation("ETree")
 * )
 *
 * @package Drupal\etree_group
 */
class ETreeContextPlugin extends ETreeContextPluginBase implements ETreeContextPluginInterface {

  use StringTranslationTrait;

  use ETreeContextTrait;

  protected $routeParts;

  /**
   * Constructs a new GroupRouteContext.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match object.
   */
  public function __construct(RouteMatchInterface $current_route_match) {
    $this->currentRouteMatch = $current_route_match;
  }


  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {

    $context = [];

    foreach ($unqualified_context_ids as $name) {
      switch ($name) {
        case 'etree_action':
          $context[$name] = $this->createContext('string', str_replace('_', '-', $this->getETreeActionFromRoute()));
          break;
        case 'etree':
          $context[$name] = $this->createContext('entity:etree', $this->getETreeFromRoute());
          break;
      }
    }

    return $context;
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
    $context = [
      'etree' => new Context(new ContextDefinition('entity:etree', $this->t('ETree from URL'))),
      'etree_action' => new Context(new ContextDefinition('string', $this->t('ETree Action from URL'))),
    ];
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
   * Retrieves the group entity from the current route.
   *
   * This will try to load the group entity from the route if present. If we are
   * on the group add form, it will return a new group entity with the group
   * type set.
   *
   * @return \Drupal\group\Entity\GroupInterface|null
   *   A group entity if one could be found or created, NULL otherwise.
   */
  public function getETreeFromRoute() {
    $route_match = $this->getCurrentRouteMatch();

    // See if the route has a group parameter and try to retrieve it.
    if (($etree = $route_match->getParameter('etree')) && $etree instanceof ETreeInterface) {
      return $etree;
    }

    return NULL;
  }

  public function alterRuntimeContexts(array $unqualified_context_ids, &$context) {
    // TODO: Implement alterRuntimeContexts() method.
  }
}
