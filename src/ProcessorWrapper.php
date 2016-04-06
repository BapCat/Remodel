<?php namespace BapCat\Remodel;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor;

class ProcessorWrapper extends Processor {
  private $processor;
  private $table;
  private $from_db;
  
  public function __construct(Processor $processor, $table, array $from_db) {
    $this->processor = $processor;
    $this->table = $table;
    $this->from_db = $from_db;
  }
  
  public function getOriginalProcessor() {
    return $this->processor;
  }
  
  public function processSelect(Builder $query, $results) {
    $results = $this->processor->processSelect($query, $results);
    
    $query->getConnection()->setQueryGrammar ($query->getConnection()->getQueryGrammar ()->getOriginalGrammar());
    $query->getConnection()->setPostProcessor($query->getConnection()->getPostProcessor()->getOriginalProcessor());
    
    if($query->aggregate !== null) {
      $value = $results[0]['aggregate'];
      
      return [['aggregate' => filter_var($value, FILTER_VALIDATE_INT) ? (int)$value : (float)$value]];
    } else {
      return array_map([$this, 'coerceDataTypesFromDatabase'], $results);
    }
  }
  
  public function processInsertGetId(Builder $query, $sql, $values, $sequence = null) {
    return $this->processor->processInsertGetId($query, $sql, $values, $sequence);
  }
  
  public function processColumnListing($results) {
    return $this->processor->processColumnListing($results);
  }
  
  private function coerceDataTypesFromDatabase(array $row) {
    $mapped = [];
    
    foreach($row as $col => $value) {
      if(array_key_exists($col, $this->from_db)) {
        switch($this->table->getColumn($col)->getType()->getName()) {
          case 'integer':
            $value = (int)$value;
          break;
          
          case 'timestamp':
          case 'datetime':
            $value = strtotime($value);
          break;
        }
        
        $mapped[$this->from_db[$col]] = $value;
      } else {
        $mapped[$col] = $value;
      }
    }
    
    return $mapped;
  }
}
