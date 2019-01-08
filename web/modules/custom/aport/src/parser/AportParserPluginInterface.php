<?php


namespace Drupal\aport\parser;


interface AportParserPluginInterface {

  public function parse($data);
}