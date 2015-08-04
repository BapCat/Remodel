<?php namespace BapCat\Remodel;

use BapCat\Tailor\Tailor;

class Registry {
  private $tailor;
  
  public function __construct(Tailor $tailor) {
    $this->tailor = $tailor;
  }
  
  public function register(EntityDefinition $builder) {
    $required = [];
    $optional = [];
    
    foreach($builder->values as $value) {
      if($value['req']) {
        $required[] = $value;
      } else {
        $optional[] = $value;
      }
    }
    
    $this->tailor->bind($builder->fullname, 'Entity', [
      'namespace'    => $builder->namespace,
      'name'         => $builder->name,
      'table'        => $builder->table,
      'ids'          => $builder->ids,
      'required'     => $required,
      'optional'     => $optional
    ]);
  }
}
