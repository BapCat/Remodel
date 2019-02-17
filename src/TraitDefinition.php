<?php declare(strict_types=1); namespace BapCat\Remodel;

/**
 * Class TraitDefinition - Define one or more traits that will be applied to an Entity.
 *
 * @package BapCat\Remodel
 */
class TraitDefinition {
  
  /**
   * TraitDefinition constructor.
   */
  public function __construct() {
  }
  
  /**
   * Add a trait, with an optional alias.
   *
   * @param string      $traitName  The fully-qualified trait name (with namespace).
   * @param string|null $alias      An alias for this trait, if applicable.
   */
  public function add(string $traitName, ?string $alias = null): void {
    
    $this->traits[] = $traitName;
    
    if ( $alias ) {
      $this->aliases[$traitName] = $alias;
    }
  
    $namePosition = strrpos($traitName, '\\');
    $this->names[$traitName] = $namePosition ? substr($traitName, $namePosition + 1) : $traitName;
  }
  
  /**
   * Add a trait override for a specific method.
   *
   * @param string $traitName
   * @param string $method
   *
   * @return TraitMethodOverride
   */
  public function use_method(string $traitName, string $method): TraitMethodOverride {
  
    // Translate the provided trait name into its alias, or just trait name (without namespace).
    if ( isset($this->aliases[$traitName]) ) {
      $traitAlias =  $this->aliases[$traitName];
    } else {
      $traitAlias =  $this->names[$traitName];
    }
    
    $override = new TraitMethodOverride($traitAlias, $method);
    $this->overrides[] = $override;
    
    return $override;
  }
  
  /**
   * Return a string containing all required import statements, generally for directly above a class definition.
   *
   * @return string
   */
  public function imports(): string {
    
    $imports = '';
    
    foreach ( $this->traits as $traitName ) {
      
      $imports .= 'use ' . $traitName;
      
      if ( isset($this->aliases[$traitName]) ) {
        $imports .= ' as ' . $this->aliases[$traitName];
      }
      
      $imports .= ";\n";
    }
    
    return $imports;
  }
  
  /**
   * Return a string containing the use statements for use within a class definition.
   *
   * @return string
   */
  public function use_statement(): string {
    
    $full = '  use ' . implode(', ', $this->names);
    
    if ( $this->overrides ) {
      
      $full .= " { \n";
      
      foreach ( $this->overrides as $override ) {
        $full .= "    {$override};\n";
      }
      
      $full .= "  }\n";
    } else {
      $full .= ';';
    }
    
    return $full;
  }
  
  /** @var TraitMethodOverride[] $overrides */
  private $overrides = [];
  
  /** @var string[] $traits List of FQTN (Fully Qualified Trait Names, i.e. with namespace) */
  private $traits = [];
  
  /** @var string[] $names Key: FQTN, value: Trait name (without namespace) */
  private $names = [];
  
  /** @var string[] $aliases Key: FQTN, value: Trait alias (without namespace) */
  private $aliases = [];
  
}
