<?php
/**
 * @var  string                     $table
 * @var  string                     $namespace
 * @var  string                     $name
 * @var  EntityDefinitionOptions    $id
 * @var  EntityDefinitionOptions[]  $required
 * @var  EntityDefinitionOptions[]  $optional
 */

use BapCat\Remodel\EntityDefinitionOptions;

?>

<<?= '?php' ?> namespace <?= $namespace ?>;

use BapCat\Interfaces\Ioc\Ioc;
use BapCat\Remodel\GatewayQuery;

use Illuminate\Database\ConnectionInterface;

/**
 * Builds queries to retrieve raw `<?= $name ?>` data from the database
 */
class <?= $name ?>Gateway {
  /** @var  array  $MAPPINGS */
  protected static $MAPPINGS = [
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
    '<?= $def->alias ?>' => '<?= $def->raw ?>',
<?php endforeach; ?>
  ];

  /** @var  array  $TYPES */
  protected static $TYPES = [
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
    '<?= $def->raw ?>' => '<?= $def->type ?>',
<?php endforeach; ?>
  ];

  /** @var  ConnectionInterface  $connection */
  private $connection;

  /** @var  array  $scopes */
  private $scopes = [];

  /**
   * @param  ConnectionInterface  $connection
   */
  public function __construct(ConnectionInterface $connection) {
    $this->connection = $connection;
    
    $ioc = Ioc::instance();
    $this->scopes = $ioc->make("bap.remodel.scopes.{! str_replace('\\', '.', $namespace) !}.{! $name !}");
  }

  /**
   * Build a query to interact with `<?= $table ?>`
   *
   * @return  GatewayQuery
   */
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
