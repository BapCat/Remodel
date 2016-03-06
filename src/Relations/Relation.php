<?php namespace BapCat\Remodel\Relations;

use BapCat\Propifier\PropifierTrait;

class Relation {
  use PropifierTrait;
  
  private $alias;
  private $local_entity;
  private $local_key;
  private $foreign_entity;
  private $foreign_key;
  
  public function __construct($alias, $local_entity, $foreign_entity) {
    $this->alias          = $alias;
    $this->local_entity   = $local_entity;
    $this->foreign_entity = $foreign_entity;
  }
  
  public function localKey($key) {
    $this->local_key = $key;
    return $this;
  }
  
  public function foreignKey($key) {
    $this->foreign_key = $key;
    return $this;
  }
  
  protected function getAlias() {
    return $this->alias;
  }
  
  protected function getLocalEntity() {
    return $this->local_entity;
  }
  
  protected function getLocalKey() {
    return $this->local_key;
  }
  
  protected function getForeignEntity() {
    return $this->foreign_entity;
  }
  
  protected function getForeignKey() {
    return $this->foreign_key;
  }
}
