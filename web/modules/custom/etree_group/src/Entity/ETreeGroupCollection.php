<?php


namespace Drupal\etree_group\Entity;


use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Url;

class ETreeGroupCollection implements ETreeGroupCollectionInterface {

  use MessengerTrait;

  private $group;

  public function __construct(ETreeGroupInterface $group) {
    $this->group = $group;
  }

  /**
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  private function getViewsStorage() {
    try {
      return Drupal::entityTypeManager()->getStorage('view');
    } catch (InvalidPluginDefinitionException $e) {
      $this->messenger()->addError($e->getMessage());
    } catch (PluginNotFoundException $e) {
      $this->messenger()->addError($e->getMessage());
    }
  }


  public function getViews() {
    return $this->group->getCollectionViews();
  }

  public function getView($name) {
    foreach ($this->getViews() as $view_data) {
      if ($view_data['id'] === $name) {
        return $view_data;
      }
    }
  }

  /**
   * @param $view
   *
   * @return string
   */
  public function getPath($view_name) {
    $view_data = $this->getView($view_name);

    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = $this->getViewsStorage()->load($view_data['view_id']);


    /** @var \Drupal\views\Plugin\views\display\Page $display */
    $display = $view->getDisplay($view_data['display_id']);

    $path = $display['display_options']['path'];

    return $path;
  }

  /**
   * @return array
   */
  public function getPaths() {

    $views = $this->loadCollectionViews();

    return array_map(function ($view_data) use ($views) {
      $view_id = $view_data['id'];
      $view = $views[$view_id];
      $display = $view->getDisplay($view_data['display_id']);

      return $display['display_options']['path'];
    }, $this->getViews());
  }

  /**
   * @return array
   */
  public function loadCollectionViews() {
    $views_ids = array_unique(array_column($this->getViews(), 'view_id'));
    $data = array_column($this->getViews(), 'view_id', 'id');


    $views = $this->getViewsStorage()->loadMultiple($views_ids);

    return array_map(function ($view_id) use ($views) {
      return $views[$view_id];
    }, $data);
  }

  /**
   * @param $view_name
   *
   * @return string
   */
  public function getRoute($view_name) {
    $path = "/" . trim($this->getPath($view_name), '/');
    return Url::fromUserInput($path)->getRouteName();
  }


  /**
   * @return array
   */
  public function getRoutes() {

    $routes = [];
    foreach ($this->getViews() as $view_data) {
      $view_id = $view_data['id'];
      $routes[$view_id] = $this->getRoute($view_id);
    };
    return $routes;
  }


  /**
   * @return bool
   */
  public function isCollectionRoute($route_name) {
    $routes = array_flip($this->getRoutes());
    return isset($routes[$route_name]);
  }

  /**
   * @return bool
   */
  public function getCollectionViewByRoute($route_name) {
    $routes = array_flip($this->getRoutes());
    return isset($routes[$route_name]) ? $routes[$route_name] : NULL;
  }

  /**
   * @param $path
   *
   * @return bool
   */
  public function isCollectionPath($path) {
    foreach ($this->getPaths() as $view_path) {
      /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
      if (strpos(trim($path, '/'), trim($view_path, '/')) === 0) {
        return TRUE;
      }
    }
  }



  public function getPathSettings($path) {
    foreach ($this->getPaths() as $name => $view_path) {
      /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
      if (strpos(trim($path, '/'), trim($view_path, '/')) === 0) {
        return $this->getView($name);
      }
    }
  }
}