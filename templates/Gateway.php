<<?= '?php' ?> namespace <?= $namespace ?>;

use BapCat\Remodel\GatewayQuery;

use Illuminate\Database\ConnectionInterface;

class <?= $name ?>Gateway {
  protected static $MAPPINGS = [
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
    '<?= $def->alias ?>' => '<?= $def->raw ?>',
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
      array_flip(static::$MAPPINGS)
    );
  }
}
