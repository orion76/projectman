<?php

namespace Drupal\etree\exception;


use Drupal\Component\Plugin\Exception\ExceptionInterface;

/**
 * Generic Plugin exception class to be thrown when no more specific class
 * is applicable.
 */
class ETreeHierarchyDataException extends \Exception implements ExceptionInterface {

}
