<?php namespace BapCat\Remodel;

use BapCat\Propifier\PropifierTrait;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;

class GrammarWrapper extends Grammar {
  use PropifierTrait;
  
  private $grammar;
  private $to_db = [];
  
  public function __construct(Grammar $grammar) {
    $this->grammar = $grammar;
  }
  
  protected function setToDb(array $to_db) {
    $this->to_db = $to_db;
  }
  
  public function getOriginalGrammar() {
    return $this->grammar;
  }
  
  public function compileSelect(Builder $query) {
    $this->beforeGet($query);
    $sql = $this->grammar->compileSelect($query);
    //var_dump($sql);
    return $sql;
  }
  
  public function compileExists(Builder $query) {
    return $this->grammar->compileExists($query);
  }
  
  public function compileInsert(Builder $query, array $values) {
    foreach($values as &$row) {
      $this->beforePut($row);
    }
    
    $this->remapWheres($query);
    $sql = $this->grammar->compileInsert($query, $values);
    //var_dump($sql);
    return $sql;
  }
  
  public function compileInsertGetId(Builder $query, $values, $sequence) {
    foreach($values as &$row) {
      $this->beforePut($row);
    }
    
    $this->remapWheres($query);
    $sql = $this->grammar->compileInsertGetId($query, $values, $sequence);
    //var_dump($sql);
    return $sql;
  }
  
  public function compileUpdate(Builder $query, $values) {
    $this->beforePut($values);
    $this->remapWheres($query);
    $sql = $this->grammar->compileUpdate($query, $values);
    //var_dump($sql);
    return $sql;
  }
  
  public function compileDelete(Builder $query) {
    $this->remapWheres($query);
    $sql = $this->grammar->compileDelete($query);
    //var_dump($sql);
    return $sql;
  }
  
  public function compileTruncate(Builder $query) {
    return $this->grammar->compileTruncate($query);
  }
  
  public function supportsSavepoints() {
    return $this->grammar->supportsSavepoints();
  }
  
  public function compileSavepoint($name) {
    return $this->grammar->compileSavepoint($name);
  }
  
  public function compileSavepointRollBack($name) {
    return $this->grammar->compileSavepointRollBack($name);
  }
  
  private function beforeGet(Builder $builder) {
    $this->remapBuilderColumns($builder);
    $this->remapWheres($builder);
  }
  
  private function beforePut(array &$values) {
    $keys = array_keys($values);
    
    foreach($keys as &$column) {
      if(array_key_exists($column, $this->to_db)) {
        $column = $this->to_db[$column];
      }
    }
    
    $values = array_combine($keys, array_values($values));
  }
  
  private function remapBuilderColumns(Builder $builder) {
    if($builder->aggregate === null) {
      if($builder->columns === ['*']) {
        $builder->columns = array_keys($this->to_db);
      }
      
      if(is_string($builder->columns)) {
        $builder->columns = [$builder->columns];
      }
      
      $this->remapColumns($builder->columns);
    } else {
      if($builder->aggregate['columns'] == ['*']) {
        return;
      }
      
      $this->remapColumns($builder->aggregate['columns'], false);
    }
  }
  
  private function remapColumns(array &$columns, $alias = true) {
    foreach($columns as &$column) {
      if($this->to_db[$column] != $column) {
        if($alias) {
          $column = $this->to_db[$column] . ' as ' . $column;
        } else {
          $column = $this->to_db[$column];
        }
      }
    }
  }
  
  private function remapWheres(Builder $builder) {
    if($builder->wheres !== null) {
      foreach($builder->wheres as &$where) {
        if(array_key_exists($where['column'], $this->to_db)) {
          $where['column'] = $this->to_db[$where['column']];
        }
      }
    }
  }
}
