<?php

namespace Drupal\etree_links\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\etree_context\Context\ETreeRouteContext;
use Drupal\etree_links\ETreeAjaxOverview;
use Drupal\etree_links\Plugin\ETreeLinksPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'LocalActionsBlock' block.
 *
 * @Block(
 *  id = "etree_links_ajax_overview_block",
 *  admin_label = @Translation("ETree ajax Overview"),
 * )
 */
class AjaxOverviewBlock extends BlockBase implements ContainerFactoryPluginInterface {


  protected $etreeLinksManager;

  protected $etreeContext;

  public function __construct(array $configuration,
                              $plugin_id,
                              $plugin_definition,
                              ETreeLinksPluginManager $local_action_manager,
                              ETreeRouteContext $etree_context
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->etreeLinksManager = $local_action_manager;
    $this->etreeContext = $etree_context;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $container = ETreeAjaxOverview::createContainer('');

    return ['#markup' => render($container)];

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $n = 0;
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.etree_links'),
      $container->get('etree_context.context_route')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @todo Make cacheable in https://www.drupal.org/node/2232375.
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
