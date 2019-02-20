<?php declare(strict_types=1);

use BapCat\Remodel\GatewayQuery;
use BapCat\Remodel\RemodelTestTrait;
use BapCat\Values\Email;
use BapCat\Values\Password;
use BapCat\Values\Text;
use BapCat\Values\Timestamp;

use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

class GatewayQueryTest extends TestCase {
  use RemodelTestTrait;

  /** @var GatewayQuery $query */
  private $query;

  /** @var string[] $no_mapping */
  private $no_mapping;

  /** @var string[] $no_mapping_types */
  private $no_mapping_types;

  public function setUp(): void {
    parent::setUp();

    $this->no_mapping = [
      'id' => 'id',
      'email' => 'email',
      'password' => 'password',
      'name' => 'name',
      'age' => 'age',
      'created_at' => 'created_at',
      'updated_at' => 'updated_at'
    ];

    $this->no_mapping_types = [
      //'id' =>
      'email' => Email::class,
      'password' => Password::class,
      'name' => Text::class,
      //'age' =>
      'created_at' => Timestamp::class,
      'updated_at' => Timestamp::class
    ];

    $this->setUpRemodel($this->no_mapping);

    $this->createTable('users', function(Blueprint $table): void {
      $table->increments('id');
      $table->string('email', 254)->unique();
      $table->string('password', 255);
      $table->string('name', 100)->nullable();
      $table->integer('age');
      $table->timestamp('created_at')->default($this->connection->raw('CURRENT_TIMESTAMP'));
      $table->timestamp('updated_at')->default($this->connection->raw('CURRENT_TIMESTAMP'));
    });

    $this->table('users')->insert([
      'email'    => 'test+name@bapcat.com',
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'name'     => 'I Have a Name',
      'age'      => 1
    ]);

    $this->table('users')->insert([
      'email'    => 'test+no-name@bapcat.com',
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'age'      => 2
    ]);

    $this->table('users')->insert([
      'email'    => 'test+no-name-2@bapcat.com',
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'age'      => 3
    ]);

    $this->newQuery();
  }

  private function newQuery(): void {
    $this->query = new GatewayQuery($this->connection, 'users', $this->no_mapping, $this->no_mapping_types, []);
  }

  public function testFind(): void {
    $user = $this->query->find(1);
    $this->assertSame('test+name@bapcat.com', $user['email']);
  }

  public function testGetSimple(): void {
    [$user1, $user2] = $this->query->get();

    $this->assertSame('test+name@bapcat.com', $user1['email']);
    $this->assertTrue(password_verify('password', $user1['password']));
    $this->assertSame('I Have a Name', $user1['name']);

    $this->assertSame('test+no-name@bapcat.com', $user2['email']);
    $this->assertTrue(password_verify('password', $user2['password']));
    $this->assertNull($user2['name']);
  }

  public function testGetSimpleWithWhere(): void {
    $user = $this->query->whereNotNull('name')->get();

    $this->assertCount(1, $user);
    $this->assertSame('test+name@bapcat.com', $user[0]['email']);
  }

  public function testSelectGet(): void {
    $user = $this->query->select('name')->first();

    $this->assertSame('I Have a Name', $user['name']);
  }

  public function testUpdate(): void {
    $this->query->where('id', 1)->update(['name' => 'Test']);

    $user = $this->query->select('name')->where('id', 1)->first();

    $this->assertSame('Test', $user['name']);
  }

  public function testUpdateTimestamp(): void {
    $this->query->where('id', 1)->update(['updated_at' => 100]);

    $user = $this->query->where('id', 1)->first();

    $this->assertSame(100, $user['updated_at']);
  }

  public function testInsert(): void {
    $email = 'test+insert@bapcat.com';

    $this->query->insert([
      'email'    => $email,
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'age'      => 4
    ]);

    $user = $this->query->where('email', $email)->first();

    $this->assertSame($email, $user['email']);
  }

  public function testReplace(): void {
    $email = 'test+replace@bapcat.com';

    $this->query->replace([
      'email'    => $email,
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'age'      => 4,
    ]);

    $user1 = $this->query->where('email', $email)->first();

    $this->assertSame($email, $user1['email']);

    $email = 'test+replace2@bapcat.com';

    $this->query->replace([
      'id'       => $user1['id'],
      'email'    => $email,
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'age'      => 4
    ]);

    $this->newQuery();
    $user2 = $this->query->where('email', $email)->first();

    $this->assertSame($email, $user2['email']);

    $this->assertSame($user1['id'], $user2['id']);
  }

  public function testReplaceGetId(): void {
    $id1 = $this->query->replaceGetId([
      'email'    => 'test+replace@bapcat.com',
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'age'      => 4,
    ]);

    $id2 = $this->query->replaceGetId([
      'id'       => $id1,
      'email'    => 'test+replace2@bapcat.com',
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'age'      => 4
    ]);

    $this->assertSame($id1, $id2);
  }

  public function testCount(): void {
    $count = $this->query->whereNull('name')->count();

    $this->assertSame(2, $count);
  }

  public function testMin(): void {
    $min = $this->query->min('age');

    $this->assertSame(1, $min);
  }

  public function testMax(): void {
    $max = $this->query->max('age');

    $this->assertSame(3, $max);
  }

  public function testSum(): void {
    $sum = $this->query->sum('age');

    $this->assertSame(6, $sum);
  }

  public function testAvg(): void {
    $avg = $this->query->avg('age');

    $this->assertEquals(2, $avg);
  }

  public function testDelete(): void {
    $result = $this->query->where('id', 1)->delete();

    $this->assertSame(1, $result);
  }

  public function testDeleteImplicitId(): void {
    $result = $this->query->delete(1);

    $this->assertSame(1, $result);
  }
}
