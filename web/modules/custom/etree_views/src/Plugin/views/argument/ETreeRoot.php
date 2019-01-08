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
 * @ViewsArgument("etree_root")
 */
class ETreeRoot extends NumericArgument {

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
      $entity = $this->etreeStorage->load($this->argument);
      if (!empty($entity)) {
        return $entity->label();
      }
    }
    // TODO review text
    return $this->t('No name');
  }

  public function query($group_by = FALSE) {
    $this->ensureMyTable();
    $this->value = $this->argument;

    $placeholder = $this->placeholder();
    $this->query->addWhereExpression(0, "{$this->tableAlias}.{$this->realField} = $placeholder", [$placeholder => $this->argument]);
  }

}
