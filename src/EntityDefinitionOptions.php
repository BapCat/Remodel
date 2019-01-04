<?php declare(strict_types=1); namespace BapCat\Remodel;

use BapCat\Propifier\PropifierTrait;

/**
 * Configuration options for fields
 *
 * @property-read  string  $alias      The name of this field
 * @property-read  string  $type       The fully-qualified class name of a value object
 * @property-read  string  $raw        The database column this field maps to
 * @property-read  bool    $read_only  Is this column read-only?
 */
class EntityDefinitionOptions {
  use PropifierTrait;

  /** @var  string  $alias */
  private $alias;

  /** @var  string  $type */
  private $type;

  /** @var  string  $raw */
  private $raw;

  /** @var  bool  $read_only */
  private $read_only = false;

  /**
   * @param  string  $alias
   * @param  string  $type
   */
  public function __construct(string $alias, string $type) {
    $this->alias = $alias;
    $this->type  = $type;
    $this->raw   = $alias;
  }

  /**
   * Specify the database column this field maps to
   *
   * @param  string  $raw
   *
   * @return  self
   */
  public function mapsTo(string $raw): self {
    $this->raw = $raw;
    return $this;
  }

  /**
   * Mark this field as read only
   *
   * @return  self
   */
  public function readOnly(): self {
    $this->read_only = true;
    return $this;
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
   * @return  string
   */
  protected function getRaw(): string {
    return $this->raw;
  }

  /**
   * @return  bool
   */
  protected function getReadOnly(): bool {
    return $this->read_only;
  }
}
