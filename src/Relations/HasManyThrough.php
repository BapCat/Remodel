<?php namespace BapCat\Remodel\Relations;

use BapCat\Propifier\PropifierTrait;

/**
 * A has-many-through relationship
 *
 * @property-read  string  $alias
 * @property-read  string  $entity_join
 * @property-read  string  $entity_foreign
 * @property-read  string  $id_local
 * @property-read  string  $id_foreign
 * @property-read  string  $key_local
 * @property-read  string  $key_foreign
 */
class HasManyThrough {
  use PropifierTrait;

  /** @var  string  $alias */
  private $alias;

  /** @var  string  $entity_join */
  private $entity_join;

  /** @var  string  $entity_foreign */
  private $entity_foreign;

  /** @var  string  $id_local */
  private $id_local;

  /** @var  string  $id_foreign */
  private $id_foreign;

  /** @var  string  $key_local */
  private $key_local;

  /** @var  string  $key_foreign */
  private $key_foreign;

  /**
   * @param  string  $alias
   * @param  string  $entity_join
   * @param  string  $entity_foreign
   */
  public function __construct($alias, $entity_join, $entity_foreign) {
    $this->alias = $alias;
    
    $this->entity_join    = $entity_join;
    $this->entity_foreign = $entity_foreign;
  }

  /**
   * Set the local ID
   *
   * @param  string  $id
   *
   * @return  self
   */
  public function idLocal($id) {
    $this->id_local= $id;
    return $this;
  }

  /**
   * Set the foreign ID
   *
   * @param  string  $id
   *
   * @return  self
   */
  public function idForeign($id) {
    $this->id_foreign = $id;
    return $this;
  }

  /**
   * Set the local key
   *
   * @param  string  $key
   *
   * @return  self
   */
  public function keyLocal($key) {
    $this->key_local = $key;
    return $this;
  }

  /**
   * Set the foreign key
   *
   * @param  string  $key
   *
   * @return  self
   */
  public function keyForeign($key) {
    $this->key_foreign = $key;
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
  protected function getEntityJoin() {
    return $this->entity_join;
  }

  /**
   * @return  string
   */
  protected function getEntityForeign() {
    return $this->entity_foreign;
  }

  /**
   * @return  string
   */
  protected function getIdLocal() {
    return $this->id_local;
  }

  /**
   * @return  string
   */
  protected function getIdForeign() {
    return $this->id_foreign;
  }

  /**
   * @return  string
   */
  protected function getKeyLocal() {
    return $this->key_local;
  }

  /**
   * @return  string
   */
  protected function getKeyForeign() {
    return $this->key_foreign;
  }
}
