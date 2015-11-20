@php namespace {! $namespace !};

<?php

use BapCat\Remodel\EntityDefinitionOptions;

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

class {! $name !} implements \BapCat\Remodel\Entity, \JsonSerializable {
  use \BapCat\Propifier\PropifierTrait;
  
@each(array_merge([$id], $required, $optional) as $def)
  private ${! $def->alias !};
@endeach
@each($virtual as $def)
  private ${! $def['alias'] !};
@endeach
  
  private function __construct() { }
  
  public static function create({! defsToParams($required) !}) {
    return self::make(null, {! defsToArgs($required) !});
  }
  
  public static function from({! defsToParams(array_merge([$id], $required)) !}) {
    return self::make({! defsToArgs(array_merge([$id], $required)) !});
  }
  
  public static function fromRepository({! defsToParams(array_merge([$id], $required)) !}, callable $accessor) {
    $entity = static::from({! defsToArgs(array_merge([$id], $required)) !});
    $accessor({! virtualsToArgs($virtual) !});
    return $entity;
  }
  
  private static function make({! defsToParams(array_merge([$id], $required, $optional), true) !}) {
    $entity = new \{! $namespace !}\{! $name !}();
@each(array_merge([$id], $required, $optional) as $def)
    $entity->{! $def->alias !} = ${! $def->alias !};
@endeach
    
    return $entity;
  }
@each(array_merge([$id], $required, $optional) as $def)
  
  protected function get{! @camelize($def->alias) !}() {
    return $this->{! $def->alias !};
  }
  
  protected function set{! @camelize($def->alias) !}({! defToParam($def) !}) {
    $this->{! $def->alias !} = ${! $def->alias !};
  }
@endeach
@each($virtual as $def)
  
  protected function get{! @camelize($def['alias']) !}() {
    return $this->{! $def['alias'] !};
  }
@endeach
  
  public function __toString() {
    $output = '{! $namespace !}\{! $name !} ';
    
    if($this->id === null) {
      return $output . '(new)';
    }
    
    return $output . $this->id;
  }
  
  public function jsonSerialize() {
    return [
@each(array_merge([$id], $required, $optional) as $def)
      '{! $def->alias !}' => $this->{! $def->alias !},
@endeach
@each($virtual as $def)
      '{! $def['alias'] !}' => $this->{! $def['alias'] !},
@endeach
    ];
  }
}
