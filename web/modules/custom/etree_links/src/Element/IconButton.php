<?php

namespace Drupal\etree_links\Element;

use Drupal\Component\Utility\Html as HtmlUtility;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url as CoreUrl;

/**
 * Provides a link render element.
 *
 * Properties:
 * - #name: The button name for class generation.
 * - #url: \Drupal\Core\Url object containing URL information pointing to a
 *   internal or external link. See \Drupal\Core\Utility\LinkGeneratorInterface.
 *
 *
 * @RenderElement("icon_button")
 */
class IconButton extends RenderElement {

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
    // By default, link options to pass to the link generator are normally set
    // in #options.
    $element += ['#options' => [], '#description' => t('No description')];
    // However, within the scope of renderable elements, #attributes is a valid
    // way to specify attributes, too. Take them into account, but do not override
    // attributes from #options.
    if (isset($element['#attributes'])) {
      $element['#options'] += ['attributes' => []];
      $element['#options']['attributes'] += $element['#attributes'];
    }

    // This #pre_render callback can be invoked from inside or outside of a Form
    // API context, and depending on that, a HTML ID may be already set in
    // different locations. #options should have precedence over Form API's #id.
    // #attributes have been taken over into #options above already.
    if (isset($element['#options']['attributes']['id'])) {
      $element['#id'] = $element['#options']['attributes']['id'];
    }
    elseif (isset($element['#id'])) {
      $element['#options']['attributes']['id'] = $element['#id'];
    }

    // Conditionally invoke self::preRenderAjaxForm(), if #ajax is set.
    if (isset($element['#ajax']) && !isset($element['#ajax_processed'])) {
      // If no HTML ID was found above, automatically create one.
      if (!isset($element['#id'])) {
        $element['#id'] = $element['#options']['attributes']['id'] = HtmlUtility::getUniqueId('ajax-link');
      }
      $element = static::preRenderAjaxForm($element);
    }


    $element['#options']['attributes']['title'] = $element['#description'];
    $element['#options']['attributes']['class'][] = 'button';
    $element['#options']['attributes']['class'][] = "icon-button";

    //<i class="fas fa-stroopwafel"></i>

    if (isset($element['#name'])) {
      $element['#options']['attributes']['class'][] = $element['#name'];
    }

    if (isset($element['#style'])) {
      $element['#options']['attributes']['class'][] = "button-style-{$element['#style']}";
    }

    if ($element['#icon_only']) {
      $element['#options']['attributes']['class'][] = "icon";
      $element['#title'] = Markup::create("{$element['#icon']}");
    }
    else {
      $element['#options']['attributes']['class'][] = "icon-text";
      if (isset($element['#icon'])) {
        $element['#title'] = Markup::create("{$element['#icon']}{$element['#title']}");
      }
    }



    if (!empty($element['#url']) && $element['#url'] instanceof CoreUrl) {
      $options = NestedArray::mergeDeep($element['#url']->getOptions(), $element['#options']);
      /** @var \Drupal\Core\Utility\LinkGenerator $link_generator */
      $link_generator = \Drupal::service('link_generator');
      $generated_link = $link_generator->generate($element['#title'], $element['#url']->setOptions($options));
      $element['#markup'] = $generated_link;
      $generated_link->merge(BubbleableMetadata::createFromRenderArray($element))
        ->applyTo($element);
    }
    return $element;
  }

}
