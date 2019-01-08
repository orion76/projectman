<?php

namespace Drupal\etree_links\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a link render element.
 *
 * Properties:
 * - #name: The button name for class generation.
 * - #url: \Drupal\Core\Url object containing URL information pointing to a
 *   internal or external link. See \Drupal\Core\Utility\LinkGeneratorInterface.
 *
 *
 * @RenderElement("icon_buttons")
 */
class IconButtons extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#pre_render' => [
        [$class, 'preRenderLink'],
      ],
    ];
  }

  /**
   * Pre-render callback: Renders a link into #markup.
   *
   * Doing so during pre_render gives modules a chance to alter the link parts.
   *
   * @param array $element
   *   A structured array whose keys form the arguments to
   *   \Drupal\Core\Utility\LinkGeneratorInterface::generate():
   *   - #title: The link text.
   *   - #url: The URL info either pointing to a route or a non routed path.
   *   - #options: (optional) An array of options to pass to the link generator.
   *
   * @return array
   *   The passed-in element containing a rendered link in '#markup'.
   */
  public static function preRenderLink($element) {

    $element += ['#attributes' => []];
    $element['#attributes'] += ['class' => []];

    $element['#attributes']['class'][] = 'menu icon-bar';


    $element['#theme'] = 'item_list';
    $element['#items'] = [];
    foreach ($element['#links'] as $link) {
      $link['#type'] = 'icon_button';
      $link['#wrapper_attributes'] = ['class' => ['icon-button-item']];
      $element['#items'][] = $link;
    }
    return $element;
  }

}
