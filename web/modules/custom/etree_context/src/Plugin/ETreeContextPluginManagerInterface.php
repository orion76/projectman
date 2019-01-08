<?php

namespace Drupal\etree_context\Plugin;


/**
 * Provides the Example plugin plugin manager.
 */
interface ETreeContextPluginManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids);

  /**
   * {@inheritdoc}
   */
  public function alterRuntimeContexts(array $unqualified_context_ids, &$context);

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts();
}