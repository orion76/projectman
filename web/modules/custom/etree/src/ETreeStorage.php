<?php

namespace Drupal\etree;

use Drupal\Core\Entity\EntityInterface;
use Drupal\etree\drulib\entity\EntityFullStorage;
use Drupal\etree\Entity\ETreeInterface;

/**
 * Defines the storage handler class for ETree entities.
 *
 * This extends the base storage class, adding required special handling for
 * ETree entities.
 *
 * @ingroup etree
 */
class ETreeStorage extends EntityFullStorage implements ETreeStorageInterface {

  /**
   * @var \Drupal\etree\ETreeStorageHierarchy
   */
  protected $hierarchy;

  public function __construct(\Drupal\Core\Entity\EntityTypeInterface $entity_type, \Drupal\Core\Database\Connection $database, \Drupal\Core\Entity\EntityManagerInterface $entity_manager, \Drupal\Core\Cache\CacheBackendInterface $cache, \Drupal\Core\Language\LanguageManagerInterface $language_manager) {
    parent::__construct($entity_type, $database, $entity_manager, $cache, $language_manager);
    $this->hierarchy = new ETreeStorageHierarchy($database);
  }


  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return bool|int
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save(EntityInterface $entity) {
    $is_new = $entity->isNew();

    //    if ($is_new) {
    //      $sibling = $this->loadSiblingLast($entity);
    //      $entity->weight = $sibling->weight;
    //    }

    return parent::save($entity);
  }

  /**
   * Array of loaded parents keyed by child term ID.
   *
   * @var array
   */
  protected $parents = [];

  /**
   * @return \Drupal\etree\ETreeStorageHierarchy
   */
  public function getHierarchy(): \Drupal\etree\ETreeStorageHierarchy {
    return $this->hierarchy;
  }

  public function loadChildren($id, $level) {
    $ids = $this->hierarchy->getChildrenIds($id, $level);
    return $this->loadMultiple($ids);
  }

  protected function aliasHierarchyFields() {
    return array_map(function ($field) {
      return ['' => $field, 'alias' => "etree_{$field}"];
    }, ETreeCommon::HIERARCHY_FIELDS);
  }

  protected function buildQuery($ids, $revision_ids = FALSE) {

    $query = parent::buildQuery($ids, $revision_ids);

    $query->leftJoin('etree_hierarchy', 'hierarchy', "hierarchy.id = base.id");
    $query->fields('hierarchy');

    return $query;

  }

  /**
   * Gets entities from the storage.
   *
   * @param array|null $ids
   *   If not empty, return entities that match these IDs. Return all entities
   *   when NULL.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface[]
   *   Array of entities from the storage.
   * @throws \Drupal\etree\exception\ETreeHierarchyDataException
   */
  protected function getFromStorage(array $ids = NULL) {
    $entities = [];

    if (!empty($ids)) {
      // Sanitize IDs. Before feeding ID array into buildQuery, check whether
      // it is empty as this would load all entities.
      $ids = $this->cleanIds($ids);
    }

    if ($ids === NULL || $ids) {
      // Build and execute the query.
      $query_result = $this->buildQuery($ids)->execute();
      $records = $query_result->fetchAllAssoc($this->idKey);

      if ($records) {
        /** @var \Drupal\etree\ETreeHierarchyData[] $hierarchy_data */
        $hierarchy_data = [];

        foreach (array_keys($records) as $entity_id) {
          $record =& $records[$entity_id];

          $hierarchy_data [$entity_id] = $this->createHierarchyData($record);
          $record->hierarchy = $hierarchy_data [$entity_id]->getParentIds();

          $this->clearRecordParentFields($record);
        }

        $entities = $this->mapFromStorageRecords($records);

        foreach ($entities as $id => $entity) {
          /* @var ETreeInterface $entity */
          $entity->setHierarchyData($hierarchy_data[$id]);
          $entity->set('etree_parent', $entity->getHierarchyData()->parentId());
        }

      }
    }

    return $entities;
  }

  protected function clearRecordParentFields(&$record) {
    foreach (ETreeCommon::getParentFields() as $field) {
      if (isset($record->{$field})) {
        unset($record->{$field});
      }
    }
  }

  /**
   * @param $record
   *
   * @return \Drupal\etree\ETreeHierarchyData
   */
  protected function createHierarchyData($record) {
    $hierarchy = new ETreeHierarchyData($record->type, (array) $record);

    return $hierarchy;
  }


  /**
   * {@inheritdoc}
   */
  public function updateHierarchy($record) {

    $this->getHierarchy()->deleteIds($record['id']);
    $this->getHierarchy()->insert($record);
  }

  /**
   * {@inheritdoc}
   */
  public function updateWeight(ETreeInterface $item) {
    $this->getHierarchy()->incrementSiblingWeight($item->getLevel(), $item->getParentId(), $item->getWeight());
    $this->getHierarchy()->updateWeight($item->id(), $item->getWeight());
  }


  /**
   * {@inheritdoc}
   */
  protected function doDeleteFieldItems($entities) {
    parent::doDeleteFieldItems($entities);
    $ids = array_keys($entities);
    $this->getHierarchy()->deleteIds($ids);
  }
}
