<?php

use BapCat\Remodel\GatewayQuery;

use BapCat\Hashing\PasswordHash;
use BapCat\Hashing\Algorithms\BcryptPasswordHasher;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;

class GatewayQueryTest extends PHPUnit_Framework_TestCase {
  private $query;
  private $mapped;
  private $mapped_id;
  
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
      $table->integer('age');
      $table->timestamp('created_at')->default($connection->raw('CURRENT_TIMESTAMP'));
      $table->timestamp('updated_at')->default($connection->raw('CURRENT_TIMESTAMP'));
    });
    
    $connection->table('users')->insert([
      'email'    => 'test+name@bapcat.com',
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'name'     => 'I Have a Name',
      'age'      => 1
    ]);
    
    $connection->table('users')->insert([
      'email'    => 'test+no-name@bapcat.com',
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'age'      => 2
    ]);
    
    $connection->table('users')->insert([
      'email'    => 'test+no-name-2@bapcat.com',
      'password' => password_hash('password', PASSWORD_DEFAULT),
      'age'      => 3
    ]);
    
    $no_mapping = [
        'id' => 'id',
        'email' => 'email',
        'password' => 'password',
        'name' => 'name',
        'age' => 'age',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at'
    ];
    
    $mappings = [
      'user_name' => 'name',
      'user_age'  => 'age'
    ];
    
    $mapped_id = [
      'user_id' => 'id'
    ];
    
    $this->query     = new GatewayQuery($connection, 'users', $no_mapping, array_flip($no_mapping), []);
    $this->mapped    = new GatewayQuery($connection, 'users', $mappings, array_flip($mappings), []);
    $this->mapped_id = new GatewayQuery($connection, 'users', $mapped_id, array_flip($mapped_id), []);
  }
  
  public function testFind() {
    $user = $this->query->find(1);
    $this->assertSame('test+name@bapcat.com', $user['email']);
  }
  
  public function testFindMapped() {
    $user = $this->mapped->find(1);
    $this->assertSame('I Have a Name', $user['user_name']);
  }
  
  public function testFindMappedId() {
    $user = $this->mapped_id->find(1);
    $this->assertSame(1, $user['user_id']);
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
  
  public function testInsertMapped() {
    $name = 'My Name';
    
    $this->mapped->insert([
      'email'     => 'test+insertmapped@bapcat.com',
      'password'  => password_hash('password', PASSWORD_DEFAULT),
      'user_name' => $name,
      'age'       => 5
    ]);
    
    $user = $this->mapped->where('user_name', $name)->first();
    
    $this->assertSame($name, $user['user_name']);
  }
  
  public function testCount() {
    $count = $this->query->whereNull('name')->count();
    
    $this->assertSame(2, $count);
  }
  
  public function testCountMapped() {
    $count = $this->mapped->whereNull('user_name')->count();
    
    $this->assertSame(2, $count);
  }
  
  public function testMin() {
    $min = $this->query->min('age');
    
    $this->assertSame(1, $min);
  }
  
  public function testMinMapped() {
    $min = $this->mapped->min('user_age');
    
    $this->assertSame(1, $min);
  }
  
  public function testMax() {
    $max = $this->query->max('age');
    
    $this->assertSame(3, $max);
  }
  
  public function testMaxMapped() {
    $max = $this->mapped->max('user_age');
    
    $this->assertSame(3, $max);
  }
  
  public function testSum() {
    $sum = $this->query->sum('age');
    
    $this->assertSame(6, $sum);
  }
  
  public function testSumMapped() {
    $sum = $this->mapped->sum('user_age');
    
    $this->assertSame(6, $sum);
  }
  
  public function testAvg() {
    $avg = $this->query->avg('age');
    
    $this->assertSame(2.0, $avg);
  }
  
  public function testAvgMapped() {
    $avg = $this->mapped->avg('user_age');
    
    $this->assertSame(2.0, $avg);
  }
  
  public function testDelete() {
    $result = $this->query->where('id', 1)->delete();
    
    $this->assertSame(1, $result);
  }
  
  public function testDeleteImplicitId() {
    $result = $this->query->delete(1);
    
    $this->assertSame(1, $result);
  }
  
  public function testDeleteMappedId() {
    $result = $this->mapped_id->where('user_id', 1)->delete();
    
    $this->assertSame(1, $result);
  }
  
  public function testDeleteImplicitMappedId() {
    $result = $this->mapped_id->delete(1);
    
    $this->assertSame(1, $result);
  }
}
