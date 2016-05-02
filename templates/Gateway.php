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
  private $scopes = [];
  
  public function __construct(ConnectionInterface $connection) {
    $this->connection = $connection;
    
    $ioc = \BapCat\Interfaces\Ioc\Ioc::instance();
    $this->scopes = $ioc->make("bap.remodel.scopes.{! str_replace('\\', '.', $namespace) !}.{! $name !}");
  }
  
  public function query() {
    return new GatewayQuery(
      $this->connection,
      '<?= $table ?>',
      static::$MAPPINGS,
      static::$TYPES,
      $this->scopes
    );
  }
}
