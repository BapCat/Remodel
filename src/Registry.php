<?php namespace BapCat\Remodel;

use BapCat\Tailor\Tailor;

class Registry {
  private $tailor;
  
  public function __construct(Tailor $tailor) {
    $this->tailor = $tailor;
  }
  
  public function register(EntityDefinition $builder) {
    $options = [
      'namespace' => $builder->namespace,
      'name'      => $builder->name,
      'table'     => $builder->table,
      'id'        => $builder->id,
      'required'  => $builder->required,
      'optional'  => $builder->optional,
      'virtual'   => $builder->virtual
    ];
    
    $this->tailor->bind($builder->fullname, 'Entity', $options);
    $this->tailor->bind($builder->fullname . 'Gateway', 'Gateway', $options);
    $this->tailor->bind($builder->fullname . 'Repository', 'Repository', $options);
  }
}
