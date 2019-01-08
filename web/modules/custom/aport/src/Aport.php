<?php


namespace Drupal\aport;


use Drupal\aport\destination\AportDestinationInterface;
use Drupal\aport\idMap\AportIdMapInterface;
use Drupal\aport\map\AportMapInterface;
use Drupal\aport\parser\AportParserInterface;
use Drupal\aport\process\AportProcessInterface;
use Drupal\aport\report\AportReportInterface;
use Drupal\aport\source\AportSourceInterface;

class Aport {

  protected $source;

  /**
   * @var \Drupal\aport\parser\AportParserInterface
   */
  private $parser;

  /**
   * @var \Drupal\aport\process\AportProcessInterface
   */
  private $process;

  /**
   * @var \Drupal\aport\map\AportMapInterface
   */
  private $map;

  /**
   * @var \Drupal\aport\idMap\AportIdMapInterface
   */
  private $idMap;

  /**
   * @var \Drupal\aport\destination\AportDestinationInterface
   */
  private $destination;

  /**
   * @var \Drupal\aport\report\AportReportInterface
   */
  private $report;

  public function __construct(AportSourceInterface $source,
                              AportParserInterface $parser,
                              AportProcessInterface $process,
                              AportMapInterface $map,
                              AportIdMapInterface $idMap,
                              AportDestinationInterface $destination,
                              AportReportInterface $report) {
    $this->source = $source;
    $this->parser = $parser;
    $this->process = $process;
    $this->map = $map;
    $this->idMap = $idMap;
    $this->destination = $destination;
    $this->report = $report;
  }

  public function aport() {
    foreach ($this->source as $item) {
      $parsed = $this->parser->parse($item);
      $processed = $this->process->process($parsed);
      $values = $this->map->map($processed);
      $this->idMap->save($values);
      $this->destination->save($values);

    }
  }
}