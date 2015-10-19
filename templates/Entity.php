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
  
  private @func(__construct)
  
  public static @func(create({! defsToParams($required) !}))
    return self::make(null, {! defsToArgs($required) !});
  @endfunc
  
  public static @func(from({! defsToParams(array_merge([$id], $required)) !}))
    return self::make({! defsToArgs(array_merge([$id], $required)) !});
  @endfunc
  
  public static @func(fromRepository({! defsToParams(array_merge([$id], $required)) !}, callable $accessor))
    $entity = static::from({! defsToArgs(array_merge([$id], $required)) !});
    $accessor({! virtualsToArgs($virtual) !});
    return $entity;
  @endfunc
  
  private static @func(make({! defsToParams(array_merge([$id], $required, $optional), true) !}))
    $entity = new \{! $namespace !}\{! $name !}();
@each(array_merge([$id], $required, $optional) as $def)
    $entity->{! $def->alias !} = ${! $def->alias !};
@endeach
    
    return $entity;
  @endfunc
@each(array_merge([$id], $required, $optional) as $def)
  
  protected @func(get{! @titleize($def->alias) !})
    return $this->{! $def->alias !};
  @endfunc
  
  protected @func(set{! @titleize($def->alias) !}({! defToParam($def) !}))
    $this->{! $def->alias !} = ${! $def->alias !};
  @endfunc
@endeach
@each($virtual as $def)
  
  protected @func(get{! @titleize($def['alias']) !})
    return $this->{! $def['alias'] !};
  @endfunc
@endeach
  
  public @func(__toString)
    $output = '{! $namespace !}\{! $name !} ';
    
    if($this->id === null) {
      return $output . '(new)';
    }
    
    return $output . $this->id;
  @endfunc
  
  public @func(jsonSerialize)
    return [
@each(array_merge([$id], $required, $optional) as $def)
      '{! $def->alias !}' => $this->{! $def->alias !},
@endeach
@each($virtual as $def)
      '{! $def['alias'] !}' => $this->{! $def['alias'] !},
@endeach
    ];
  @endfunc
}
