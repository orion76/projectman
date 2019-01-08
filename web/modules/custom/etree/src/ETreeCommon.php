<?php


namespace Drupal\etree;


use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\ContentEntityInterface;
use function str_replace;

class ETreeCommon {

  const PATH_DELIMITER = '.';

  const LEVELS = 10;

  const PARENT_PREFIX = 'parent';

  const PARENT_ID = 'id';

  const PARENT_TYPE = 'type';

  const HIERARCHY_FIELDS = [
    'etree_group',
    'etree_level',
    'etree_weight',
    'etree_path',
  ];

  public static function getParentFields($fields = [self::PARENT_ID, self::PARENT_TYPE], $max_level = self::LEVELS) {
    $parent_fields = [];
    for ($level = 0; $level < $max_level; $level++) {
      foreach ($fields as $name) {
        $parent_fields[] = static::getParentField($name, $level);
      }
    }
    return $parent_fields;
  }

  public static function createPath($ids) {
    return implode(self::PATH_DELIMITER, $ids);
  }

  public static function getParentLevelFields($max_level = self::LEVELS) {
    $fields = [self::PARENT_ID, self::PARENT_TYPE];
    $parent_fields = [];
    for ($level = 0; $level < $max_level; $level++) {
      $level_fields = [];
      foreach ($fields as $name) {
        $level_fields[$name] = static::getParentField($name, $level);
      }
      $parent_fields[$level] = $level_fields;
    }
    return $parent_fields;
  }


  public static function getParentField($name, $level) {
    $field = static::PARENT_PREFIX;
    if ($name !== $field) {
      $field .= '_' . $name;
    }
    return "{$field}_{$level}";
  }

  public static function linkModalAttributes() {
    return [
      'class' => ['use-ajax'],
      'data-dialog-type' => 'modal',
      'data-dialog-options' => Json::encode([
        'width' => '95%',
        'drupalAutoButtons' => FALSE,
        'buttons' => [],
      ]),
    ];
  }

  public static function linkModalAttached() {
    return [
      'library' => ['core/drupal.ajax'],
    ];
  }

  static function getAliasNames() {
    return [
      'canonical' => TRUE,
      'add-form' => TRUE,
      'edit-form' => TRUE,
    ];
  }

  static function getPathTemplate($root_path, $bundle, $path_type) {
    switch ($path_type) {
      case 'canonical':
        $template = "{$root_path}/{$bundle}/{etree}";
        break;
      case 'add-form':
        $template = "{$root_path}/{etree}/add/{etree_type}";
        break;
      case 'edit-form':
        $template = "{$root_path}/{$bundle}/{etree}/edit";
        break;
    }
    return $template;
  }

  static function baseRouteName($group_name) {
    return "entity.etree.{$group_name}";
  }

  static function getRouteName($group_name, $path_type) {

    $base = static::baseRouteName($group_name);

    $path_type = str_replace('-', '_', $path_type);

    $route_name = "{$base}.{$path_type}";

    return $route_name;
  }

  static function getGroupViewsName($group_id) {
    return "etree_collection_{$group_id}";
  }

  static function getGroupViewsRouteName($group_id) {
    return 'view.' . static::getGroupViewsName($group_id) . '.etree_1';
  }

  /**
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *
   * @return array
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public static function entityModalLink(ContentEntityInterface $entity, $title, $rel = 'canonical') {
    $options = ['attributes' => static::linkModalAttributes()];
    return [
      '#markup' => $entity->toLink($title, $rel, $options)->toString(),
      '#attached' => static::linkModalAttached(),
    ];
  }

}