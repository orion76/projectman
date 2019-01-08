<?php


namespace Drupal\etree\Routing;


use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ETreeRouteSubscriber implements ContainerInjectionInterface {

  private $entityTypeManager;


  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  public function routes() {


    $routes = [];
    $defaults = [
      '_controller' => 'Drupal\etree\Controller\ETreeController::bundleList',
      //      'bundle' => $this->entity->bundle(),
      //      '_title' => $this->entity->label(),
    ];

    //    $route = new Route($route_path, $defaults);

    return $routes;
  }
}