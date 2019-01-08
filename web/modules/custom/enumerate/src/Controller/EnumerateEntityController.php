<?php

namespace Drupal\enumerate\Controller;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\enumerate\Entity\EnumerateEntityInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Class EnumerateEntityController.
 *
 *  Returns responses for Enumerate routes.
 */
class EnumerateEntityController extends ControllerBase implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Displays a Enumerate  revision.
   *
   * @param int $enumerate_revision
   *   The Enumerate  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function listCategory() {

    $page[] = $this->getVocabularyTree();
    $page[] = views_embed_view('enumerate');


    return $page;
  }


  protected function getVocabularyTree() {
    $vocabulary = 'enumerate';
    try {
      /** @var \Drupal\taxonomy\TermStorageInterface $storage */
      $storage = $this->entityTypeManager()->getStorage('taxonomy_term');
      $terms = $storage->loadTree($vocabulary, 0, NULL, TRUE);

    } catch (InvalidPluginDefinitionException $e) {
    } catch (PluginNotFoundException $e) {
    }

    $tree = $this->getTree($terms);
    $items = [];
    foreach ($tree as &$data) {
      if ($this->hasChild($data)) {
        $data += ['#title' => $data['#term_name'], '#theme' => 'item_list'];
      }
      else {
        $data += [
          '#type' => 'link',
          '#title' => $data['#term_name'],
          '#url' => Url::fromRoute('entity.enumerate.collection', [], ['query' => ['category' => $data['#term_id']]]),
          '#attributes' => $this->getCategoryAttributes($data['#term_id']),
        ];
      }

      if (!$data['#term_parent']) {
        $items[] = &$data;
      }

      unset($data['#term_id']);
      unset($data['#term_name']);
      unset($data['#term_parent']);
    }
    $n = 0;
    //    return (, ['items' => $items]);
    $elements = [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#title' => $this->t('Categories'),
      '#items' => $items,
      '#attributes' => ['class' => ['menu-block']],
    ];
    //    \Drupal::service('renderer')->render($elements);
    return $elements;
  }

  protected function getCategoryAttributes($category_id) {
    $attributes = ['class' => []];
    if (\Drupal::request()->query->get('category') === $category_id) {
      $attributes['class'][] = 'is-active';
    }
    return $attributes;
  }

  protected function hasChild($item) {
    return isset($item['#items']) && !empty($item['#items']);
  }

  protected function addChild(&$tree, $parent_id, &$child) {
    if (!isset($tree[$parent_id])) {
      $tree[$parent_id] = ['#items' => []];
    }

    $tree[$parent_id]['#items'][$child['#term_id']] = &$child;
  }

  protected function createItem($id, $name, $parent) {
    return [
      '#term_id' => $id,
      '#term_name' => $name,
      '#term_parent' => $parent
      // 'url' => Link::createFromRoute($name, $route_name, [], ['query' => ['category' => $id]]),
    ];
  }

  /**
   * @param $terms array Term
   */

  protected function getTree($terms) {
    $tree = [];
    foreach ($terms as $term) {
      /** @var Term $term */
      $id = $term->id();
      $name = $term->getName();
      $parent = reset($term->parents);
      if (!isset($tree[$id])) {
        $tree[$id] = [];
      }

      $tree[$id] += $this->createItem($id, $name, $parent);
      if ($parent) {
        $this->addChild($tree, $parent, $tree[$id]);
      }
    }
    return $tree;
  }

  /**
   * Displays a Enumerate  revision.
   *
   * @param int $enumerate_revision
   *   The Enumerate  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($enumerate_revision) {
    $enumerate = $this->entityManager()->getStorage('enumerate')->loadRevision($enumerate_revision);
    $view_builder = $this->entityManager()->getViewBuilder('enumerate');

    return $view_builder->view($enumerate);
  }

  /**
   * Page title callback for a Enumerate  revision.
   *
   * @param int $enumerate_revision
   *   The Enumerate  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($enumerate_revision) {
    $enumerate = $this->entityManager()->getStorage('enumerate')->loadRevision($enumerate_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $enumerate->label(),
      '%date' => format_date($enumerate->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Enumerate .
   *
   * @param \Drupal\enumerate\Entity\EnumerateEntityInterface $enumerate
   *   A Enumerate  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(EnumerateEntityInterface $enumerate) {
    $account = $this->currentUser();
    $langcode = $enumerate->language()->getId();
    $langname = $enumerate->language()->getName();
    $languages = $enumerate->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $enumerate_storage = $this->entityManager()->getStorage('enumerate');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $enumerate->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $enumerate->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all enumerate revisions") || $account->hasPermission('administer enumerate entities')));
    $delete_permission = (($account->hasPermission("delete all enumerate revisions") || $account->hasPermission('administer enumerate entities')));

    $rows = [];

    $vids = $enumerate_storage->revisionIds($enumerate);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\enumerate\EnumerateEntityInterface $revision */
      $revision = $enumerate_storage->loadRevision($vid);
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
        if ($vid != $enumerate->getRevisionId()) {
          $link = $this->l($date, new Url('entity.enumerate.revision', [
            'enumerate' => $enumerate->id(),
            'enumerate_revision' => $vid,
          ]));
        }
        else {
          $link = $enumerate->link($date);
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
                Url::fromRoute('entity.enumerate.translation_revert', [
                  'enumerate' => $enumerate->id(),
                  'enumerate_revision' => $vid,
                  'langcode' => $langcode,
                ]) :
                Url::fromRoute('entity.enumerate.revision_revert', [
                  'enumerate' => $enumerate->id(),
                  'enumerate_revision' => $vid,
                ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.enumerate.revision_delete', [
                'enumerate' => $enumerate->id(),
                'enumerate_revision' => $vid,
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

    $build['enumerate_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
