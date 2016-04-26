<?php

use BapCat\Remodel\GatewayQuery;
use BapCat\Remodel\RemodelTestTrait;
use BapCat\Values\Email;
use BapCat\Values\Password;
use BapCat\Values\Text;
use BapCat\Values\Timestamp;

use Illuminate\Database\Schema\Blueprint;

class GatewayQueryMappedTest extends PHPUnit_Framework_TestCase {
  use RemodelTestTrait;
  
  private $mapped;
  
  public function setUp() {
    $mappings = [
      'user_name' => 'name',
      'user_age'  => 'age'
    ];
    
    $mapped_types = [
      'name' => Text::class,
      //'age' =>
    ];
    
    $this->setUpRemodel($mappings);
    
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
    
    $this->mapped = new GatewayQuery($this->connection, 'users', $mappings,   $mapped_types);
  }
  
  public function testFindMapped() {
    $user = $this->mapped->find(1);
    $this->assertSame('I Have a Name', $user['user_name']);
  }
  
  public function testGetMapped() {
    list($user1, $user2) = $this->mapped->get('user_name');
    
    $this->assertSame('I Have a Name', $user1['user_name']);
    
    $this->assertNull($user2['user_name']);
  }
  
  public function testGetMappedWithWhere() {
    $user = $this->mapped->whereNotNull('user_name')->get('user_name');
    
    $this->assertCount(1, $user);
    $this->assertSame('I Have a Name', $user[0]['user_name']);
  }
  
  public function testSelectGetMapped() {
    $user = $this->mapped->select('user_name')->first();
    
    $this->assertCount(1, $user);
    $this->assertSame('I Have a Name', $user['user_name']);
  }
  
  public function testUpdateMapped() {
    $this->mapped->where('id', 1)->update(['user_name' => 'Test']);
    
    $user = $this->mapped->select('user_name')->where('id', 1)->first();
    
    $this->assertSame('Test', $user['user_name']);
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
  
  public function testCountMapped() {
    $count = $this->mapped->whereNull('user_name')->count();
    
    $this->assertSame(2, $count);
  }
  
  public function testMinMapped() {
    $min = $this->mapped->min('user_age');
    
    $this->assertSame(1, $min);
  }
  
  public function testMaxMapped() {
    $max = $this->mapped->max('user_age');
    
    $this->assertSame(3, $max);
  }
  
  public function testSumMapped() {
    $sum = $this->mapped->sum('user_age');
    
    $this->assertSame(6, $sum);
  }
  
  public function testAvgMapped() {
    $avg = $this->mapped->avg('user_age');
    
    $this->assertEquals(2, $avg);
  }
}
