<?php namespace BapCat\Remodel;

use BapCat\Propifier\PropifierTrait;
use ICanBoogie\Inflector;

class EntityDefinition {
  use PropifierTrait;
  
  private $inflector;
  
  private $fullname;
  private $name;
  private $namespace;
  private $table;
  private $ids = [];
  private $values = [];
  private $virtual = [];
  
  public function __construct($name, $table) {
    $split = explode('\\', $name);
    
    $this->inflector = Inflector::get();
    $this->fullname = $name;
    $this->name = array_pop($split);
    $this->namespace = implode('\\', $split);
    $this->table = $table;
  }
  
  private function add($mapped_name, $raw_name, $type, $required) {
    $this->values[] = ['raw' => $raw_name, 'mapped' => $mapped_name, 'inflected' => $this->inflector->camelize($mapped_name), 'type' => $type, 'req' => $required];
    return $this;
  }
  
  public function id($mapped_name, $raw_name, $type) {
    $this->ids[] = ['raw' => $raw_name, 'mapped' => $mapped_name, 'inflected' => $this->inflector->camelize($mapped_name), 'type' => $type];
    return $this;
  }
  
  public function required($mapped_name, $raw_name, $type) {
    return $this->add($raw_name, $mapped_name, $type, true);
  }
  
  public function optional($mapped_name, $raw_name, $type) {
    return $this->add($raw_name, $mapped_name, $type, false);
  }
  
  public function virtual($mapped_name, $raw_name, $type) {
    $this->virtual[] = ['raw' => $raw_name, 'mapped' => $mapped_name, 'inflected' => $this->inflector->camelize($mapped_name), 'type' => $type];
    return $this;
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
  
  protected function getId($index) {
    return $this->ids[$index];
  }
  
  protected function getIds() {
    return $this->ids;
  }
  
  protected function getValue($index) {
    return $this->values[$index];
  }
  
  protected function getValues() {
    return $this->values;
  }
  
  protected function getVirtual($index) {
    return $this->virtual[$index];
  }
  
  protected function getVirtuals() {
    return $this->virtual;
  }
}
