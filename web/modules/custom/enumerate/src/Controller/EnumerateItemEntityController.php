<?php

namespace Drupal\enumerate\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\enumerate\Entity\EnumerateItemEntityInterface;

/**
 * Class EnumerateItemEntityController.
 *
 *  Returns responses for Enumerate item routes.
 */
class EnumerateItemEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Enumerate item  revision.
   *
   * @param int $enumerate_item_revision
   *   The Enumerate item  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($enumerate_item_revision) {
    $enumerate_item = $this->entityManager()->getStorage('enumerate_item')->loadRevision($enumerate_item_revision);
    $view_builder = $this->entityManager()->getViewBuilder('enumerate_item');

    return $view_builder->view($enumerate_item);
  }

  /**
   * Page title callback for a Enumerate item  revision.
   *
   * @param int $enumerate_item_revision
   *   The Enumerate item  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($enumerate_item_revision) {
    $enumerate_item = $this->entityManager()->getStorage('enumerate_item')->loadRevision($enumerate_item_revision);
    return $this->t('Revision of %title from %date', ['%title' => $enumerate_item->label(), '%date' => format_date($enumerate_item->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Enumerate item .
   *
   * @param \Drupal\enumerate\Entity\EnumerateItemEntityInterface $enumerate_item
   *   A Enumerate item  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(EnumerateItemEntityInterface $enumerate_item) {
    $account = $this->currentUser();
    $langcode = $enumerate_item->language()->getId();
    $langname = $enumerate_item->language()->getName();
    $languages = $enumerate_item->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $enumerate_item_storage = $this->entityManager()->getStorage('enumerate_item');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $enumerate_item->label()]) : $this->t('Revisions for %title', ['%title' => $enumerate_item->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all enumerate item revisions") || $account->hasPermission('administer enumerate item entities')));
    $delete_permission = (($account->hasPermission("delete all enumerate item revisions") || $account->hasPermission('administer enumerate item entities')));

    $rows = [];

    $vids = $enumerate_item_storage->revisionIds($enumerate_item);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\enumerate\EnumerateItemEntityInterface $revision */
      $revision = $enumerate_item_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $enumerate_item->getRevisionId()) {
          $link = $this->l($date, new Url('entity.enumerate_item.revision', ['enumerate_item' => $enumerate_item->id(), 'enumerate_item_revision' => $vid]));
        }
        else {
          $link = $enumerate_item->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.enumerate_item.translation_revert', ['enumerate_item' => $enumerate_item->id(), 'enumerate_item_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.enumerate_item.revision_revert', ['enumerate_item' => $enumerate_item->id(), 'enumerate_item_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.enumerate_item.revision_delete', ['enumerate_item' => $enumerate_item->id(), 'enumerate_item_revision' => $vid]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['enumerate_item_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
