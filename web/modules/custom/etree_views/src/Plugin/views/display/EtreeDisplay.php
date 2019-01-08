<?php

namespace Drupal\etree_views\Plugin\views\display;

use Drupal\views\Plugin\views\display\Page;

/**
 * The plugin that handles a full page.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "etree",
 *   title = @Translation("ETree"),
 *   help = @Translation("Display the etree view as a page, with a URL and menu links."),
 *   uses_menu_links = TRUE,
 *   uses_route = TRUE,
 *   contextual_links_locations = {"etree"},
 *   admin = @Translation("ETree")
 * )
 */
class EtreeDisplay extends Page {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $element = parent::render();
    //    if ($handler = $this->getHandler('field', 'etree_link_collection_preview')) {
    //      if (empty($element['#sidebar'])) {
    //        $element['#sidebar'] = ['#type' => 'details', '#title' => 'Sidebar', '#open' => TRUE];
    //      }

    /** @var \Drupal\etree\Plugin\views\field\ETreeLinkCollectionPreview $handler */
    //    }

    return $element;
  }


  public function themeFunctions() {
    return 'views_view_etree';
  }

  public function defaultableSections($section = NULL) {
    $sections = parent::defaultableSections();

    $sections['sidebar'] = ['sidebar'];

    if ($section) {
      if (!empty($sections[$section])) {
        return $sections[$section];
      }
    }
    else {
      return $sections;
    }
  }


}
