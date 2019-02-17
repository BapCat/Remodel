<?php

use BapCat\Remodel\EntityDefinitionOptions;
use PHPUnit\Framework\TestCase;

class EntityDefinitionOptionsTest extends TestCase {
  /** @var EntityDefinitionOptions $def */
  private $def;

  public function setUp(): void {
    parent::setUp();
    $this->def = new EntityDefinitionOptions('alias', 'type');
  }

  public function testAliasAndRawAreTheSame(): void {
    $this->assertSame('alias', $this->def->alias);
    $this->assertSame('alias', $this->def->raw);
  }

  public function testAccessors(): void {
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
