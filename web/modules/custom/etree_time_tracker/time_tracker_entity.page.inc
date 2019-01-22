<?php

/**
 * @file
 * Contains time_tracker_entity.page.inc.
 *
 * Page callback for Time tracker entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Time tracker entity templates.
 *
 * Default template: time_tracker_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_time_tracker_entity(array &$variables) {
  // Fetch TimeTrackerEntity Entity Object.
  $time_tracker_entity = $variables['elements']['#time_tracker_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
