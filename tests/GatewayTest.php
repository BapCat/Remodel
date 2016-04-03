<?php

use BapCat\Persist\Drivers\Local\LocalDriver;
use BapCat\Phi\Phi;
use BapCat\Remodel\EntityDefinition;
use BapCat\Remodel\Registry;

use BapCat\Hashing\PasswordHash;
use BapCat\Values\Email;
use BapCat\Values\Password;
use BapCat\Values\Text;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;

use Test\User;
use Test\UserGateway;
use Test\UserRepository;

class GatewayTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    $persist = new LocalDriver(__DIR__);
    $cache   = $persist->getDirectory('/cache');
    
    $pdo = new PDO('sqlite::memory:');
    //$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $connection = new SQLiteConnection($pdo);
    //$connection = new Illuminate\Database\MysqlConnection($pdo);
    
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
    
    $registry = new Registry(Phi::instance(), $cache);
    
    $def = new EntityDefinition(User::class);
    $def->required('email',    Email::class);
    $def->required('password', PasswordHash::class);
    $def->optional('name',     Text::class);
    $def->timestamps();
    
    $registry->register($def);
    
    $this->gateway = new UserGateway($connection);
  }
  
  public function testGet() {
    list($user1, $user2) = $this->gateway->query()->get();
    
    $this->assertSame('test+name@bapcat.com', $user1['email']);
    $this->assertTrue(password_verify('password', $user1['password']));
    $this->assertSame('I Have a Name', $user1['name']);
    
    $this->assertSame('test+no-name@bapcat.com', $user2['email']);
    $this->assertTrue(password_verify('password', $user2['password']));
    $this->assertNull($user2['name']);
  }
}
