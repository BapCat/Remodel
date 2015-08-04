<?php namespace BapCat\Remodel;

use BapCat\Propifier\PropifierTrait;
use ICanBoogie\Inflector;

class EntityBuilder {
  use PropifierTrait;
  
  private $inflector;
  
  private $id;
  private $values = [];
  
  public function __construct($id_raw_name, $id_mapped_name, $id_type) {
    $this->inflector = Inflector::get();
    $this->id = ['raw' => $id_raw_name, 'mapped' => $id_mapped_name, 'inflected' => $this->inflector->camelize($id_mapped_name), 'type' => $id_type];
  }
  
  private function add($raw_name, $mapped_name, $type, $required) {
    $this->values[] = ['raw' => $raw_name, 'mapped' => $mapped_name, 'inflected' => $this->inflector->camelize($mapped_name), 'type' => $type, 'req' => $required];
    return $this;
  }
  
  public function required($raw_name, $mapped_name, $type) {
    return $this->add($raw_name, $mapped_name, $type, true);
  }
  
  public function optional($raw_name, $mapped_name, $type) {
    return $this->add($raw_name, $mapped_name, $type, false);
  }
  
  protected function getId() {
    return $this->id;
  }
  
  protected function getValue($index) {
    return $this->values[$index];
  }
  
  protected function getValues() {
    return $this->values;
  }
}
