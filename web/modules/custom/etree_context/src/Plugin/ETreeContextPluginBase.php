<?php

namespace Drupal\etree_context\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Example plugin plugins.
 */
abstract class ETreeContextPluginBase extends PluginBase implements ETreeContextPluginInterface {

  use StringTranslationTrait;


  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }


}
