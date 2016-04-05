<?php namespace BapCat\Remodel\Relations;

use BapCat\Propifier\PropifierTrait;

class ManyToMany {
  use PropifierTrait;
  
  private $alias_join;
  private $alias_left;
  private $alias_right;
  private $entity_join;
  private $entity_left;
  private $entity_right;
  private $id_left;
  private $id_right;
  private $key_left;
  private $key_right;
  
  public function __construct($alias_join, $alias_left, $entity_join, $entity_left, $alias_right, $entity_right) {
    $this->alias_join  = $alias_join;
    $this->alias_left  = $alias_left;
    $this->alias_right = $alias_right;
    
    $this->entity_join  = $entity_join;
    $this->entity_left  = $entity_left;
    $this->entity_right = $entity_right;
  }
  
  public function idLeft($id) {
    $this->id_left = $id;
  }
  
  public function idRight($id) {
    $this->id_right = $id;
  }
  
  public function keyLeft($key) {
    $this->key_left = $key;
  }
  
  public function keyRight($key) {
    $this->key_right = $key;
  }
  
  protected function getAliasJoin() {
    return $this->alias_join;
  }
  
  protected function getAliasLeft() {
    return $this->alias_left;
  }
  
  protected function getAliasRight() {
    return $this->alias_right;
  }
  
  protected function getEntityJoin() {
    return $this->entity_join;
  }
  
  protected function getEntityLeft() {
    return $this->entity_left;
  }
  
  protected function getEntityRight() {
    return $this->entity_right;
  }
  
  protected function getIdLeft() {
    return $this->id_left;
  }
  
  protected function getIdRight() {
    return $this->id_right;
  }
  
  protected function getKeyLeft() {
    return $this->key_left;
  }
  
  protected function getKeyRight() {
    return $this->key_right;
  }
}
