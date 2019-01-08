<?php


namespace Drupal\etree_views\Plugin\views\style;

use Drupal\views\Plugin\views\style\Table;

/**
 * Style plugin to render a list of years and months
 * in reverse chronological order linked to content.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "ETree",
 *   title = @Translation("ETree"),
 *   help = @Translation("ETree items hierarchy."),
 *   theme = "views_view_etree_style",
 *   display_types = {"normal"}
 * )
 */
class ETreeViewsStylePlugin extends Table {

  /**
   * Group records as needed for rendering.
   *
   * @param $records
   *   An array of records from the view to group.
   * @param $groupings
   *   An array of grouping instructions on which fields to group. If empty, the
   *   result set will be given a single group with an empty string as a label.
   * @param $group_rendered
   *   Boolean value whether to use the rendered or the raw field value for
   *   grouping. If set to NULL the return is structured as before
   *   Views 7.x-3.0-rc2. After Views 7.x-3.0 this boolean is only used if
   *   $groupings is an old-style string or if the rendered option is missing
   *   for a grouping instruction.
   *
   * @return
   *   The grouped record set.
   *   A nested set structure is generated if multiple grouping fields are used.
   *
   * @code
   *   array(
   *     'grouping_field_1:grouping_1' => array(
   *       'group' => 'grouping_field_1:content_1',
   *       'level' => 0,
   *       'rows' => array(
   *         'grouping_field_2:grouping_a' => array(
   *           'group' => 'grouping_field_2:content_a',
   *           'level' => 1,
   *           'rows' => array(
   *             $row_index_1 => $row_1,
   *             $row_index_2 => $row_2,
   *             // ...
   *           )
   *         ),
   *       ),
   *     ),
   *     'grouping_field_1:grouping_2' => array(
   *       // ...
   *     ),
   *   )
   * @endcode
   */
  public function renderGrouping($records, $groupings = [], $group_rendered = NULL) {
    // This is for backward compatibility, when $groupings was a string
    // containing the ID of a single field.
    if (is_string($groupings)) {
      $rendered = $group_rendered === NULL ? TRUE : $group_rendered;
      $groupings = [['field' => $groupings, 'rendered' => $rendered]];
    }

    // Make sure fields are rendered
    $this->renderFields($this->view->result);
    $sets = [];
    if ($groupings) {
      foreach ($records as $index => $row) {
        // Iterate through configured grouping fields to determine the
        // hierarchically positioned set where the current row belongs to.
        // While iterating, parent groups, that do not exist yet, are added.
        $set = &$sets;
        foreach ($groupings as $level => $info) {
          $field = $info['field'];
          $rendered = isset($info['rendered']) ? $info['rendered'] : $group_rendered;
          $rendered_strip = isset($info['rendered_strip']) ? $info['rendered_strip'] : FALSE;
          $grouping = '';
          $group_content = '';
          // Group on the rendered version of the field, not the raw.  That way,
          // we can control any special formatting of the grouping field through
          // the admin or theme layer or anywhere else we'd like.
          if (isset($this->view->field[$field])) {
            $group_content = $this->getField($index, $field);
            if ($this->view->field[$field]->options['label']) {
              $group_content = $this->view->field[$field]->options['label'] . ': ' . $group_content;
            }
            if ($rendered) {
              $grouping = (string) $group_content;
              if ($rendered_strip) {
                $group_content = $grouping = strip_tags(htmlspecialchars_decode($group_content));
              }
            }
            else {
              $grouping = $this->getFieldValue($index, $field);
              // Not all field handlers return a scalar value,
              // e.g. views_handler_field_field.
              if (!is_scalar($grouping)) {
                $grouping = hash('sha256', serialize($grouping));
              }
            }
          }

          // Create the group if it does not exist yet.
          if (empty($set[$grouping])) {
            $set[$grouping]['group'] = $group_content;
            $set[$grouping]['level'] = $level;
            $set[$grouping]['rows'] = [];
          }

          // Move the set reference into the row set of the group we just determined.
          $set = &$set[$grouping]['rows'];
        }
        // Add the row to the hierarchically positioned row set we just determined.
        $set[$index] = $row;
      }
    }
    else {
      // Create a single group with an empty grouping field.
      $sets[''] = [
        'group' => '',
        'rows' => $records,
      ];
    }

    // If this parameter isn't explicitly set, modify the output to be fully
    // backward compatible to code before Views 7.x-3.0-rc2.
    // @TODO Remove this as soon as possible e.g. October 2020
    if ($group_rendered === NULL) {
      $old_style_sets = [];
      foreach ($sets as $group) {
        $old_style_sets[$group['group']] = $group['rows'];
      }
      $sets = $old_style_sets;
    }

    return $sets;
  }
}