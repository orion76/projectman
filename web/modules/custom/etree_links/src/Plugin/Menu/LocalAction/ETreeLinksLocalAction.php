<?php

namespace Drupal\etree_links\Plugin\Menu\LocalAction;

use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\etree_context\Context\ETreeRouteContext;
use Drupal\etree_links\Plugin\ETreeLinksPluginBase;
use Drupal\etree_links\Plugin\ETreeLinksPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Modifies the 'Add link' local action to add a destination.
 */
class ETreeLinksLocalAction extends ETreeLinksPluginBase implements ETreeLinksPluginInterface {



}