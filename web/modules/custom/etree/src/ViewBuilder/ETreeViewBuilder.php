<?php

namespace Drupal\etree\ViewBuilder;

use Drupal\Core\Config\Entity\ConfigEntityStorage;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\etree\Entity\ETreeInterface;

/**
 * View builder handler for etree.
 */
class ETreeViewBuilder extends EntityViewBuilder {

  /**
   * The entity manager
   *
   * @var ConfigEntityStorage
   */
  protected $storageGroup;

  /**
   * The entity manager
   *
   * @var ConfigEntityStorage
   */
  protected $storageTypes;

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    /** @var ETreeInterface $entities */
    if (empty($entities)) {
      return;
    }

    parent::buildComponents($build, $entities, $displays, $view_mode);

    foreach ($entities as $id => $entity) {
      $bundle = $entity->bundle();
      $display = $displays[$bundle];

      if ($display->getComponent('links')) {
        $build[$id]['links'] = [
          '#lazy_builder' => [
            get_called_class() . '::renderLinks',
            [
              $entity->id(),
              $view_mode,
              $entity->language()->getId(),
              !empty($entity->in_preview),
              $entity->isDefaultRevision() ? NULL : $entity->getLoadedRevisionId(),
            ],
          ],
        ];
      }
    }
  }

  protected function getStorageTypes() {
    if (empty($this->storageTypes)) {
      $this->storageTypes = $this->entityManager->getStorage('etree_type');
    }
    return $this->storageTypes;
  }

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $defaults = parent::getBuildDefaults($entity, $view_mode);

    // Don't cache ETree that are in 'preview' mode.
    if (isset($defaults['#cache']) && isset($entity->in_preview)) {
      unset($defaults['#cache']);
    }

    return $defaults;
  }

  /**
   * #lazy_builder callback; builds a ETree's links.
   *
   * @param string $etree_entity_id
   *   The etree entity ID.
   * @param string $view_mode
   *   The view mode in which the etree entity is being viewed.
   * @param string $langcode
   *   The language in which the etree entity is being viewed.
   * @param bool $is_in_preview
   *   Whether the etree is currently being previewed.
   * @param $revision_id
   *   (optional) The identifier of the etree revision to be loaded. If none
   *   is provided, the default revision will be loaded.
   *
   * @return array
   *   A renderable array representing the etree links.
   */
  public static function renderLinks($etree_entity_id, $view_mode, $langcode, $is_in_preview, $revision_id = NULL) {
    $links = [
      '#theme' => 'links__etree',
      '#pre_render' => ['drupal_pre_render_links'],
      '#attributes' => ['class' => ['links', 'inline']],
    ];

    if (!$is_in_preview) {
      $storage = \Drupal::entityTypeManager()->getStorage('etree');
      /** @var \Drupal\etree\Entity\ETreeInterface $revision */
      $revision = !isset($revision_id) ? $storage->load($etree_entity_id) : $storage->loadRevision($revision_id);
      $entity = $revision->getTranslation($langcode);
      $links['etree'] = static::buildLinks($entity, $view_mode);

      // Allow other modules to alter the etree links.
      $hook_context = [
        'view_mode' => $view_mode,
        'langcode' => $langcode,
      ];
      \Drupal::moduleHandler()->alter('etree_links', $links, $entity, $hook_context);
    }
    return $links;
  }

  /**
   * Build the default links (Read more) for a etree.
   *
   * @param ETreeInterface $entity
   *   The etree object.
   * @param string $view_mode
   *   A view mode identifier.
   *
   * @return array
   *   An array that can be processed by drupal_pre_render_links().
   */
  protected static function buildLinks(ETreeInterface $entity, $view_mode) {
    return $entity->buildLinks($view_mode);
  }

}
