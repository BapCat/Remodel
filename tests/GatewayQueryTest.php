<?php

use BapCat\Remodel\GatewayQuery;

use BapCat\Hashing\PasswordHash;
use BapCat\Hashing\Algorithms\BcryptPasswordHasher;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;

class GatewayQueryTest extends PHPUnit_Framework_TestCase {
  private $query;
  
  public function setUp() {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $connection = new SQLiteConnection($pdo);
    $connection->setFetchMode(PDO::FETCH_ASSOC);
    
    $connection->getSchemaBuilder()->create('users', function(Blueprint $table) use($connection) {
      $table->increments('id');
      $table->string('email', 254)->unique();
      $table->string('password', 255);
      $table->string('name', 100)->nullable();
      $table->timestamp('created_at')->default($connection->raw('CURRENT_TIMESTAMP'));
      $table->timestamp('updated_at')->default($connection->raw('CURRENT_TIMESTAMP'));
    });
    
    $connection->table('users')->insert([
      'email'    => 'test+name@bapcat.com',
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'name'     => 'I Have a Name'
    ]);
    
    $connection->table('users')->insert([
      'email'    => 'test+no-name@bapcat.com',
      'password' => password_hash('password', PASSWORD_DEFAULT),
    ]);
    
    $mappings = [
      'user_name' => 'name',
    ];
    
    $this->query  = new GatewayQuery($connection, 'users', [], [], []);
    $this->mapped = new GatewayQuery($connection, 'users', $mappings, array_flip($mappings), []);
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
  
  public function testGetMapped() {
    list($user1, $user2) = $this->mapped->get('user_name');
    
    $this->assertSame('I Have a Name', $user1['user_name']);
    
    $this->assertNull($user2['user_name']);
  }
  
  public function testGetIntIsInt() {
    $user = $this->query->first();
    
    $this->assertInternalType('int', $user['id']);
  }
  
  public function testGetTimestampIsInt() {
    $user = $this->query->first();
    
    $this->assertInternalType('int', $user['created_at']);
  }
  
  public function testGetMappedWithWhere() {
    $user = $this->mapped->whereNotNull('user_name')->get('user_name');
    
    $this->assertCount(1, $user);
    $this->assertSame('I Have a Name', $user[0]['user_name']);
  }
  
  public function testSelectGet() {
    $user = $this->query->select('name')->first();
    
    $this->assertCount(1, $user);
    $this->assertSame('I Have a Name', $user['name']);
  }
  
  public function testSelectGetMapped() {
    $user = $this->mapped->select('user_name')->first();
    
    $this->assertCount(1, $user);
    $this->assertSame('I Have a Name', $user['user_name']);
  }
  
  public function testUpdate() {
    $this->query->where('id', 1)->update(['name' => 'Test']);
    
    $user = $this->query->select('name')->where('id', 1)->first();
    
    $this->assertSame('Test', $user['name']);
  }
  
  public function testUpdateMapped() {
    $this->mapped->where('id', 1)->update(['user_name' => 'Test']);
    
    $user = $this->mapped->select('user_name')->where('id', 1)->first();
    
    $this->assertSame('Test', $user['user_name']);
  }
  
  public function testUpdateTimestamp() {
    $this->query->where('id', 1)->update(['updated_at' => 100]);
    
    $user = $this->query->where('id', 1)->first();
    
    $this->assertSame(100, $user['updated_at']);
  }
}
