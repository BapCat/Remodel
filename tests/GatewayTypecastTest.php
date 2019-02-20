<?php declare(strict_types = 1);

use BapCat\Remodel\GatewayQuery;
use BapCat\Remodel\RemodelTestTrait;
use BapCat\Values\Timestamp;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;

class GatewayTypecastTest extends TestCase {
  use RemodelTestTrait;

  /** @var string[] $no_mapping */
  private $no_mapping;

  /** @var string[] $no_mapping_types */
  private $no_mapping_types;

  public function setUp(): void {
    parent::setUp();

    $this->no_mapping = [
      'string'    => 'string',
      'big_int'   => 'big_int',
      'reg_int'   => 'reg_int',
      'med_int'   => 'med_int',
      'small_int' => 'small_int',
      'tiny_int'  => 'tiny_int',
      'float'     => 'float',
      'double'    => 'double',
      'timestamp' => 'timestamp',
    ];

    $this->no_mapping_types = [
      'timestamp' => Timestamp::class,
    ];

    $this->setUpRemodel($this->no_mapping);

    $this->createTable('test', function(Blueprint $table): void {
      $table->string('string', 256);
      $table->bigInteger('big_int');
      $table->integer('reg_int');
      $table->mediumInteger('med_int');
      $table->smallInteger('small_int');
      $table->tinyInteger('tiny_int');
      $table->float('float');
      $table->double('double');
      $table->timestamp('timestamp')->default($this->connection->raw('CURRENT_TIMESTAMP'));
    });

    $this->table('test')->insert([
      'string'    => 'string',
      'big_int'   => 1000000000000000,
      'reg_int'   => 1000000000,
      'med_int'   => 1000000,
      'small_int' => 1000,
      'tiny_int'  => 1,
      'float'     => 0.01,
      'double'    => 0.12345678987654321,
      'timestamp' => 10000000,
    ]);

    $this->newQuery();
  }

  private function newQuery(): GatewayQuery {
    return new GatewayQuery($this->connection, 'test', $this->no_mapping, $this->no_mapping_types, []);
  }

  public function testTypes(): void {
    $data = $this->newQuery()->first();

    $this->assertThat(
      $data['string'],
      new IsType(IsType::TYPE_STRING)
    );

    $this->assertThat(
      $data['big_int'],
      new IsType(IsType::TYPE_INT)
    );

    $this->assertThat(
      $data['reg_int'],
      new IsType(IsType::TYPE_INT)
    );

    $this->assertThat(
      $data['med_int'],
      new IsType(IsType::TYPE_INT)
    );

    $this->assertThat(
      $data['small_int'],
      new IsType(IsType::TYPE_INT)
    );

    $this->assertThat(
      $data['tiny_int'],
      new IsType(IsType::TYPE_INT)
    );

    $this->assertThat(
      $data['float'],
      new IsType(IsType::TYPE_FLOAT)
    );

    $this->assertThat(
      $data['double'],
      new IsType(IsType::TYPE_FLOAT)
    );

    $this->assertThat(
      $data['timestamp'],
      new IsType(IsType::TYPE_INT)
    );
  }
}
