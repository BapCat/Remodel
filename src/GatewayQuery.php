<?php namespace BapCat\Remodel;

use BapCat\Values\Timestamp;
use Illuminate\Database\ConnectionInterface;

class GatewayQuery {
  private $builder;
  private $grammar;
  private $to_db;
  private $types;
  private $scopes;
  
  public function __construct(ConnectionInterface $connection, $table, array $to_db, array $types, array $scopes) {
    $this->grammar = $connection->getQueryGrammar();
    $this->grammar->to_db = $to_db;
    
    $this->to_db = $to_db;
    $this->types = $types;
    $this->scopes = $scopes;
    
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
  
  public function replace(array $values) {
    $this->grammar->replace();
    return $this->insert($values);
  }
  
  public function replaceGetId(array $values) {
    $this->grammar->replace();
    return $this->insertGetId($values);
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
    if(array_key_exists($name, $this->scopes)) {
      $return = $this->scopes[$name]($this, ...$arguments);
    } else {
      $return = $this->builder->$name(...$arguments);
    }
    
    if($return === $this->builder) {
      return $this;
    }
    
    return $return;
  }
}
