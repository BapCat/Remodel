<?php namespace BapCat\Remodel;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class ProcessorWrapper {
  private $connection;
  private $processor;
  private $from_db;
  
  public function __construct(ConnectionInterface $connection, array $from_db) {
    $this->connection = $connection;
    $this->processor = $connection->getPostProcessor();
    $this->from_db = $from_db;
  }
  
  public function getOriginalProcessor() {
    return $this->processor;
  }
  
  public function processSelect(Builder $query, $results) {
    $results = $this->processor->processSelect($query, $results);
    
    if($query->aggregate !== null) {
      $value = $results[0]['aggregate'];
      
      return [['aggregate' => filter_var($value, FILTER_VALIDATE_INT) ? (int)$value : (float)$value]];
    } else {
      foreach($results as &$row) {
        $this->coerceDataTypesFromDatabase($row);
      }
      
      return $results;
    }
  }
  
  public function processInsertGetId(Builder $query, $sql, $values, $sequence = null) {
    return $this->processor->processInsertGetId($query, $sql, $values, $sequence);
  }
  
  public function processColumnListing($results) {
    return $this->processor->processColumnListing($results);
  }
  
  private function coerceDataTypesFromDatabase(array &$row) {
    foreach($row as $col => &$value) {
      switch($this->connection->meta[$col]) {
        case 'long':
        case 'integer':
          $value = (int)$value;
        break;
        
        case 'timestamp':
        case 'datetime':
          $value = strtotime($value);
        break;
      }
    }
  }
}
