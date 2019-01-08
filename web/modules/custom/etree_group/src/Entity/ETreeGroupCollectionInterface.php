<?php

namespace Drupal\etree_group\Entity;

interface ETreeGroupCollectionInterface {

  public function getViews();

  public function getView($name);

  /**
   * @param $view
   *
   * @return string
   */
  public function getPath($view_name);

  /**
   * @return array
   */
  public function getPaths();

  /**
   * @return array
   */
  public function loadCollectionViews();

  /**
   * @param $view_name
   *
   * @return string
   */
  public function getRoute($view_name);

  /**
   * @return array
   */
  public function getRoutes();

  /**
   * @return bool
   */
  public function isCollectionRoute($route_name);

  /**
   * @return bool
   */
  public function getCollectionViewByRoute($route_name);

  /**
   * @param $path
   *
   * @return bool
   */
  public function isCollectionPath($path);

  public function getPathSettings($path);
}