<?php namespace BapCat\Remodel;

use Illuminate\Database\Query\Grammars\SQLiteGrammar;
use Illuminate\Database\Query\Processors\SQLiteProcessor;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar as SQLiteSchemaGrammar;

use PDO;

trait RemodelTestTrait {
  protected $connection;
  
  public function setUpRemodel(array $mappings) {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    $this->connection = new RemodelConnection($pdo, new SQLiteGrammar(), new SQLiteProcessor());
    $this->connection->setSchemaGrammar(new SQLiteSchemaGrammar());
    $this->connection->getQueryGrammar()->to_db = $mappings;
  }
  
  public function createTable($name, callable $creator) {
    return $this->connection->getSchemaBuilder()->create($name, $creator);
  }
  
  public function table($name) {
    return $this->connection->table($name);
  }
}
