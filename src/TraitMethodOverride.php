<?php declare(strict_types=1); namespace BapCat\Remodel;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class TraitMethodOverride - Allows you to specify your own trait conflict override.
 *
 * @link    http://php.net/manual/en/language.oop5.traits.php
 *
 * @package BapCat\Remodel
 */
class TraitMethodOverride {
  
  /**
   * TraitUseStatement constructor.  Should not be constructed outside of {@see TraitDefinition::use_method()}.
   *
   * @param string $traitName
   * @param string $method
   */
  public function __construct(string $traitName, string $method) {
    $this->traitName = $traitName;
    $this->method = $method;
  }
  
  /**
   * Give this method an alias for use within this class.
   *
   * @param string $alias
   *
   * @return TraitMethodOverride
   */
  public function as_alias(string $alias): self {
    $this->alias = $alias;
    
    return $this;
  }
  
  /**
   * Specify the trait which is having it's method overridden.
   *
   * @param string $traitName
   *
   * @return TraitMethodOverride
   */
  public function instead_of(string $traitName): self {
    $namePosition = strrpos($traitName, '\\');
    $this->insteadOf = $namePosition ? substr($traitName, $namePosition + 1) : $traitName;
    
    return $this;
  }
  
  /**
   * Specify the visibility of the method.
   *
   * @param string $visibility
   *
   * @return TraitMethodOverride
   */
  public function with_visibility(string $visibility): self {
    if ( ! in_array($visibility, ['public', 'private', 'protected']) ) {
      throw new InvalidArgumentException("Visibility[$visibility] must be one of public, private, protected");
    }
    $this->visibility = $visibility;
    
    return $this;
  }
  
  /**
   * Return the defined trait resolution as a use statement, for use within a class definition.
   *
   * @return string
   */
  public function __toString(): string {
    
    $source = "{$this->traitName}::{$this->method}";
    
    if ( $this->insteadOf ) {
      return "{$source} insteadof {$this->insteadOf}";
    }
    
    $visibility = $this->visibility ?: '';
    $alias = $this->alias ?: '';
    $spacer = ( $visibility && $alias ) ? ' ' : '';
    
    if ( ! $visibility && ! $alias ) {
      throw new RuntimeException('Must call at least one of: instead_of(), as_alias(), with_visibility()');
    }
    
    return "{$source} as {$visibility}{$spacer}{$alias}";
  }
  
  /** @var string $traitName */
  private $traitName;
  
  /** @var string $method */
  private $method;
  
  /** @var string $insteadOf */
  private $insteadOf;
  
  /** @var string $alias */
  private $alias;
  
  /** @var string $visibility */
  private $visibility;
  
}
