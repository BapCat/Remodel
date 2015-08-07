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
    
    $options = [
      'namespace'    => $builder->namespace,
      'name'         => $builder->name,
      'table'        => $builder->table,
      'ids'          => $builder->ids,
      'required'     => $required,
      'optional'     => $optional,
      'virtual'      => $builder->virtuals
    ];
    
    $this->tailor->bind($builder->fullname, 'Entity', $options);
    $this->tailor->bind($builder->fullname . 'Gateway', 'Gateway', $options);
    $this->tailor->bind($builder->fullname . 'Repository', 'Repository', $options);
  }
}
