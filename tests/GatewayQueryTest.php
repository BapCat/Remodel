<?php

use BapCat\Remodel\GatewayQuery;
use BapCat\Remodel\RemodelTestTrait;
use BapCat\Values\Email;
use BapCat\Values\Password;
use BapCat\Values\Text;
use BapCat\Values\Timestamp;

use Illuminate\Database\Schema\Blueprint;

class GatewayQueryTest extends PHPUnit_Framework_TestCase {
  use RemodelTestTrait;
  
  private $query;
  
  public function setUp() {
    $no_mapping = [
      'id' => 'id',
      'email' => 'email',
      'password' => 'password',
      'name' => 'name',
      'age' => 'age',
      'created_at' => 'created_at',
      'updated_at' => 'updated_at'
    ];
    
    $no_mapping_types = [
      //'id' =>
      'email' => Email::class,
      'pasword' => Password::class,
      'name' => Text::class,
      //'age' =>
      'created_at' => Timestamp::class,
      'updated_at' => Timestamp::class
    ];
    
    $this->setUpRemodel($no_mapping);
    
    $this->createTable('users', function(Blueprint $table) {
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
    
    $this->query = new GatewayQuery($this->connection, 'users', $no_mapping, $no_mapping_types);
  }
  
  public function testFind() {
    $user = $this->query->find(1);
    $this->assertSame('test+name@bapcat.com', $user['email']);
  }
  
  public function testGetSimple() {
    list($user1, $user2) = $this->query->get();
    
    $this->assertSame('test+name@bapcat.com', $user1['email']);
    $this->assertTrue(password_verify('password', $user1['password']));
    $this->assertSame('I Have a Name', $user1['name']);
    
    $this->assertSame('test+no-name@bapcat.com', $user2['email']);
    $this->assertTrue(password_verify('password', $user2['password']));
    $this->assertNull($user2['name']);
  }
  
  public function testGetSimpleWithWhere() {
    $user = $this->query->whereNotNull('name')->get();
    
    $this->assertCount(1, $user);
    $this->assertSame('test+name@bapcat.com', $user[0]['email']);
  }
  
  public function testGetIntIsInt() {
    $user = $this->query->first();
    
    $this->assertInternalType('int', $user['id']);
  }
  
  public function testGetTimestampIsInt() {
    $user = $this->query->first();
    
    $this->assertInternalType('int', $user['created_at']);
  }
  
  public function testSelectGet() {
    $user = $this->query->select('name')->first();
    
    $this->assertCount(1, $user);
    $this->assertSame('I Have a Name', $user['name']);
  }
  
  public function testUpdate() {
    $this->query->where('id', 1)->update(['name' => 'Test']);
    
    $user = $this->query->select('name')->where('id', 1)->first();
    
    $this->assertSame('Test', $user['name']);
  }
  
  public function testUpdateTimestamp() {
    $this->query->where('id', 1)->update(['updated_at' => 100]);
    
    $user = $this->query->where('id', 1)->first();
    
    $this->assertSame(100, $user['updated_at']);
  }
  
  public function testInsert() {
    $email = 'test+insert@bapcat.com';
    
    $this->query->insert([
      'email'    => $email,
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'age'      => 4
    ]);
    
    $user = $this->query->where('email', $email)->first();
    
    $this->assertSame($email, $user['email']);
  }
  
  public function testCount() {
    $count = $this->query->whereNull('name')->count();
    
    $this->assertSame(2, $count);
  }
  
  public function testMin() {
    $min = $this->query->min('age');
    
    $this->assertSame(1, $min);
  }
  
  public function testMax() {
    $max = $this->query->max('age');
    
    $this->assertSame(3, $max);
  }
  
  public function testSum() {
    $sum = $this->query->sum('age');
    
    $this->assertSame(6, $sum);
  }
  
  public function testAvg() {
    $avg = $this->query->avg('age');
    
    $this->assertEquals(2, $avg);
  }
  
  public function testDelete() {
    $result = $this->query->where('id', 1)->delete();
    
    $this->assertSame(1, $result);
  }
  
  public function testDeleteImplicitId() {
    $result = $this->query->delete(1);
    
    $this->assertSame(1, $result);
  }
}
