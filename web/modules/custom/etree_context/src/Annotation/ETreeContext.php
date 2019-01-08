<?php

namespace Drupal\etree_context\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Example plugin item annotation object.
 *
 * @see \Drupal\etree\Plugin\ETreeRoutesPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class ETreeContext extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The links type of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $link_type;

}
