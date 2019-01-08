<?php

/**
 * @file
 * Contains etree.page.inc.
 *
 * Page callback for ETree entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for ETree templates.
 *
 * Default template: etree.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_etree(array &$variables) {
  // Fetch ETree Entity Object.
  $etree = $variables['elements']['#etree'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}

