<?php

namespace Drupal\etree;

/**
 * Defines the storage handler class for ETree entities.
 *
 * This extends the base storage class, adding required special handling for
 * ETree entities.
 *
 * @ingroup etree
 */
class ETreeStorageHierarchy {

  protected $database;


  const TABLE = 'etree_hierarchy';

  public function __construct(\Drupal\Core\Database\Connection $database) {

    $this->database = $database;

  }

  protected function fieldName($name) {
    return self::TABLE . '.' . $name;
  }

  /**
   * @param $values
   *
   * @throws \Exception
   */
  public function insert($values) {
    $this->database->insert(self::TABLE)
      ->fields(array_keys($values))
      ->values($values)
      ->execute();
  }

  public function load($id) {
    $query = $this->database->select(self::TABLE, self::TABLE)
      ->fields(self::TABLE);
    $query->condition(self::fieldName('id'), $id);

    return $query->execute()->fetchAll();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteIds($ids) {
    $this->database->delete(self::TABLE)
      ->condition('id', $ids, 'IN')
      ->execute();
  }

  /**
   * @param  \Drupal\Core\Database\Query\SelectInterface|\Drupal\Core\Database\Query\Update $query
   * @param $level
   * @param $parent_id
   *
   * @return mixed
   */
  protected function conditionSibling($query, $level = 0, $parent_id = NULL) {

    if ($level > 0) {

      $parent_level = $level - 1;
      $parent_field = ETreeCommon::getParentField(ETreeCommon::PARENT_ID, $parent_level);

      $query->condition(self::fieldName($parent_field), $parent_id);
      $query->condition(self::fieldName('etree_level'), $level);
    }
    else {
      $query->condition(self::fieldName('etree_level'), 0);
    }
    return $query;
  }

  /**
   * @param  \Drupal\Core\Database\Query\SelectInterface|\Drupal\Core\Database\Query\Update $query $query
   * @param $id
   * @param $level
   *
   * @return mixed
   */
  protected function conditionChildren($query, $id, $level) {

    $parent_field = ETreeCommon::getParentField(ETreeCommon::PARENT_ID, $level);
    $child_level = $level + 1;

    $query->condition(self::fieldName($parent_field), $id);

    $query->condition(self::fieldName('etree_level'), $child_level);

    $query->orderBy(self::fieldName('etree_weight'));

    return $query;
  }

  /**
   * @param  \Drupal\Core\Database\Query\SelectInterface|\Drupal\Core\Database\Query\Update|\Drupal\Core\Database\Query\Delete $query
   * @param $id
   * @param $level
   *
   * @return mixed
   */
  protected function conditionChildrenAll($query, $id, $level) {

    $parent_field = ETreeCommon::getParentField(ETreeCommon::PARENT_ID, $level);

    $query->condition(self::fieldName($parent_field), $id);

    return $query;
  }

  public function getChildrenIdsAll($id, $level) {
    $query = $this->database->select(self::TABLE)->fields(self::TABLE, ['id']);

    $this->conditionChildrenAll($query, $id, $level);
    return $query->execute()->fetchCol();
  }

  public function getChildrenIds($id, $level) {
    $query = $this->database->select(self::TABLE)->fields(self::TABLE, ['id']);

    $this->conditionChildren($query, $id, $level);
    return $query->execute()->fetchCol();
  }

  public function deleteChildren($id, $level) {
    $query = $this->database->delete(self::TABLE);
    $this->conditionChildrenAll($query, $id, $level);
    return $query->execute();
  }


  public function incrementSiblingWeight($level, $parent_id, $weight) {

    $query = $this->database->update(self::TABLE);
    $this->conditionSibling($query, $level, $parent_id);

    return $query
      ->expression('etree_weight', 'etree_weight + 1')
      ->condition('etree_weight', $weight, '>=')
      ->execute();
  }

  public function updateWeight($id, $weight) {

    $query = $this->database->update(self::TABLE)
      ->condition('id', $id)
      ->fields(['etree_weight' => $weight]);

    return $query->execute();
  }

  public function getNextWeight($level = 0, $parent_id = NULL) {

    $query = $this->database->select(self::TABLE, self::TABLE);

    $this->conditionSibling($query, $level, $parent_id);

    $query->groupBy('etree_level')
      ->addExpression('MAX(etree_weight) + 1', 'next_weight');

    $weight = $query->execute()->fetchField();

    return $weight ? $weight : 0;

  }

}
