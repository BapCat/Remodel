<?php namespace BapCat\Remodel;

use BapCat\Values\Timestamp;
use Illuminate\Database\ConnectionInterface;

class GatewayQuery {
  private $builder;
  private $to_db;
  private $types;
  
  public function __construct(ConnectionInterface $connection, $table, array $to_db, array $types) {
    $connection->getQueryGrammar()->to_db = $to_db;
    
    $this->to_db = $to_db;
    $this->types = $types;
    
    $this->builder = $connection->table($table);
  }
  
  public function insert(array $values) {
    $this->coerceDataTypesToDatabase($values);
    return $this->builder->insert($values);
  }
  
  public function insertGetId(array $values) {
    $this->coerceDataTypesToDatabase($values);
    return $this->builder->insertGetId($values);
  }
  
  public function update(array $values) {
    $this->coerceDataTypesToDatabase($values);
    return $this->builder->update($values);
  }
  
  private function coerceDataTypesToDatabase(array &$row) {
    foreach($row as $col => &$value) {
      if(array_key_exists($col, $this->types)) {
        switch($this->types[$col]) {
          case Timestamp::class:
            $value = date('c', $value);
          break;
        }
      }
    }
  }
  
  public function __call($name, array $arguments) {
    $return = $this->builder->$name(...$arguments);
    
    if($return === $this->builder) {
      return $this;
    }
    
    return $return;
  }
}
