<<?= '?php' ?> namespace <?= $namespace ?>;

use BapCat\Remodel\GatewayQuery;

use Illuminate\Database\ConnectionInterface;

class <?= $name ?>Gateway {
  protected static $MAPPINGS = [
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
    '<?= $def->alias ?>' => '<?= $def->raw ?>',
<?php endforeach; ?>
  ];
  
  protected static $VIRTUAL = [
<?php foreach($virtual as $def): ?>
    '<?= $def['alias'] ?>' => <?= var_export($def['raw'], true) ?>,
<?php endforeach; ?>
  ];
  
  private $connection;
  
  public function __construct(ConnectionInterface $connection) {
    $this->connection = $connection;
    $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
  }
  
  public function query() {
    return new GatewayQuery(
      $this->connection,
      '<?= $table ?>',
      static::$MAPPINGS,
      array_flip(static::$MAPPINGS),
      static::$VIRTUAL
    );
  }
}
