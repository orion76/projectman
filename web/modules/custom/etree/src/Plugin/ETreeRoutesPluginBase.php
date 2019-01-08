<?php

namespace Drupal\etree\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Example plugin plugins.
 */
abstract class ETreeRoutesPluginBase extends PluginBase implements ETreeRoutesPluginInterface {

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
    $n=0;
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }


}
