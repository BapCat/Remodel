<?php declare(strict_types=1); namespace BapCat\Remodel;

use Doctrine\DBAL\Driver as DoctrineDriver;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;

use DateTime;
use Illuminate\Database\Schema\Builder;
use PDO;

/**
 * A flexible Illuminate connection customized for Remodel
 */
class RemodelConnection extends Connection {
  /** @var  string  $builder */
  private $builder;

  /** @var  DoctrineDriver  $doctrine */
  private $doctrine;

  /**
   * @param  PDO                  $pdo
   * @param  Grammar              $grammar
   * @param  Processor            $processor
   * @param  DoctrineDriver|null  $doctrine
   */
  public function __construct(PDO $pdo, Grammar $grammar, Processor $processor, ?DoctrineDriver $doctrine = null) {
    parent::__construct($pdo);
    $this->queryGrammar  = new GrammarWrapper($grammar);
    $this->postProcessor = $processor;

    $this->doctrine = $doctrine;
  }

  /**
   * @param  string  $class  The fully-qualified class name of the schema builder
   *
   * @return  void
   */
  public function setSchemaBuilderClass(string $class): void {
    $this->builder = $class;
  }

  /**
   * @param  string  $query
   * @param  array   $bindings
   * @param  bool    $useReadPdo
   *
   * @return  array|mixed
   */
  public function select($query, $bindings = [], $useReadPdo = true) {
    $types = [];

    $rows = $this->run($query, $bindings, function($query, $bindings) use($useReadPdo, &$types) {
      if($this->pretending()) {
        return [];
      }

      $statement = $this->getPdoForSelect($useReadPdo)->prepare($query);
      $statement->execute($this->prepareBindings($bindings));

      for($i = 0; $i < $statement->columnCount(); $i++) {
        $meta = $statement->getColumnMeta($i);

        $type = $meta['native_type'];

        if(isset($meta['sqlite:decl_type'])) {
          if($meta['sqlite:decl_type'] === 'datetime') {
            $type = $meta['sqlite:decl_type'];
          }
        }

        $types[$meta['name']] = strtolower($type);
      }

      return $statement->fetchAll(PDO::FETCH_ASSOC);
    });

    foreach($rows as &$row) {
      foreach($row as $col => &$value) {
        if($value !== null) {
          switch($types[$col]) {
            case 'long':
            case 'integer':
              $value = (int)$value;
            break;

            case 'double':
              $value = (float)$value;
            break;

            case 'timestamp':
            case 'datetime':
              if(strpos($value, '.') === false) {
                $value = strtotime($value);
              } else {
                $value = (float)DateTime::createFromFormat('Y-m-d H:i:s.u', $value)->format('U.u');
              }
            break;
          }
        }
      }
    }

    return $rows;
  }

  /**
   * Get a schema builder instance for the connection
   *
   * @return  Builder
   */
  public function getSchemaBuilder() {
    if(empty($this->builder)) {
      return parent::getSchemaBuilder();
    }

    $class = $this->builder;
    return new $class($this);
  }

  /**
   * @return  DoctrineDriver|null
   */
  protected function getDoctrineDriver(): ?DoctrineDriver {
    return $this->doctrine;
  }
}
