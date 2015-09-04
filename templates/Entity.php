<<?= '?php' ?> namespace <?= $namespace ?>;

<?php

use BapCat\Remodel\EntityDefinitionOptions;

//use function BapCat\Remodel\titlize;

if(!function_exists('defToParam')) {
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

  function virtualToArg(array $def) {
    return "\$entity->{$def['alias']}";
  }

  function virtualsToArgs(array $defs) {
    $args = '';
    
    foreach($defs as $i => $def) {
      $args .= virtualToArg($def);
      
      if($i < count($defs) - 1) {
        $args .= ', ';
      }
    }
    
    return $args;
  }
}

?>

class <?= $name ?> implements \BapCat\Remodel\Entity, \JsonSerializable {
  use \BapCat\Propifier\PropifierTrait;
  
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
  private $<?= $def->alias ?>;
<?php endforeach; ?>
<?php foreach($virtual as $def): ?>
  private $<?= $def['alias'] ?>;
<?php endforeach; ?>
  
  private function __construct() { }
  
  public static function create(<?= defsToParams($required) ?>) {
    return self::make(null, <?= defsToArgs($required) ?>);
  }
  
  public static function from(<?= defsToParams(array_merge([$id], $required)) ?>) {
    return self::make(<?= defsToArgs(array_merge([$id], $required)) ?>);
  }
  
  public static function fromRepository(<?= defsToParams(array_merge([$id], $required)) ?>, callable $accessor) {
    $entity = static::from(<?= defsToArgs(array_merge([$id], $required)) ?>);
    $accessor(<?= virtualsToArgs($virtual) ?>);
    return $entity;
  }
  
  private static function make(<?= defsToParams(array_merge([$id], $required, $optional), true) ?>) {
    $entity = new \<?= $namespace ?>\<?= $name ?>();
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
    $entity-><?= $def->alias ?> = $<?= $def->alias ?>;
<?php endforeach; ?>
    
    return $entity;
  }
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
  
  protected function get<?= \BapCat\Remodel\titlize($def->alias) ?>() {
    return $this-><?= $def->alias ?>;
  }
  
  protected function set<?= \BapCat\Remodel\titlize($def->alias) ?>(<?= defToParam($def) ?>) {
    $this-><?= $def->alias ?> = $<?= $def->alias ?>;
  }
<?php endforeach; ?>
<?php foreach($virtual as $def): ?>
  
  protected function get<?= \BapCat\Remodel\titlize($def['alias']) ?>() {
    return $this-><?= $def['alias'] ?>;
  }
<?php endforeach; ?>
  
  public function __toString() {
    $output = '<?= $namespace ?>\<?= $name ?> ';
    
    if($this->id === null) {
      return $output . '(new)';
    }
    
    return $output . $this->id;
  }
  
  public function jsonSerialize() {
    return [
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
      '<?= $def->alias ?>' => $this-><?= $def->alias ?>,
<?php endforeach; ?>
<?php foreach($virtual as $def): ?>
      '<?= $def['alias'] ?>' => $this-><?= $def['alias'] ?>,
<?php endforeach; ?>
    ];
  }
}
