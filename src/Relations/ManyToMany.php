<?php namespace BapCat\Remodel\Relations;

use BapCat\Propifier\PropifierTrait;

/**
 * A many-to-many relationship
 *
 * @property-read  string  $alias_join
 * @property-read  string  $alias_left
 * @property-read  string  $alias_right
 * @property-read  string  $entity_join
 * @property-read  string  $entity_left
 * @property-read  string  $entity_right
 * @property-read  string  $id_left
 * @property-read  string  $id_right
 * @property-read  string  $key_left
 * @property-read  string  $key_right
 */
class ManyToMany {
  use PropifierTrait;

  /** @var  string  $alias_join */
  private $alias_join;

  /** @var  string  $alias_left */
  private $alias_left;

  /** @var  string $alias_right */
  private $alias_right;

  /** @var  string  $entity_join */
  private $entity_join;

  /** @var  string  $entity_left */
  private $entity_left;

  /** @var  string  $entity_right */
  private $entity_right;

  /** @var  string  $id_left */
  private $id_left;

  /** @var  string  $id_right */
  private $id_right;

  /** @var  string  $key_left */
  private $key_left;

  /** @var  string $key_right */
  private $key_right;

  /**
   * @param  string  $alias_join
   * @param  string  $alias_left
   * @param  string  $entity_join
   * @param  string  $entity_left
   * @param  string  $alias_right
   * @param  string  $entity_right
   */
  public function __construct($alias_join, $alias_left, $entity_join, $entity_left, $alias_right, $entity_right) {
    $this->alias_join  = $alias_join;
    $this->alias_left  = $alias_left;
    $this->alias_right = $alias_right;
    
    $this->entity_join  = $entity_join;
    $this->entity_left  = $entity_left;
    $this->entity_right = $entity_right;
  }

  /**
   * Set the left ID
   *
   * @param  string  $id
   *
   * @return  self
   */
  public function idLeft($id) {
    $this->id_left = $id;
    return $this;
  }

  /**
   * Set the right ID
   *
   * @param  string  $id
   *
   * @return  self
   */
  public function idRight($id) {
    $this->id_right = $id;
    return $this;
  }

  /**
   * Set the left key
   *
   * @param  string  $key
   *
   * @return  self
   */
  public function keyLeft($key) {
    $this->key_left = $key;
    return $this;
  }

  /**
   * Set the right key
   *
   * @param  string  $key
   *
   * @return  self
   */
  public function keyRight($key) {
    $this->key_right = $key;
    return $this;
  }

  /**
   * @return  string
   */
  protected function getAliasJoin() {
    return $this->alias_join;
  }

  /**
   * @return  string
   */
  protected function getAliasLeft() {
    return $this->alias_left;
  }

  /**
   * @return  string
   */
  protected function getAliasRight() {
    return $this->alias_right;
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
  protected function getEntityLeft() {
    return $this->entity_left;
  }

  /**
   * @return  string
   */
  protected function getEntityRight() {
    return $this->entity_right;
  }

  /**
   * @return  string
   */
  protected function getIdLeft() {
    return $this->id_left;
  }

  /**
   * @return  string
   */
  protected function getIdRight() {
    return $this->id_right;
  }

  /**
   * @return  string
   */
  protected function getKeyLeft() {
    return $this->key_left;
  }

  /**
   * @return  string
   */
  protected function getKeyRight() {
    return $this->key_right;
  }
}
