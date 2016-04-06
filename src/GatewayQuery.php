<?php namespace BapCat\Remodel;

use Illuminate\Database\ConnectionInterface;

class GatewayQuery {
  private $builder;
  private $doctrine;
  private $to_db;
  
  public function __construct(ConnectionInterface $connection, $table, array $to_db, array $from_db, array $virtual) {
    $this->doctrine = $connection->getDoctrineSchemaManager()->listTableDetails($table);
    $this->to_db = $to_db;
    
    $connection->setQueryGrammar (new GrammarWrapper  ($connection->getQueryGrammar (), $to_db, $virtual));
    $connection->setPostProcessor(new ProcessorWrapper($connection->getPostProcessor(), $this->doctrine, $from_db));
    
    $this->builder = $connection->table($table);
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
      switch($this->doctrine->getColumn($col)->getType()->getName()) {
        case 'timestamp':
        case 'datetime':
          $value = date('c', $value);
        break;
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
