<?php namespace BapCat\Remodel;

use BapCat\Propifier\PropifierTrait;

class EntityDefinition {
  use PropifierTrait;
  
  private $fullname;
  private $name;
  private $namespace;
  private $table;
  private $id;
  private $required = [];
  private $optional = [];
  private $virtual = [];
  
  public function __construct($name) {
    $split = explode('\\', $name);
    
    $this->fullname = $name;
    $this->name = array_pop($split);
    $this->namespace = implode('\\', $split);
    $this->table = pluralize(underscore($this->name));
  }
  
  public function id($type) {
    return $this->id = new EntityDefinitionOptions('id', $type);
  }
  
  public function required($alias, $type) {
    return $this->required[] = new EntityDefinitionOptions($alias, $type);
  }
  
  public function optional($alias, $type) {
    return $this->optional[] = new EntityDefinitionOptions($alias, $type);
  }
  
  public function virtual($alias, $type, $raw) {
    $this->virtual[] = ['raw' => $raw, 'alias' => $alias, 'type' => $type];
  }
  
  protected function getFullname() {
    return $this->fullname;
  }
  
  protected function getName() {
    return $this->name;
  }
  
  protected function getNamespace() {
    return $this->namespace;
  }
  
  protected function getTable() {
    return $this->table;
  }
  
  protected function getId() {
    return $this->id;
  }
  
  protected function getRequired() {
    return $this->required;
  }
  
  protected function getOptional() {
    return $this->optional;
  }
  
  protected function getVirtual() {
    return $this->virtual;
  }
}
