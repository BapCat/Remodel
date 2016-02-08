<?php namespace BapCat\Remodel\Relations;

use BapCat\Propifier\PropifierTrait;

use function BapCat\Remodel\underscore;
use function BapCat\Remodel\pluralize;

class HasMany {
  use PropifierTrait;
  
  private $local_entity;
  private $local_key;
  private $foreign_entity;
  private $foreign_entity_short;
  private $foreign_key;
  
  public function __construct($local_entity, $foreign_entity) {
    $this->local_entity   = $local_entity;
    $this->foreign_entity = $foreign_entity;
    
    $parts = explode('\\', $foreign_entity);
    $this->foreign_entity_short = array_pop($parts);
  }
  
  public function localKey($key) {
    $this->local_key = $key;
    return $this;
  }
  
  public function foreignKey($key) {
    $this->foreign_key = $key;
    return $this;
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
  
  protected function getForeignEntityShort() {
    return $this->foreign_entity_short;
  }
  
  protected function getForeignKey() {
    return $this->foreign_key;
  }
}
