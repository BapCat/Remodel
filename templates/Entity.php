<<?= '?php' ?> namespace <?= $namespace ?>;

<?php

use BapCat\Remodel\EntityDefinitionOptions;

function defToParam(EntityDefinitionOptions $def, $nullable = false) {
  return "\\{$def->type} \${$def->alias}" . ($nullable ? ' = null' : '');
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

function defToArg(EntityDefinitionOptions $def) {
  return "\${$def->alias}";
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

class <?= $name ?> {
  use \BapCat\Propifier\PropifierTrait;
  
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
  private $<?= $def->alias ?>;
<?php endforeach; ?>
  
  private function __construct() { }
  
  public static function create(<?= defsToParams($required) ?>) {
    return self::make(null, <?= defsToArgs($required) ?>);
  }
  
  public static function from(<?= defsToParams(array_merge([$id], $required)) ?>) {
    return self::make(<?= defsToArgs(array_merge([$id], $required)) ?>);
  }
  
  private static function make(<?= defsToParams(array_merge([$id], $required, $optional), true) ?>) {
    $entity = new User();
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
    $entity-><?= $def->alias ?> = $<?= $def->alias ?>;
<?php endforeach; ?>
    
    return $entity;
  }
  
  protected function get<?= $id->alias ?>() {
    return $this-><?= $id->alias ?>;
  }
  
<?php foreach(array_merge($required, $optional) as $def): ?>
  protected function get<?= $def->alias ?>() {
    return $this-><?= $def->alias ?>;
  }
  
  protected function set<?= $def->alias ?>(<?= defToParam($def) ?>) {
    $this-><?= $def->alias ?> = $<?= $def->alias ?>;
  }
<?php endforeach; ?>
}
