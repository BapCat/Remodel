<<?= '?php' ?> namespace <?= $namespace ?>;

<?php

function defToParam(array $def, $nullable = false) {
  return "\\{$def['type']} \${$def['mapped']}" . ($nullable ? ' = null' : '');
}

function defsToParams(array $defs, $nullable = false) {
  $params = '';
  
  foreach($defs as $i => $def) {
    $params .= defToParam($def, $nullable);
    
    if($i < count($defs) - 1) {
      $params .= ', ';
    }
  }
  
  return $params;
}

function defToArg(array $def) {
  return "\${$def['mapped']}";
}

function defsToArgs(array $defs) {
  $args = '';
  
  foreach($defs as $i => $def) {
    $args .= defToArg($def);
    
    if($i < count($defs) - 1) {
      $args .= ', ';
    }
  }
  
  return $args;
}

?>

use BapCat\Propifier\PropifierTrait;

class <?= $name ?> {
  use PropifierTrait;
  
  public static $DEFINITION = [
<?php foreach($required as $def): ?>
    '<?= $def['mapped'] ?>' => ['required' => true, 'type' => \<?= $def['type'] ?>::class],
<?php endforeach; ?>
<?php foreach($optional as $def): ?>
    <?= $def['mapped'] ?> => ['required' => false, 'type' => \<?= $def['type'] ?>::class],
<?php endforeach; ?>
  ];
  
<?php foreach(array_merge($ids, $required, $optional) as $def): ?>
  private $<?= $def['mapped'] ?>;
<?php endforeach; ?>
  
  private function __construct() { }
  
  public static function create(<?= defsToParams($required) ?>) {
    return self::make(null, <?= defsToArgs($required) ?>);
  }
  
  public static function from(<?= defsToParams(array_merge($ids, $required)) ?>) {
    return self::make(<?= defsToArgs(array_merge($ids, $required)) ?>);
  }
  
  private static function make(<?= defsToParams(array_merge($ids, $required, $optional), true) ?>) {
    $entity = new User();
<?php foreach(array_merge($ids, $required, $optional) as $def): ?>
    $entity-><?= $def['mapped'] ?> = $<?= $def['mapped'] ?>;
<?php endforeach; ?>
    
    return $entity;
  }
  
<?php foreach($ids as $id): ?>
  protected function get<?= $id['inflected'] ?>() {
    return $this-><?= $id['mapped'] ?>;
  }
<?php endforeach; ?>
  
<?php foreach(array_merge($required, $optional) as $def): ?>
  protected function get<?= $def['inflected'] ?>() {
    return $this-><?= $def['mapped'] ?>;
  }
  
  protected function set<?= $def['inflected'] ?>(<?= defToParam($def) ?>) {
    $this-><?= $def['mapped'] ?> = <?= $def['mapped'] ?>;
  }
<?php endforeach; ?>
}
