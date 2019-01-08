<?php


namespace Drupal\etree;


use function is_null;

class ETreeHierarchyData implements ETreeHierarchyDataInterface {

  protected $type;

  protected $data = [];

  public function __construct($type, $data = NULL) {
    $this->type = $type;

    if (!isset($data)) {
      $data = $this->dataDefault();
    }

    $this->setData($data);
  }


  public function dataDefault($id = NULL) {
    return [
      'id' => $id,
      'etree_level' => 0,
      'etree_weight' => 0,
      'etree_path' => '',
      'etree_group' => '',
    ];
  }

  public function isNew() {
    return is_null($this->data['id']);
  }

  /**
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function getData($name = NULL) {
    if (is_null($name)) {
      return $this->data;
    }

    return isset($this->data[$name]) ? $this->data[$name] : NULL;
  }

  public function type() {
    return $this->type;
  }


  /**
   * @return mixed
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function id() {
    return $this->getData('id');
  }

  /**
   * @return mixed
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function path() {
    return $this->getData('etree_path');
  }

  /**
   * @return mixed
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function group() {
    return $this->getData('etree_group');
  }

  /**
   * @return mixed
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function level() {
    return $this->getData('etree_level');
  }

  /**
   * @return mixed
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function parentLevel() {
    return $this->level() - 1;
  }

  /**
   * @return mixed
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function weight() {
    return $this->getData('etree_weight');
  }

  /**
   * @param null $level
   *
   * @return mixed
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function parentId($level = NULL) {
    if (is_null($level)) {
      $level = $this->getData('etree_level');
    }
    if ($level > 0) {
      return $this->getParentValue(ETreeCommon::PARENT_ID, $level - 1);
    }
    return NULL;
  }

  /**
   * @param $name
   * @param $level
   *
   * @return mixed
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  protected function getParentValue($name, $level) {
    $field_name = ETreeCommon::getParentField($name, $level);
    return $this->getData($field_name);
  }

  /**
   * @return array
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function getParentValues() {
    $data = $this->getData();
    $values = [];
    foreach (ETreeCommon::getParentFields() as $field) {
      if (isset($data[$field]) && !empty($data[$field])) {
        $values[$field] = $data[$field];
      }
    }
    return $values;
  }

  /**
   * @return array
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function getParentIds() {
    $data = $this->getData();
    $values = [];
    foreach (ETreeCommon::getParentFields([ETreeCommon::PARENT_ID]) as $field) {
      if (isset($data[$field]) && !empty($data[$field])) {
        $values[$field] = $data[$field];
      }
    }
    return $values;
  }

  /**
   * @return string
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function createPath() {
    $path = $this->getParentIds();
    return ETreeCommon::createPath($path);
  }

  /**
   * @param $child_id
   * @param $weight
   *
   * @return array
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  public function getChildData($child_type, $child_id) {

    $data = [
      'id' => $child_id,
      'etree_level' => $this->level() + 1,
    ];

    $data += $this->getParentValues();
    $self_id = ETreeCommon::getParentField(ETreeCommon::PARENT_ID, $this->level());
    $self_type = ETreeCommon::getParentField(ETreeCommon::PARENT_TYPE, $this->level());

    $data[$self_id] = $this->id();
    $data[$self_type] = $this->type();

    $child_data = new ETreeHierarchyData($child_type, $data);

    $child_data->setPath($child_data->createPath());


    return $child_data->getData();
  }

  public function setWeight($weight): void {
    $this->data['etree_weight'] = $weight;
  }

  public function setPath($path): void {
    $this->data['etree_path'] = $path;
  }

  public function setGroup($group_id): void {
    $this->data['etree_group'] = $group_id;
  }

  /**
   * @param mixed $data
   */
  public function setData($data): void {
    $this->data = $data;
  }

}