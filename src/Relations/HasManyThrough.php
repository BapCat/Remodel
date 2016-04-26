<?php namespace BapCat\Remodel\Relations;

use BapCat\Propifier\PropifierTrait;

class HasManyThrough {
  use PropifierTrait;
  
  private $alias;
  private $entity_join;
  private $entity_foreign;
  private $id_local;
  private $id_foreign;
  private $key_local;
  private $key_foreign;
  
  public function __construct($alias, $entity_join, $entity_foreign) {
    $this->alias = $alias;
    
    $this->entity_join    = $entity_join;
    $this->entity_foreign = $entity_foreign;
  }
  
  public function idLocal($id) {
    $this->id_local= $id;
    return $this;
  }
  
  public function idForeign($id) {
    $this->id_foreign = $id;
    return $this;
  }
  
  public function keyLocal($key) {
    $this->key_local = $key;
    return $this;
  }
  
  public function keyForeign($key) {
    $this->key_foreign = $key;
    return $this;
  }
  
  protected function getAlias() {
    return $this->alias;
  }
  
  protected function getEntityJoin() {
    return $this->entity_join;
  }
  
  protected function getEntityForeign() {
    return $this->entity_foreign;
  }
  
  protected function getIdLocal() {
    return $this->id_local;
  }
  
  protected function getIdForeign() {
    return $this->id_foreign;
  }
  
  protected function getKeyLocal() {
    return $this->key_local;
  }
  
  protected function getKeyForeign() {
    return $this->key_foreign;
  }
}
