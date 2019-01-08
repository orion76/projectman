<?php

namespace Drupal\etree_views\Plugin\views\field;


use Drupal\Core\Config\Entity\ConfigEntityStorage;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\etree\Entity\ETreeGroup;
use Drupal\views\ResultRow;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handler for showing etree_item_path.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("etree_group")
 */
class ETreeItemGroup extends ETreeItemFieldBase {

  protected $storageGroup;

  /**
   * Constructs a PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigEntityStorage $storageGroup) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);


    $this->storageGroup = $storageGroup;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('etree_group')
    );
  }

  public function query($group_by = FALSE) {
    $this->ensureMyTable();


    $this->query->addField($this->tableAlias, $this->realField);


  }

  public function render(ResultRow $values) {
    $group_id = $values->{$this->getFieldName($this->realField)};
    /** @var ETreeGroup $group */
    $group = $this->storageGroup->load($group_id);
    $url = Url::fromUserInput('/' . $group->getPath());
    $link = new Link($group->label(), $url);
    return $link->toString();
  }
}
