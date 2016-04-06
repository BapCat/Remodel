<?php namespace BapCat\Remodel;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;

class GrammarWrapper extends Grammar {
  private $grammar;
  private $to_db;
  private $virtual;
  
  public function __construct(Grammar $grammar, array $to_db, array $virtual) {
    $this->grammar = $grammar;
    $this->to_db = $to_db;
    $this->virtual = $virtual;
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
        $builder->columns = array_values($this->to_db);
        return;
      }
      
      if(is_string($builder->columns)) {
        $builder->columns = [$builder->columns];
      }
      
      $to_map = &$builder->columns;
    } else {
      $to_map = &$builder->aggregate['columns'];
    }
    
    $this->remapColumns($to_map);
  }
  
  private function remapColumns(array &$columns) {
    foreach($columns as &$column) {
      if(array_key_exists($column, $this->to_db)) {
        $column = $this->to_db[$column];
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
