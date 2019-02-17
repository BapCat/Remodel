<?php declare(strict_types=1);

use BapCat\Remodel\EntityDefinition;
use BapCat\Values\Timestamp;

use PHPUnit\Framework\TestCase;
use Some\Framework\Entity;

class EntityDefinitionTest extends TestCase {
  /** @var EntityDefinition $def */
  private $def;

  public function setUp(): void {
    parent::setUp();
    $this->def = new EntityDefinition(Entity::class);
  }

  public function testGuesses(): void {
    $this->assertSame(Entity::class, $this->def->full_name);

    $split = explode('\\', Entity::class);
    $this->assertSame(array_pop($split), $this->def->name);
    $this->assertSame(implode('\\', $split), $this->def->namespace);

    $this->assertSame('entities', $this->def->table);

    $this->assertSame(Entity::class . 'Id', $this->def->id->type);
  }

  public function testTable(): void {
    $this->def->table('test_table');

    $this->assertSame('test_table', $this->def->table);
  }

  public function testId(): void {
    $this->def->id('test_id');

    $this->assertSame('test_id', $this->def->id->type);
  }

  public function testRequired(): void {
    $this->def->required('required1', 'required1_type');
    $this->def->required('required2', 'required2_type');
    $this->def->required('required3', 'required3_type');

    $this->assertCount(3, $this->def->required);

    foreach($this->def->required as $index => $required) {
      $this->assertSame($index, $required->alias);
      $this->assertSame($index . '_type', $required->type);
    }
  }

  public function testOptional(): void {
    $this->def->optional('optional1', 'optional1_type');
    $this->def->optional('optional2', 'optional2_type');
    $this->def->optional('optional3', 'optional3_type');

    $this->assertCount(3, $this->def->optional);

    foreach($this->def->optional as $index => $optional) {
      $this->assertSame($index, $optional->alias);
      $this->assertSame($index . '_type', $optional->type);
    }
  }

  public function testTimestamps(): void {
    $this->def->timestamps();

    $this->assertCount(2, $this->def->optional);

    $this->assertSame('created_at', $this->def->optional['created_at']->alias);
    $this->assertSame(Timestamp::class, $this->def->optional['created_at']->type);

    $this->assertSame('updated_at', $this->def->optional['updated_at']->alias);
    $this->assertSame(Timestamp::class, $this->def->optional['updated_at']->type);
  }
}
