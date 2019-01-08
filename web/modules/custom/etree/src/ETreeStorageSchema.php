<?php

namespace Drupal\etree;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the term schema handler.
 */
class ETreeStorageSchema extends SqlContentEntityStorageSchema {


  static function HierarchySchemaFields() {
    $fields = [
      'id' => [
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'default' => 0,
        'description' => 'Primary Key: ID.',
      ],
      'etree_group' => [
        'type' => 'varchar_ascii',
        'length' => 128,
        'not null' => TRUE,
        'default' => '',
        'description' => "Etree group",
      ],
      'etree_path' => [
        'type' => 'varchar_ascii',
        'length' => 255,
        'not null' => TRUE,
        'default' => '',
        'description' => "Etree item path",
      ],
      'etree_weight' => [
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'default' => 0,
        'description' => 'Record weight.',
      ],
      'etree_level' => [
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'default' => 0,
        'description' => 'ETree Item level.',
      ],
    ];

    foreach (ETreeCommon::getParentLevelFields() as $level_fields) {
      $fields += [
        $level_fields[ETreeCommon::PARENT_ID] => [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Parent ID',
        ],
        $level_fields[ETreeCommon::PARENT_TYPE] => [
          'type' => 'varchar_ascii',
          'length' => 128,
          'not null' => TRUE,
          'default' => '',
          'description' => "Parent TYPE",
        ],
      ];
    }
    return $fields;
  }

  static function HierarchySchemaIndexes() {


    $parent_fields = ETreeCommon::getParentFields();

    $parent_indexes = array_map(function ($item) {
      return [$item];
    }, $parent_fields);

    $indexes = array_combine($parent_fields, $parent_indexes);

    $hierarchy_indexes = array_map(function ($item) {
      return [$item];
    }, ETreeCommon::HIERARCHY_FIELDS);

    $indexes += array_combine(ETreeCommon::HIERARCHY_FIELDS, $hierarchy_indexes);

    return $indexes;
  }

  static function HierarchySchemaPrimaryKey() {
    return ['id'];
  }


  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {

    $schema = parent::getEntitySchema($entity_type, $reset = FALSE);

    $schema['etree_hierarchy'] = [
      'description' => 'Stores the hierarchical relationship between leafs.',
      'fields' => static::HierarchySchemaFields(),
      'indexes' => static::HierarchySchemaIndexes(),
      'foreign keys' => [
        'etree_data' => [
          'table' => 'etree_data',
          'columns' => ['id' => 'id'],
        ],
      ],
      'primary key' => static::HierarchySchemaPrimaryKey(),
    ];


    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
    $field_name = $storage_definition->getName();

    if ($table_name == 'etree_field_data') {
      switch ($field_name) {
        case 'name':
          $this->addSharedTableFieldIndex($storage_definition, $schema, TRUE);
          break;
      }
    }

    return $schema;
  }

}
