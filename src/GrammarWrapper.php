<?php namespace BapCat\Remodel;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;

class GrammarWrapper {
  private $grammar;
  private $to_db;
  private $from_db;
  
  public function __construct(Grammar $grammar, array $to_db, array $from_db) {
    $this->grammar = $grammar;
    $this->to_db = $to_db;
    $this->from_db = $from_db;
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
    $this->remapWheres($query);
    $sql = $this->grammar->compileInsert($query, $values);
    //var_dump($sql);
    return $sql;
  }
  
  public function compileInsertGetId(Builder $query, $values, $sequence) {
    $this->remapWheres($query);
    $sql = $this->grammar->compileInsertGetId($query, $values, $sequence);
    //var_dump($sql);
    return $sql;
  }
  
  public function compileUpdate(Builder $query, $values) {
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
