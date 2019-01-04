<?php declare(strict_types=1); namespace BapCat\Remodel;

use BapCat\Values\Timestamp;
use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * The query builder
 *
 * @method self select(array|mixed $columns = ['*'])
 * @method self distinct()
 * @method self where(string|array|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 * @method self orWhere(string|array|Closure $column, mixed $operator = null, mixed $value = null)
 * @method self whereIn(string|array|Closure $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method self orWhereIn(string|array|Closure $column, mixed $values)
 * @method self whereNotIn(string|array|Closure $column, mixed $values, string $boolean = 'and', bool $not = false)
 * @method self orWhereNotIn(string|array|Closure $column, mixed $values)
 * @method self whereNull(string|array|Closure $column, string $boolean = 'and', bool $not = false)
 * @method self orWhereNull(string|array|Closure $column)
 * @method self whereNotNull(string|array|Closure $column, string $boolean = 'and')
 * @method self orWhereNotNull(string|array|Closure $column)
 * @method self whereBetween(string|array|Closure $column, array $values, string $boolean = 'and', bool $not = false)
 * @method self orWhereBetween(string|array|Closure $column, array $values)
 * @method self whereNotBetween(string|array|Closure $column, array $values, string $boolean = 'and', bool $not = false)
 * @method self orWhereNotBetween(string|array|Closure $column, array $values)
 * @method self whereExists(Closure $callback, string $boolean = 'and', bool $not = false)
 * @method self orWhereExists(Closure $callback, bool $not = false)
 * @method self whereNotExists(Closure $callback, string $boolean = 'and')
 * @method self orWhereNotExists(Closure $callback)
 * @method self groupBy(...$groups)
 * @method self having(string|array|Closure $column, ?string $operator = null, ?string $value = null, string $boolean = 'and')
 * @method self orHaving(string|array|Closure $column, ?string $operator = null, ?string $value = null)
 * @method self orderBy(string|array|Closure $column, string $direction = 'asc')
 * @method self orderByDesc(string|array|Closure $column)
 * @method self latest(string $column = 'created_at')
 * @method self oldest(string $column = 'created_at')
 * @method self inRandomOrder(string $seed = '')
 * @method self skip(int $value)
 * @method self offset(int $value)
 * @method self take(int $value)
 * @method self limit(int $value)
 * @method string toSql()
 * @method Collection get(string[] $columns = ['*'])
 * @method Model|object|self|null first(string[] $columns = ['*'])
 * @method bool exists()
 * @method bool doesntExist()
 * @method int count(string $columns = '*')
 * @method int min(string $column)
 * @method int max(string $column)
 * @method int sum(string $column)
 * @method int avg(string $column)
 * @method int average(string $column)
 * @method int delete(?mixed $id = null)
 * @method mixed|self find(int $id, array $columns = ['*'])
 */
class GatewayQuery {
  /** @var  Builder  $builder */
  private $builder;

  /** @var  GrammarWrapper  $grammar */
  private $grammar;

  /** @var  array  $types */
  private $types;

  /** @var  array  $scopes */
  private $scopes;

  /**
   * @param  ConnectionInterface  $connection
   * @param  string               $table
   * @param  array                $to_db
   * @param  array                $types
   * @param  array                $scopes
   */
  public function __construct(ConnectionInterface $connection, string $table, array $to_db, array $types, array $scopes) {
    $this->grammar = $connection->getQueryGrammar();
    $this->grammar->to_db = $to_db;

    $this->types = $types;
    $this->scopes = $scopes;

    $this->builder = $connection->table($table);
  }

  /**
   * Insert a new record into the database
   *
   * @param  mixed[]  $values
   *
   * @return  bool
   */
  public function insert(array $values): bool {
    $this->coerceDataTypesToDatabase($values);
    return $this->builder->insert($values);
  }

  /**
   * Insert a new record and get the value of the primary key
   *
   * @param  mixed[]  $values
   *
   * @return  int
   */
  public function insertGetId(array $values): int {
    $this->coerceDataTypesToDatabase($values);
    return $this->builder->insertGetId($values);
  }

  /**
   * Update a record in the database
   *
   * @param  mixed[]  $values
   *
   * @return  int
   */
  public function update(array $values): int {
    $this->coerceDataTypesToDatabase($values);
    return $this->builder->update($values);
  }

  /**
   * Insert or replace a record in the database
   *
   * @param  mixed[]  $values
   *
   * @return  bool
   */
  public function replace(array $values): bool {
    $this->grammar->replace();
    return $this->insert($values);
  }

  /**
   * Insert or replace a record and get the value of the primary key
   *
   * @param  mixed[]  $values
   *
   * @return  int
   */
  public function replaceGetId(array $values): int {
    $this->grammar->replace();
    return $this->insertGetId($values);
  }

  /**
   * @param  mixed[]  $row
   *
   * @return  void
   */
  private function coerceDataTypesToDatabase(array &$row): void {
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

  /**
   * @param  string  $name
   * @param  array   $arguments
   *
   * @return  self|mixed
   */
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
