<?php

namespace Drupal\etree_context\Context;

use Drupal\Core\Plugin\Context\ContextProviderInterface;

/**
 * Sets the current group as a context on group routes.
 */
interface ETreeRouteContextInterface extends ContextProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids);

  public function getRuntimeContext($context_id);

  public function getRuntimeContextsValues(array $unqualified_context_ids);

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts();

}