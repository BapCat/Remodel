<<?= '?php' ?> namespace <?= $namespace ?>;

use Illuminate\Database\Capsule\Manager as Capsule;
use BapCat\Remodel\GatewayQuery;

class <?= $name ?>Gateway {
  protected static $MAPPINGS = [
<?php foreach(array_merge($ids, $required, $optional) as $def): ?>
    '<?= $def['mapped'] ?>' => '<?= $def['raw'] ?>',
<?php endforeach; ?>
  ];
  
  protected static $VIRTUAL = [
<?php foreach($virtual as $def): ?>
    '<?= $def['mapped'] ?>' => <?= var_export($def['raw'], true) ?>,
<?php endforeach; ?>
  ];
  
  public function query() {
    return new GatewayQuery(
      $this,
      Capsule::table('<?= $table ?>'),
      static::$MAPPINGS,
      array_flip(static::$MAPPINGS),
      static::$VIRTUAL
    );
  }
}
