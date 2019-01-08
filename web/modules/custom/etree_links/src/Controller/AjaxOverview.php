<?php


namespace Drupal\etree_links\Controller;


use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\etree_links\ETreeAjaxOverview;


class AjaxOverview {

  /**
   * @param $etree
   *
   */
  public function collectionOverview($etree) {
    try {
      return ETreeAjaxOverview::Response($etree);
    } catch (InvalidPluginDefinitionException $e) {
    } catch (PluginNotFoundException $e) {
    } catch (EntityMalformedException $e) {
    }
  }

}