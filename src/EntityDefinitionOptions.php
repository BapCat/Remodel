<?php namespace BapCat\Remodel;

use BapCat\Propifier\PropifierTrait;

class EntityDefinitionOptions {
  use PropifierTrait;
  
  private $alias;
  private $type;
  private $raw;
  
  private $referenced_entity;
  private $referenced_field;
  
  public function __construct($alias, $type) {
    $this->alias = $alias;
    $this->type  = $type;
    $this->raw   = $alias;
  }
  
  public function mapsTo($raw) {
    $this->raw = $raw;
    return $this;
  }
  
  public function references($entity_class, $referenced_field = null) {
    $this->referenced_entity = $entity_class;
    $this->referenced_field  = $referenced_field;
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
  
  protected function getReferencedEntity() {
    return $this->referenced_entity;
  }
  
  protected function getReferencedField() {
    return $this->referenced_field;
  }
}
