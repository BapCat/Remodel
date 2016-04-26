<<?= '?php' ?> namespace <?= $namespace ?>;

use BapCat\Remodel\GatewayQuery;
use BapCat\Remodel\GrammarWrapper;
use BapCat\Remodel\RemodelConnection;

use Illuminate\Database\ConnectionInterface;

use PDO;

class <?= $name ?>Gateway {
  protected static $MAPPINGS = [
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
    '<?= $def->alias ?>' => '<?= $def->raw ?>',
<?php endforeach; ?>
  ];
  
  protected static $TYPES = [
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
    '<?= $def->raw ?>' => '<?= $def->type ?>',
<?php endforeach; ?>
  ];
  
  private $connection;
  
  public function __construct(ConnectionInterface $connection) {
    $this->connection = $connection;
  }
  
  public function query() {
    return new GatewayQuery(
      $this->connection,
      '<?= $table ?>',
      static::$MAPPINGS,
      static::$TYPES
    );
  }
}
