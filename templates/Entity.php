@php namespace {! $namespace !};

<?php

use BapCat\Remodel\EntityDefinitionOptions;

if(!function_exists('defToParam')) {
  function defToParam(EntityDefinitionOptions $def, $nullable = false) {
    return "\\{$def->type} \${$def->alias}" . ($nullable ? ' = null' : '');
  }

  function defsToParams(array $defs, $nullable = false) {
    return implode(', ', array_map(function($def) use($nullable) {
      return defToParam($def, $nullable);
    }, $defs));
  }

  function defToArg(EntityDefinitionOptions $def) {
    return "\${$def->alias}";
  }

  function defsToArgs(array $defs) {
    return implode(', ', array_map(function($def) {
      return defToArg($def);
    }, $defs));
  }

  function virtualToArg(array $def) {
    return "\$entity->{$def['alias']}";
  }

  function virtualsToArgs(array $defs) {
    return implode(', ', array_map(function($def) {
      return virtualToArg($def);
    }, $defs));
  }
}

?>

class {! $name !} implements \BapCat\Remodel\Entity, \JsonSerializable {
  use \BapCat\Propifier\PropifierTrait;
  
  const ID_NAME = '{! $id->alias !}';
  
  private $ioc;
  
@each(array_merge([$id], $required, $optional) as $def)
  private ${! $def->alias !};
@endeach
@each($virtual as $def)
  private ${! $def['alias'] !};
@endeach
  
@each($has_many as $relation)
  private $cache_{! $relation->alias !};
@endeach
  
  private function __construct() {
    $this->ioc = \BapCat\Interfaces\Ioc\Ioc::instance();
  }
  
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
  
  public function cacheRelations() {
@each($has_many as $relation)
    $this->cache_{! $relation->alias !} = $this->{! $relation->alias !};
    
    foreach($this->cache_{! $relation->alias !} as $entity) {
      $entity->cacheRelations();
    }
@endeach
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
@each($has_many as $relation)
  
  protected function get{! @camelize($relation->alias) !}() {
    if(isset($this->cache_{! $relation->alias !})) {
      return $this->cache_{! $relation->alias !};
    }
    
    $repo = $this->ioc->make(\{! $relation->foreign_entity !}Repository::class);
    <?php $foreign_key = $relation->foreign_key ?: @underscore($name) . '_id'; ?>
    return $repo->with{! @camelize($foreign_key) !}($this->{! $relation->local_key ?: $id->alias !})->get();
  }
@endeach
@each($belongs_to as $relation)
  
  protected function get{! @camelize($relation->alias) !}() {
    $repo = $this->ioc->make(\{! $relation->foreign_entity !}Repository::class);
    <?php $foreign_key = $relation->foreign_key ?: $relation->foreign_entity::ID_NAME; ?>
    return $repo->with{! @camelize($foreign_key) !}($this->{! $relation->local_key ?: $relation->alias . '_id' !})->first();
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
    $output = [
@each(array_merge([$id], $required, $optional) as $def)
      '{! $def->alias !}' => $this->{! $def->alias !},
@endeach
@each($virtual as $def)
      '{! $def['alias'] !}' => $this->{! $def['alias'] !},
@endeach
    ];
    
@each($has_many as $relation)
    if(isset($this->cache_{! $relation->alias !})) {
      $output['{! $relation->alias !}'] = $this->cache_{! $relation->alias !};
    }
@endeach
    
    return $output;
  }
}
