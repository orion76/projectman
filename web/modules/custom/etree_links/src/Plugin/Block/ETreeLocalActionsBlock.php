<?php

namespace Drupal\etree_links\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\etree\Entity\ETree;
use Drupal\etree\Entity\ETreeInterface;
use Drupal\etree_context\Context\ETreeRouteContext;
use Drupal\etree_links\Plugin\ETreeLinksPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'LocalActionsBlock' block.
 *
 * @Block(
 *  id = "etree_links_local_actions_block",
 *  admin_label = @Translation("ETree local actions block"),
 * )
 */
class ETreeLocalActionsBlock extends BlockBase implements ContainerFactoryPluginInterface {


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

    $group = $this->etreeContext->getRuntimeContext('etree_group');

    if (!$group) {
      return NULL;
    }
    $appearson_parameters = ['group' => $group];


    $context = $this->etreeContext->getRuntimeContextsValues(['etree_group_view', 'etree_action', 'etree']);


    if (!empty($context['etree_group_view'])) {
      $appearson_parameters['view_id'] = $context['etree_group_view'];
    }

    if (!empty($context['etree_action'])) {
      $appearson_parameters['action'] = $context['etree_action'];
    }

    if ($context['etree'] instanceof ETreeInterface) {
      $appearson_parameters['etree'] = $context['etree'];
    }
    /**
     * @var array $appearson_parameters
     *  - group: EtreeGroup
     *  - view_id: group view_id
     *  - action: entity action
     *  - etree: ETree
     */
    $local_actions = $this->etreeLinksManager->getETreeActionsFromContext($appearson_parameters);
    $local_actions['#theme'] = 'etree_links_local_actions';
    return $local_actions;


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
