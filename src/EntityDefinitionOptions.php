<?php namespace BapCat\Remodel;

use BapCat\Propifier\PropifierTrait;

class EntityDefinitionOptions {
  use PropifierTrait;
  
  private $alias;
  private $type;
  private $raw;
  private $read_only = false;
  
  public function __construct($alias, $type) {
    $this->alias = $alias;
    $this->type  = $type;
    $this->raw   = $alias;
  }
  
  public function mapsTo($raw) {
    $this->raw = $raw;
    return $this;
  }
  
  public function readOnly() {
    $this->read_only = true;
    return $this;
  }
  
  protected function getAlias() {
    return $this->alias;
  }
  
  protected function getType() {
    return $this->type;
  }
  
  protected function getRaw() {
    return $this->raw;
  }
  
  protected function getReadOnly() {
    return $this->read_only;
  }
}
