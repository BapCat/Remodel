<?php namespace BapCat\Remodel\Relations;

use BapCat\Propifier\PropifierTrait;

/**
 * A relationship between two entities
 *
 * @property-read  string  $alias           The alias of the column
 * @property-read  string  $local_entity    The fully-qualified class name of the local entity
 * @property-read  string  $local_key       The local key
 * @property-read  string  $foreign_entity  The fully-qualified class name of the foreign entity
 * @property-read  string  $foreign_key     The foreign key
 */
class Relation {
  use PropifierTrait;

  /** @var  string  $alias */
  private $alias;

  /** @var  string  $local_entity */
  private $local_entity;

  /** @var  string  $local_key */
  private $local_key;

  /** @var  string  $foreign_entity */
  private $foreign_entity;

  /** @var  string  $foreign_key */
  private $foreign_key;

  /**
   * @param  string  $alias
   * @param  string  $local_entity
   * @param  string  $foreign_entity
   */
  public function __construct($alias, $local_entity, $foreign_entity) {
    $this->alias          = $alias;
    $this->local_entity   = $local_entity;
    $this->foreign_entity = $foreign_entity;
  }

  /**
   * Set the local key for this relation
   *
   * @param  string  $key
   *
   * @return  self
   */
  public function localKey($key) {
    $this->local_key = $key;
    return $this;
  }

  /**
   * Set the foreign key for this relation
   *
   * @param  string  $key
   *
   * @return  self
   */
  public function foreignKey($key) {
    $this->foreign_key = $key;
    return $this;
  }

  /**
   * @return  string
   */
  protected function getAlias() {
    return $this->alias;
  }

  /**
   * @return  string
   */
  protected function getLocalEntity() {
    return $this->local_entity;
  }

  /**
   * @return  string
   */
  protected function getLocalKey() {
    return $this->local_key;
  }

  /**
   * @return  string
   */
  protected function getForeignEntity() {
    return $this->foreign_entity;
  }

  /**
   * @return  string
   */
  protected function getForeignKey() {
    return $this->foreign_key;
  }
}
