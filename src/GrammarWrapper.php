<?php declare(strict_types=1); namespace BapCat\Remodel;

use BapCat\Propifier\PropifierTrait;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;

use function is_string;

/**
 * A wrapper for Illuminate's Grammar class
 */
class GrammarWrapper extends Grammar {
  use PropifierTrait;

  /** @var  Grammar  $grammar */
  private $grammar;

  /** @var  mixed[]  $to_db */
  private $to_db = [];

  /** @var  bool  $replace_into */
  private $replace_into = false;

  /**
   * @param  Grammar  $grammar
   */
  public function __construct(Grammar $grammar) {
    $this->grammar = $grammar;
  }

  /**
   * @param  array  $to_db
   *
   * @return  void
   */
  protected function setToDb(array $to_db): void {
    $this->to_db = $to_db;
  }

  /**
   * @return  Grammar
   */
  public function getOriginalGrammar(): Grammar {
    return $this->grammar;
  }

  /**
   * Turn on `replace into`
   *
   * @return  void
   */
  public function replace(): void {
    $this->replace_into = true;
  }

  /**
   * Compile a select query into SQL
   *
   * @param  Builder  $query
   *
   * @return  string
   */
  public function compileSelect(Builder $query) {
    $this->beforeGet($query);
    return $this->grammar->compileSelect($query);
  }

  /**
   * Compile an exists statement into SQL
   *
   * @param  Builder  $query
   *
   * @return  string
   */
  public function compileExists(Builder $query) {
    return $this->grammar->compileExists($query);
  }

  /**
   * Compile an insert statement into SQL
   *
   * @param  Builder  $query
   * @param  mixed[]  $values
   *
   * @return  string
   */
  public function compileInsert(Builder $query, array $values) {
    foreach($values as &$row) {
      $this->beforePut($row);
    }

    unset($row);

    $this->remapWheres($query);
    $sql = $this->grammar->compileInsert($query, $values);

    if($this->replace_into) {
      $sql = preg_replace('/^insert into/i', 'replace into', $sql);
      $this->replace_into = false;
    }

    return $sql;
  }

  /**
   * Compile an insert and get ID statement into SQL
   *
   * @param  Builder  $query
   * @param  mixed[]  $values
   * @param  string   $sequence
   *
   * @return  string
   */
  public function compileInsertGetId(Builder $query, $values, $sequence) {
    $this->beforePut($values);
    $this->remapWheres($query);
    $sql = $this->grammar->compileInsertGetId($query, $values, $sequence);

    if($this->replace_into) {
      $sql = preg_replace('/^insert into/i', 'replace into', $sql);
      $this->replace_into = false;
    }

    return $sql;
  }

  /**
   * Compile an update statement into SQL
   *
   * @param  Builder  $query
   * @param  array    $values
   *
   * @return  string
   */
  public function compileUpdate(Builder $query, $values) {
    $this->beforePut($values);
    $this->remapWheres($query);
    return $this->grammar->compileUpdate($query, $values);
  }

  /**
   * Compile a delete statement into SQL
   *
   * @param  Builder  $query
   *
   * @return  string
   */
  public function compileDelete(Builder $query) {
    $this->remapWheres($query);
    return $this->grammar->compileDelete($query);
  }

  /**
   * Compile a truncate table statement into SQL
   *
   * @param  Builder  $query
   *
   * @return  array
   */
  public function compileTruncate(Builder $query) {
    return $this->grammar->compileTruncate($query);
  }

  /**
   * Determine if the grammar supports savepoints
   *
   * @return  bool
   */
  public function supportsSavepoints() {
    return $this->grammar->supportsSavepoints();
  }

  /**
   * Compile the SQL statement to define a savepoint
   *
   * @param  string  $name
   *
   * @return  string
   */
  public function compileSavepoint($name) {
    return $this->grammar->compileSavepoint($name);
  }

  /**
   * Compile the SQL statement to execute a savepoint rollback
   *
   * @param  string  $name
   *
   * @return  string
   */
  public function compileSavepointRollBack($name) {
    return $this->grammar->compileSavepointRollBack($name);
  }

  /**
   * @param  Builder  $builder
   *
   * @return  void
   */
  private function beforeGet(Builder $builder): void {
    $this->remapBuilderColumns($builder);
    $this->remapWheres($builder);
  }

  /**
   * @param  array  $values
   *
   * @return  void
   */
  private function beforePut(array &$values): void {
    $keys = array_keys($values);

    foreach($keys as &$column) {
      if(array_key_exists($column, $this->to_db)) {
        $column = $this->to_db[$column];
      }
    }

    unset($column);

    $values = array_combine($keys, array_values($values));
  }

  /**
   * @param  Builder  $builder
   *
   * @return  void
   */
  private function remapBuilderColumns(Builder $builder): void {
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

  /**
   * @param  array  $columns
   * @param  bool   $alias
   *
   * @return  void
   */
  private function remapColumns(array &$columns, $alias = true): void {
    foreach($columns as &$column) {
      if($this->to_db[$column] !== $column) {
        if($alias) {
          $column = $this->to_db[$column] . ' as ' . $column;
        } else {
          $column = $this->to_db[$column];
        }
      }
    }
  }

  /**
   * @param  Builder  $builder
   *
   * @return  void
   */
  private function remapWheres(Builder $builder): void {
    if($builder->wheres !== null) {
      foreach($builder->wheres as &$where) {
        if(array_key_exists($where['column'], $this->to_db)) {
          $where['column'] = $this->to_db[$where['column']];
        }
      }
    }
  }
}
