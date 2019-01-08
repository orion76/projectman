<?php


namespace Drupal\etree_group;


use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\views\Entity\View;

class EtreeGroupHelper {

  private $entityTypeManager;

  private $storageViews;

  private $storageGroup;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getGroupViews() {
    $view_ids = [];
    foreach ($this->getStorageGroup()->loadMultiple() as $group) {
      /** @var \Drupal\etree_group\Entity\ETreeGroupInterface $group */
      $group_views = [];
      foreach ($group->getCollectionViews() as $data) {

        /** @var View $view */
        $view = $this->getStorageViews()->load($data['view_id']);
        $data['path'] = $this->getViewPath($view, $data['display_id']);
        $group_views[] = $data;
      }
      $view_ids[$group->id()] = $group_views;
    }
    return $view_ids;
  }

  protected function getViewPath(View $view, $display_id) {
    $display = $view->getDisplay($display_id);
    return $display['display_options']['path'];

  }


  /**
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getStorageViews() {
    if (empty($this->storageViews)) {
      $this->storageViews = $this->entityTypeManager->getStorage('view');
    }

    return $this->storageViews;
  }


  protected function getStorageGroup() {
    if (empty($this->storageGroup)) {
      $this->storageGroup = $this->entityTypeManager->getStorage('etree_group');
    }
    return $this->storageGroup;
  }

}