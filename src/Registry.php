<?php namespace BapCat\Remodel;

use BapCat\Tailor\Tailor;

class Registry {
  private $tailor;
  
  public function __construct(Tailor $tailor) {
    $this->tailor = $tailor;
  }
  
  public function register($class_name, EntityBuilder $builder) {
    $dependencies = [];
    $required = [];
    $optional = [];
    
    foreach($builder->values as $value) {
      if(!in_array($value['type'], $dependencies)) {
        $dependencies[] = $value['type'];
      }
      
      $value['type'] = basename($value['type']);
      
      if($value['req']) {
        $required[] = $value;
      } else {
        $optional[] = $value;
      }
    }
    
    $this->tailor->bind($class_name, 'Entity', [
      'namespace'    => dirname($class_name),
      'class_name'   => basename($class_name),
      'id'           => $builder->id,
      'dependencies' => $dependencies,
      'required'     => $required,
      'optional'     => $optional
    ]);
  }
}
