<?php

use BapCat\Remodel\EntityDefinition;
use BapCat\Values\Timestamp;

use Some\Framework\Entity;

class EntityDefinitionTest extends PHPUnit_Framework_TestCase {
  private $def;
  
  public function setUp() {
    $this->def = new EntityDefinition(Entity::class);
  }
  
  public function testGuesses() {
    $this->assertSame(Entity::class, $this->def->fullname);
    
    $split = explode('\\', Entity::class);
    $this->assertSame(array_pop($split), $this->def->name);
    $this->assertSame(implode('\\', $split), $this->def->namespace);
    
    $this->assertSame('entities', $this->def->table);
    
    $this->assertSame(Entity::class . 'Id', $this->def->id->type);
  }
  
  public function testTable() {
    $this->def->table('test_table');
    
    $this->assertSame('test_table', $this->def->table);
  }
  
  public function testId() {
    $this->def->id('test_id');
    
    $this->assertSame('test_id', $this->def->id->type);
  }
  
  public function testRequired() {
    $this->def->required('required1', 'required1_type');
    $this->def->required('required2', 'required2_type');
    $this->def->required('required3', 'required3_type');
    
    $this->assertCount(3, $this->def->required);
    
    foreach($this->def->required as $index => $required) {
      $this->assertSame('required' . ($index + 1), $required->alias);
      $this->assertSame('required' . ($index + 1) . '_type', $required->type);
    }
  }
  
  public function testOptional() {
    $this->def->optional('optional1', 'optional1_type');
    $this->def->optional('optional2', 'optional2_type');
    $this->def->optional('optional3', 'optional3_type');
    
    $this->assertCount(3, $this->def->optional);
    
    foreach($this->def->optional as $index => $optional) {
      $this->assertSame('optional' . ($index + 1), $optional->alias);
      $this->assertSame('optional' . ($index + 1) . '_type', $optional->type);
    }
  }
  
  public function testTimestamps() {
    $this->def->timestamps();
    
    $this->assertCount(2, $this->def->optional);
    
    $this->assertSame('created_at', $this->def->optional[0]->alias);
    $this->assertSame(Timestamp::class, $this->def->optional[0]->type);
    
    $this->assertSame('updated_at', $this->def->optional[1]->alias);
    $this->assertSame(Timestamp::class, $this->def->optional[1]->type);
  }
}
