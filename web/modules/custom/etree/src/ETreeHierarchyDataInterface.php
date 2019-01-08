<?php

namespace Drupal\etree;

interface ETreeHierarchyDataInterface {

  public function type();

  public function id();

  public function path();

  public function level();

  public function parentLevel();

  public function weight();

  public function parentId($level = NULL);

  public function getParentValues();

  public function getParentIds();

  public function createPath();

  public function getChildData($child_type, $child_id);
}