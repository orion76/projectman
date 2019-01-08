<?php

namespace Drupal\etree_context\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\etree_context\Context\ETreeRouteContextInterface;

/**
 * Defines an interface for Example plugin plugins.
 */
interface ETreeContextPluginInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {


  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids);

  public function alterRuntimeContexts(array $unqualified_context_ids, &$context);

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts();

}
