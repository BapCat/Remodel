<?php declare(strict_types = 1); namespace BapCat\Remodel;

use BapCat\Propifier\PropifierTrait;

/**
 * Configuration options for fields
 *
 * @property-read  string    $alias     The name of this field
 * @property-read  string    $type      The fully-qualified class name of a value object
 * @property-read  callable  $callback  The database column this field maps to
 */
class VirtualField {
  use PropifierTrait;

  /** @var  string  $alias */
  private $alias;

  /** @var  string  $type */
  private $type;

  /** @var  callable  $callback */
  private $callback;

  /**
   * @param  string    $alias
   * @param  string    $type
   * @param  callable  $callback
   */
  public function __construct(string $alias, string $type, callable $callback) {
    $this->alias    = $alias;
    $this->type     = $type;
    $this->callback = $callback;
  }

  /**
   * @return  string
   */
  protected function getAlias(): string {
    return $this->alias;
  }

  /**
   * @return  string
   */
  protected function getType(): string {
    return $this->type;
  }

  /**
   * @return  callable
   */
  protected function getCallback(): callable {
    return $this->callback;
  }
}
