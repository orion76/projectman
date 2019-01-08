<?php

namespace Drupal\etree;

use Drupal\Core\Config\Config;


/**
 * Defines the storage handler class for ETree entities.
 *
 * This extends the base storage class, adding required special handling for
 * ETree entities.
 *
 * @ingroup etree
 */
interface ETreeTypeStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function importDelete($name, Config $new_config, Config $old_config);

  public function loadRootItems();
}