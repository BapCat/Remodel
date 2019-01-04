<?php declare(strict_types=1); namespace BapCat\Remodel;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\Query\Processors\SQLiteProcessor;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar as SQLiteSchemaGrammar;

use PDO;

trait RemodelTestTrait {
  /** @var RemodelConnection $connection */
  protected $connection;

  public function setUpRemodel(array $mappings): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $this->connection = new RemodelConnection($pdo, new SQLiteGrammar(), new SQLiteProcessor());
    $this->connection->setSchemaGrammar(new SQLiteSchemaGrammar());
    $this->connection->getQueryGrammar()->to_db = $mappings;
  }

  public function createTable($name, callable $creator): void {
    $this->connection->getSchemaBuilder()->create($name, $creator);
  }

  public function table($name): Builder {
    return $this->connection->table($name);
  }
}
