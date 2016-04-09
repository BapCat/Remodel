<?php namespace BapCat\Remodel;

use BapCat\Values\Timestamp;
use Illuminate\Database\ConnectionInterface;

use ReflectionProperty;

class GatewayQuery {
  private $builder;
  private $to_db;
  private $types;
  
  public function __construct(ConnectionInterface $connection, $table, array $to_db, array $from_db, array $types) {
    $this->to_db = $to_db;
    $this->types = $types;
    
    $this->builder = $connection->table($table);
    
    // We have to bust our way in here because the Mongo builder is strongly typed to their extended versions
    $grammar = new ReflectionProperty($this->builder, 'grammar');
    $grammar->setAccessible(true);
    $grammar->setValue($this->builder, new GrammarWrapper($connection->getQueryGrammar(), $to_db, $from_db));
    
    $processor = new ReflectionProperty($this->builder, 'processor');
    $processor->setAccessible(true);
    $processor->setValue($this->builder, new ProcessorWrapper($connection, $from_db));
  }
  
  public function insert(array $values) {
    $this->remapColumns($values);
    $this->coerceDataTypesToDatabase($values);
    return $this->builder->insert($values);
  }
  
  public function insertGetId(array $values) {
    $this->remapColumns($values);
    $this->coerceDataTypesToDatabase($values);
    return $this->builder->insertGetId($values);
  }
  
  public function update(array $values) {
    $this->remapColumns($values);
    $this->coerceDataTypesToDatabase($values);
    return $this->builder->update($values);
  }
  
  private function remapColumns(array &$values) {
    $keys = array_keys($values);
    
    foreach($keys as &$column) {
      if(array_key_exists($column, $this->to_db)) {
        $column = $this->to_db[$column];
      }
    }
    
    $values = array_combine($keys, array_values($values));
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
