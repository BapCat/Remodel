<<?= '?php' ?> namespace <?= $namespace ?>;

use Illuminate\Database\Capsule\Manager as Capsule;
use BapCat\Remodel\GatewayQuery;

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
