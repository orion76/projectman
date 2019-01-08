<?php

namespace Drupal\etree_context\Context;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\etree_context\ETreeContextTrait;

/**
 * Sets the current group as a context on group routes.
 */
class ETreeRouteContext implements ETreeRouteContextInterface {

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
  public function getRuntimeContext($context_id) {
    $context = $this->getRuntimeContexts([$context_id]);
    if (isset($context[$context_id])) {
      return $context[$context_id]->getContextValue();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContextsValues(array $unqualified_context_ids) {
    $context = $this->getRuntimeContexts($unqualified_context_ids);
    $return = [];
    foreach ($unqualified_context_ids as $context_id) {
      if (!isset($context[$context_id])) {
        return FALSE;
      }
      $return[$context_id] = $context[$context_id]->getContextValue();
    }
    return $return;
  }

  /**
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {


    $context = $this->getContextPluginManager()->getRuntimeContexts($unqualified_context_ids);

    return $context;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {

    $context = $this->getContextPluginManager()->getAvailableContexts();

    return $context;
  }

  /**
   * The current route match object.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  protected $groupStorage;


}
