<?php

namespace Drupal\etree_views\Plugin\views\argument;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\etree\ETreeCommon;
use Drupal\views\Plugin\views\argument\NumericArgument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Allow taxonomy term ID(s) as argument.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("etree_parent_id")
 */
class ETreeParentId extends NumericArgument {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $etreeStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $etree_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->etreeStorage = $etree_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')->getStorage('etree')
    );
  }

  /**
   * Override the behavior of title(). Get the title of the node.
   */
  public function title() {
    // There might be no valid argument.
    if ($this->argument) {
      $term = $this->etreeStorage->load($this->argument);
      if (!empty($term)) {
        return $term->label();
      }
    }
    // TODO review text
    return $this->t('No name');
  }

  public function query($group_by = FALSE) {
    $this->ensureMyTable();


    $this->value = $this->argument;

    /** @var \Drupal\etree\Entity\ETreeInterface $parent */
    $parent = $this->etreeStorage->load($this->value);
    $level = $parent->getLevel();
    $level_child = $level + 1;
    $this->realField = ETreeCommon::getParentField('id', $level);


    $placeholder = $this->placeholder();

    $level_placeholder = $this->query->placeholder("{$this->tableAlias}_etree_level");

    $this->query->addWhereExpression(0, "{$this->tableAlias}.{$this->realField} = $placeholder", [$placeholder => $this->argument]);
    $this->query->addWhereExpression(0, "{$this->tableAlias}.etree_level = $level_placeholder", [$level_placeholder => $level_child]);

  }

}
