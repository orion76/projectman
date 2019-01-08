<?php

/**
 * @file
 * Contains enumerate_item.page.inc.
 *
 * Page callback for Enumerate item entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Enumerate item templates.
 *
 * Default template: enumerate_item.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_enumerate_item(array &$variables) {
  // Fetch EnumerateItemEntity Entity Object.
  $enumerate_item = $variables['elements']['#enumerate_item'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
