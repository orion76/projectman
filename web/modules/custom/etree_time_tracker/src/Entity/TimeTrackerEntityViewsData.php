<?php

namespace Drupal\etree_time_tracker\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Time tracker entity entities.
 */
class TimeTrackerEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
