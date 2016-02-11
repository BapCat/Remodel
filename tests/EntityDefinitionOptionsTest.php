<?php

use BapCat\Remodel\EntityDefinitionOptions;

class EntityDefinitionOptionsTest extends PHPUnit_Framework_TestCase {
  private $def;
  
  public function setUp() {
    $this->def = new EntityDefinitionOptions('alias', 'type');
  }
  
  public function testAliasAndRawAreTheSame() {
    $this->assertSame('alias', $this->def->alias);
    $this->assertSame('alias', $this->def->raw);
  }
  
  public function testAccessors() {
    $this->assertSame('alias', $this->def->alias);
    $this->assertSame('type',  $this->def->type);
    $this->assertSame('alias', $this->def->raw);
    $this->assertFalse($this->def->read_only);
    
    $this->def->mapsTo('mapped');
    $this->assertSame('mapped', $this->def->raw);
    
    $this->def->readOnly();
    $this->assertTrue($this->def->read_only);
  }
}
