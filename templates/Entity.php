<<?= '?php' ?> namespace <?= $namespace ?>;

use BapCat\Propifier\PropifierTrait;

<?php foreach($dependencies as $dependency): ?>
use <?= $dependency ?>;
<?php endforeach; ?>

class <?= $class_name ?> {
  use PropifierTrait;
  
  public static $DEFINITION = [
<?php foreach($required as $def): ?>
    '<?= $def['mapped'] ?>' => ['required' => true, 'type' => '<?= $def['type'] ?>'],
<?php endforeach; ?>
<?php foreach($optional as $def): ?>
    <?= $def['mapped'] ?> => ['required' => false, 'type' => '<?= $def['type'] ?>'],
<?php endforeach; ?>
  ];
  
  private $<?= $id['mapped'] ?>;
<?php foreach($required as $def): ?>
  private $<?= $def['mapped'] ?>;
<?php endforeach; ?>
  
  private function __construct() { }
  
  public static function create(<?php foreach($required as $i => $def): ?><?= $def['type'] ?> $<?= $def['mapped'] ?><?php if($i == count($required)): ?><?= ',' ?><?php endif; ?><?php endforeach; ?>) {
    return self::make(null, <?php foreach($required as $i => $def): ?>$<?= $def['mapped'] ?><?php if($i == count($required)): ?><?= ', ' ?><?php endif; ?><?php endforeach; ?>);
  }
  
  public static function from(<?= $id['type'] ?> $<?= $id['mapped'] ?><?php foreach($required as $def): ?>, <?= $def['type'] ?> $<?= $def['mapped'] ?><?php endforeach; ?>) {
    return self::make($<?= $id['mapped'] ?><?php foreach($required as $def): ?>, $<?= $def['mapped'] ?><?php endforeach; ?>);
  }
  
  private static function make(<?= $id['type'] ?> $<?= $id['mapped'] ?> = null<?php foreach($required as $def): ?>, <?= $def['type'] ?> $<?= $def['mapped'] ?> = null<?php endforeach; ?><?php foreach($optional as $def): ?>, <?= $def['type'] ?> $<?= $def['mapped'] ?> = null<?php endforeach; ?>) {
    $entity = new User();
    $entity-><?= $id['mapped'] ?> = $<?= $id['mapped'] ?>;
<?php foreach($required as $def): ?>
    $entity-><?= $def['mapped'] ?> = $<?= $def['mapped'] ?>;
<?php endforeach; ?>
    
    return $entity;
  }
  
  protected function get<?= $id['inflected'] ?>() {
    return $this-><?= $id['mapped'] ?>;
  }
  
<?php foreach(array_merge($required, $optional) as $def): ?>
  protected function get<?= $def['inflected'] ?>() {
    return $this-><?= $def['mapped'] ?>;
  }
  
  protected function set<?= $def['inflected'] ?>(<?= $def['type'] ?> $<?= $def['mapped'] ?>) {
    $this-><?= $def['mapped'] ?> = <?= $def['mapped'] ?>;
  }
<?php endforeach; ?>
}
