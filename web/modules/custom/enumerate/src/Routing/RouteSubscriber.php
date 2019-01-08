<?php

namespace Drupal\enumerate\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use function strpos;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    //    $voc_overview = $collection->get('entity.taxonomy_vocabulary.overview_form');
    //    $collection->add('entity.enumerate.categories', $this->cloneRoute($voc_overview, '/admin/structure/enumerate/categories'));

    //    $route_list=$collection->get('entity.enumerate.collection');
    $routes = [];
    foreach (array_keys($collection->all()) as $name) {
      if (strpos($name, 'entity.enumerate') === 0) {
        $routes[$name] = $collection->get($name);
      }
    }
    $n = 0;
  }

  /**
   * {@inheritdoc}
   */
  public function cloneRoute(Route $source, $path) {

    $route = new Route('/');

    $route->unserialize($source->serialize());
    $route->setPath($path);
    $route->compile();
    return $route;
  }


}
