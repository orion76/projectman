<?php

/**
 * @file
 * Contains enumerate.page.inc.
 *
 * Page callback for Enumerate entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Enumerate templates.
 *
 * Default template: enumerate.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_enumerate(array &$variables) {
  // Fetch EnumerateEntity Entity Object.
  $enumerate = $variables['elements']['#enumerate'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
