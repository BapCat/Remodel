<?php namespace BapCat\Remodel;

use Illuminate\Database\SQLiteConnection;

use PDO;

trait RemodelTestTrait {
  public function setUpRemodel() {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $this->connection = new SQLiteConnection($pdo);
    $this->connection->setFetchMode(PDO::FETCH_ASSOC);
  }
  
  public function createTable($name, callable $creator) {
    return $this->connection->getSchemaBuilder()->create($name, $creator);
  }
  
  public function table($name) {
    return $this->connection->table($name);
  }
}
