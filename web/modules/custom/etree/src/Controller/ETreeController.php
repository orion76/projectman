<?php

namespace Drupal\etree\Controller;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\Core\Url;
use Drupal\etree\Entity\ETreeAjaxOverview;
use Drupal\etree\Entity\ETreeInterface;
use function views_embed_view;

/**
 * Class ETreeController.
 *
 *  Returns responses for ETree routes.
 */
class ETreeController extends ControllerBase implements ContainerInjectionInterface {


  public function collectionList() {
    $page = [];

    $page[] = views_embed_view('etree');


    return $page;
  }

  public function bundleList($bundle) {
    return ['#markup' => "List of {$bundle}"];
  }


  /**
   * Displays a ETree  revision.
   *
   * @param int $etree_revision
   *   The ETree  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($etree_revision) {
    $etree = $this->entityManager()->getStorage('etree')->loadRevision($etree_revision);
    $view_builder = $this->entityManager()->getViewBuilder('etree');

    return $view_builder->view($etree);
  }

  /**
   * Page title callback for a ETree  revision.
   *
   * @param int $etree_revision
   *   The ETree  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($etree_revision) {
    $etree = $this->entityManager()->getStorage('etree')->loadRevision($etree_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $etree->label(),
      '%date' => format_date($etree->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a ETree .
   *
   * @param \Drupal\etree\Entity\ETreeInterface $etree
   *   A ETree  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ETreeInterface $etree) {
    $account = $this->currentUser();
    $langcode = $etree->language()->getId();
    $langname = $etree->language()->getName();
    $languages = $etree->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $etree_storage = $this->entityManager()->getStorage('etree');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $etree->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $etree->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all etree revisions") || $account->hasPermission('administer etree entities')));
    $delete_permission = (($account->hasPermission("delete all etree revisions") || $account->hasPermission('administer etree entities')));

    $rows = [];

    $vids = $etree_storage->revisionIds($etree);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\etree\ETreeInterface $revision */
      $revision = $etree_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)
          ->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $etree->getRevisionId()) {
          $link = $this->l($date, new Url('entity.etree.revision', [
            'etree' => $etree->id(),
            'etree_revision' => $vid,
          ]));
        }
        else {
          $link = $etree->link($date);
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
                Url::fromRoute('entity.etree.translation_revert', [
                  'etree' => $etree->id(),
                  'etree_revision' => $vid,
                  'langcode' => $langcode,
                ]) :
                Url::fromRoute('entity.etree.revision_revert', ['etree' => $etree->id(), 'etree_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.etree.revision_delete', [
                'etree' => $etree->id(),
                'etree_revision' => $vid,
              ]),
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

    $build['etree_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
