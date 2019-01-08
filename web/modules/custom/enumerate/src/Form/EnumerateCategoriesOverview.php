<?php


namespace Drupal\enumerate\Form;


use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\Form\OverviewTerms;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\VocabularyInterface;

class EnumerateCategoriesOverview extends OverviewTerms {

  /**
   * Form constructor.
   *
   * Display a tree of all the terms in a vocabulary, with options to edit
   * each one. The form is made drag and drop by the theme function.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\taxonomy\VocabularyInterface $taxonomy_vocabulary
   *   The vocabulary to display the overview form for.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, VocabularyInterface $taxonomy_vocabulary = NULL) {
    // @todo Remove global variables when https://www.drupal.org/node/2044435 is
    //   in.
    global $pager_page_array, $pager_total, $pager_total_items;

    $form_state->set(['taxonomy', 'vocabulary'], $taxonomy_vocabulary);
    $parent_fields = FALSE;

    $page = $this->getRequest()->query->get('page') ?: 0;
    // Number of terms per page.
    $page_increment = $this->config('taxonomy.settings')->get('terms_per_page_admin');
    // Elements shown on this page.
    $page_entries = 0;
    // Elements at the root level before this page.
    $before_entries = 0;
    // Elements at the root level after this page.
    $after_entries = 0;
    // Elements at the root level on this page.
    $root_entries = 0;

    // Terms from previous and next pages are shown if the term tree would have
    // been cut in the middle. Keep track of how many extra terms we show on
    // each page of terms.
    $back_step = NULL;
    $forward_step = 0;

    // An array of the terms to be displayed on this page.
    $current_page = [];

    $delta = 0;
    $term_deltas = [];
    $tree = $this->storageController->loadTree($taxonomy_vocabulary->id(), 0, NULL, TRUE);
    $tree_index = 0;
    do {
      // In case this tree is completely empty.
      if (empty($tree[$tree_index])) {
        break;
      }
      $delta++;
      // Count entries before the current page.
      if ($page && ($page * $page_increment) > $before_entries && !isset($back_step)) {
        $before_entries++;
        continue;
      }
      // Count entries after the current page.
      elseif ($page_entries > $page_increment && isset($complete_tree)) {
        $after_entries++;
        continue;
      }

      // Do not let a term start the page that is not at the root.
      $term = $tree[$tree_index];
      if (isset($term->depth) && ($term->depth > 0) && !isset($back_step)) {
        $back_step = 0;
        while ($pterm = $tree[--$tree_index]) {
          $before_entries--;
          $back_step++;
          if ($pterm->depth == 0) {
            $tree_index--;
            // Jump back to the start of the root level parent.
            continue 2;
          }
        }
      }
      $back_step = isset($back_step) ? $back_step : 0;

      // Continue rendering the tree until we reach the a new root item.
      if ($page_entries >= $page_increment + $back_step + 1 && $term->depth == 0 && $root_entries > 1) {
        $complete_tree = TRUE;
        // This new item at the root level is the first item on the next page.
        $after_entries++;
        continue;
      }
      if ($page_entries >= $page_increment + $back_step) {
        $forward_step++;
      }

      // Finally, if we've gotten down this far, we're rendering a term on this
      // page.
      $page_entries++;
      $term_deltas[$term->id()] = isset($term_deltas[$term->id()]) ? $term_deltas[$term->id()] + 1 : 0;
      $key = 'tid:' . $term->id() . ':' . $term_deltas[$term->id()];

      // Keep track of the first term displayed on this page.
      if ($page_entries == 1) {
        $form['#first_tid'] = $term->id();
      }
      // Keep a variable to make sure at least 2 root elements are displayed.
      if ($term->parents[0] == 0) {
        $root_entries++;
      }
      $current_page[$key] = $term;
    } while (isset($tree[++$tree_index]));

    // Because we didn't use a pager query, set the necessary pager variables.
    $total_entries = $before_entries + $page_entries + $after_entries;
    $pager_total_items[0] = $total_entries;
    $pager_page_array[0] = $page;
    $pager_total[0] = ceil($total_entries / $page_increment);

    // If this form was already submitted once, it's probably hit a validation
    // error. Ensure the form is rebuilt in the same order as the user
    // submitted.
    $user_input = $form_state->getUserInput();
    if (!empty($user_input)) {
      // Get the POST order.
      $order = array_flip(array_keys($user_input['terms']));
      // Update our form with the new order.
      $current_page = array_merge($order, $current_page);
      foreach ($current_page as $key => $term) {
        // Verify this is a term for the current page and set at the current
        // depth.
        if (is_array($user_input['terms'][$key]) && is_numeric($user_input['terms'][$key]['term']['tid'])) {
          $current_page[$key]->depth = $user_input['terms'][$key]['term']['depth'];
        }
        else {
          unset($current_page[$key]);
        }
      }
    }

    $errors = $form_state->getErrors();
    $row_position = 0;
    // Build the actual form.
    $access_control_handler = $this->entityManager->getAccessControlHandler('taxonomy_term');
    $create_access = $access_control_handler->createAccess($taxonomy_vocabulary->id(), NULL, [], TRUE);
    if ($create_access->isAllowed()) {
      $empty = $this->t('No terms available. <a href=":link">Add term</a>.', [
        ':link' => Url::fromRoute('entity.taxonomy_term.add_form', ['taxonomy_vocabulary' => $taxonomy_vocabulary->id()])
          ->toString(),
      ]);
    }
    else {
      $empty = $this->t('No terms available.');
    }
    $form['terms'] = [
      '#type' => 'table',
      '#empty' => $empty,
      '#header' => [
        'term' => $this->t('Name'),
        'operations' => $this->t('Operations'),
        'weight' => $this->t('Weight'),
      ],
      '#attributes' => [
        'id' => 'taxonomy',
      ],
    ];
    $this->renderer->addCacheableDependency($form['terms'], $create_access);

    // Only allow access to changing weights if the user has update access for
    // all terms.
    $change_weight_access = AccessResult::allowed();
    foreach ($current_page as $key => $term) {

      $this->addTerm($form, $key, $term);


      if ($taxonomy_vocabulary->getHierarchy() != VocabularyInterface::HIERARCHY_MULTIPLE && count($tree) > 1) {
        $parent_fields = TRUE;

        $this->addHierarchy($form, $key, $term);

      }
      $update_access = $term->access('update', NULL, TRUE);
      $change_weight_access = $change_weight_access->andIf($update_access);

      if ($update_access->isAllowed()) {
        $form['terms'][$key]['weight'] = [
          '#type' => 'weight',
          '#delta' => $delta,
          '#title' => $this->t('Weight for added term'),
          '#title_display' => 'invisible',
          '#default_value' => $term->getWeight(),
          '#attributes' => ['class' => ['term-weight']],
        ];
      }

      if ($operations = $this->termListBuilder->getOperations($term)) {
        $form['terms'][$key]['operations'] = [
          '#type' => 'operations',
          '#links' => $operations,
        ];
      }

      $form['terms'][$key]['#attributes']['class'] = [];
      if ($parent_fields) {
        $form['terms'][$key]['#attributes']['class'][] = 'draggable';
      }

      // Add classes that mark which terms belong to previous and next pages.
      if ($row_position < $back_step || $row_position >= $page_entries - $forward_step) {
        $form['terms'][$key]['#attributes']['class'][] = 'taxonomy-term-preview';
      }

      if ($row_position !== 0 && $row_position !== count($tree) - 1) {
        if ($row_position == $back_step - 1 || $row_position == $page_entries - $forward_step - 1) {
          $form['terms'][$key]['#attributes']['class'][] = 'taxonomy-term-divider-top';
        }
        elseif ($row_position == $back_step || $row_position == $page_entries - $forward_step) {
          $form['terms'][$key]['#attributes']['class'][] = 'taxonomy-term-divider-bottom';
        }
      }

      // Add an error class if this row contains a form error.
      foreach ($errors as $error_key => $error) {
        if (strpos($error_key, $key) === 0) {
          $form['terms'][$key]['#attributes']['class'][] = 'error';
        }
      }
      $row_position++;
    }

    $this->renderer->addCacheableDependency($form['terms'], $change_weight_access);

    $this->addTabledrag($form, $change_weight_access, $parent_fields, $forward_step, $back_step);


    $this->addSubmit($form, $taxonomy_vocabulary, $tree, $change_weight_access);


    $form['pager_pager'] = ['#type' => 'pager'];
    return $form;
  }

  protected function addHierarchy(&$form, $key, TermInterface $term) {

    $form['terms'][$key]['term']['tid'] = [
      '#type' => 'hidden',
      '#value' => $term->id(),
      '#attributes' => [
        'class' => ['term-id'],
      ],
    ];
    $form['terms'][$key]['term']['parent'] = [
      '#type' => 'hidden',
      // Yes, default_value on a hidden. It needs to be changeable by the
      // javascript.
      '#default_value' => $term->parents[0],
      '#attributes' => [
        'class' => ['term-parent'],
      ],
    ];
    $form['terms'][$key]['term']['depth'] = [
      '#type' => 'hidden',
      // Same as above, the depth is modified by javascript, so it's a
      // default_value.
      '#default_value' => $term->depth,
      '#attributes' => [
        'class' => ['term-depth'],
      ],
    ];
  }

  protected function addTerm(&$form, $key, $term) {
    $form['terms'][$key] = [
      'term' => [],
      'operations' => [],
      'weight' => [],
    ];
    /** @var $term \Drupal\Core\Entity\EntityInterface */
    $term = $this->entityManager->getTranslationFromContext($term);
    $form['terms'][$key]['#term'] = $term;
    $indentation = [];
    if (isset($term->depth) && $term->depth > 0) {
      $indentation = [
        '#theme' => 'indentation',
        '#size' => $term->depth,
      ];
    }
    $form['terms'][$key]['term'] = [
      '#prefix' => !empty($indentation) ? \Drupal::service('renderer')->render($indentation) : '',
      '#type' => 'link',
      '#title' => $term->getName(),
      '#url' => $term->urlInfo(),
    ];
  }

  protected function addTabledrag(&$form, AccessResult $change_weight_access, boolean $parent_fields, integer $forward_step, integer $back_step) {
    if ($change_weight_access->isAllowed()) {
      if ($parent_fields) {
        $form['terms']['#tabledrag'][] = [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'term-parent',
          'subgroup' => 'term-parent',
          'source' => 'term-id',
          'hidden' => FALSE,
        ];
        $form['terms']['#tabledrag'][] = [
          'action' => 'depth',
          'relationship' => 'group',
          'group' => 'term-depth',
          'hidden' => FALSE,
        ];
        $form['terms']['#attached']['library'][] = 'taxonomy/drupal.taxonomy';
        $form['terms']['#attached']['drupalSettings']['taxonomy'] = [
          'backStep' => $back_step,
          'forwardStep' => $forward_step,
        ];
      }
      $form['terms']['#tabledrag'][] = [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'term-weight',
      ];
    }

  }

  protected function addSubmit(&$form, VocabularyInterface $taxonomy_vocabulary, array $tree, AccessResult $change_weight_access) {
    if (($taxonomy_vocabulary->getHierarchy() !== VocabularyInterface::HIERARCHY_MULTIPLE && count($tree) > 1) &&
      $change_weight_access->isAllowed()) {
      $form['actions'] = ['#type' => 'actions', '#tree' => FALSE];
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
        '#button_type' => 'primary',
      ];
      $form['actions']['reset_alphabetical'] = [
        '#type' => 'submit',
        '#submit' => ['::submitReset'],
        '#value' => $this->t('Reset to alphabetical'),
      ];
    }
  }

}