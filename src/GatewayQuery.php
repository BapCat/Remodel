<?php namespace BapCat\Remodel;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

class GatewayQuery extends Builder {
  private $table;
  private $from_db;
  private $to_db;
  private $virtual;
  
  public function __construct(ConnectionInterface $connection, $table, array $to_db, array $from_db, array $virtual) {
    parent::__construct($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
    $this->from($table);
    
    $this->table = $connection->getDoctrineSchemaManager()->listTableDetails($table);
    
    $this->to_db   = $to_db;
    $this->from_db = $from_db;
    $this->virtual = $virtual;
  }
  
  public function get($columns = ['*']) {
    if(!is_array($columns)) {
      $columns = [$columns];
    }
    
    if($this->columns !== null) {
      $this->columns = $this->remapColumns($this->columns);
    }
    
    $columns = $this->remapColumns($columns);
    
    $this->remapWheres();
    
    $rows = parent::get($columns);
    
    $mapped = [];
    
    foreach($rows as $row) {
      $mapped[] = $this->coerceDataTypesFromDatabase($row);
    }
    
    return $mapped;
  }
  
  public function update(array $values) {
    //@TODO: Is this fast enough?
    $values = array_combine($this->remapColumns(array_keys($values)), array_values($values));
    
    $this->remapWheres();
    
    $values = $this->coerceDataTypesToDatabase($values);
    
    return parent::update($values);
  }
  
  private function remapColumns(array $columns) {
    foreach($columns as &$column) {
      if(array_key_exists($column, $this->to_db)) {
        $column = $this->to_db[$column];
      } elseif(array_key_exists($column, $this->virtual)) {
        $mapping = $this->virtual[$column];
        
        if(!is_array($mapping)) {
          $column = $mapping;
        } else {
          $concat = 'CONCAT(';
          
          foreach($mapping as $part) {
            $concat .= $part . ',';
          }
          
          $concat = substr($concat, 0, strlen($concat) - 1) . ") AS $column";
          $column = $this->raw($concat);
        }
      }
    }
    
    return $columns;
  }
  
  private function remapWheres() {
    if($this->wheres !== null) {
      foreach($this->wheres as &$where) {
        if(array_key_exists($where['column'], $this->to_db)) {
          $where['column'] = $this->to_db[$where['column']];
        }
      }
    }
  }
  
  private function coerceDataTypesFromDatabase(array $row) {
    $mapped = [];
    
    foreach($row as $col => $value) {
      switch($this->table->getColumn($col)->getType()->getName()) {
        case 'integer':
          $value = (int)$value;
        break;
        
        case 'timestamp':
        case 'datetime':
          $value = strtotime($value);
        break;
      }
      
      if(array_key_exists($col, $this->from_db)) {
        $mapped[$this->from_db[$col]] = $value;
      } else {
        $mapped[$col] = $value;
      }
    }
    
    return $mapped;
  }
  
  private function coerceDataTypesToDatabase(array $row) {
    $mapped = [];
    
    foreach($row as $col => $value) {
      switch($this->table->getColumn($col)->getType()->getName()) {
        case 'timestamp':
        case 'datetime':
          $value = date('c', $value);
        break;
      }
      
      $mapped[$col] = $value;
    }
    
    return $mapped;
  }
}
