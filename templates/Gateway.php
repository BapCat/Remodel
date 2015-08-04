<<?= '?php' ?> namespace <?= $namespace ?>;

use Illuminate\Database\Capsule\Manager as Capsule;

class <?= $name ?>Gateway {
  protected static $MAPPINGS = [
    'id'         => 'User_ID',
    'password'   => 'User_Password',
    'is_admin'   => 'IsActive',
    'email'      => 'Email',
    'first_name' => 'First_Name',
    'last_name'  => 'Last_Name'
  ];
  
  protected static $VIRTUAL = [
    'full_email' => ['First_Name', "' '", 'Last_Name', "' <'", 'Email', "'>'"],
    'full_name'  => ['First_Name', "' '", 'Last_Name']
  ];
  
  public function query() {
    return new GatewayQuery(
      $this,
      Capsule::table(<?= $table ?>),
      static::$MAPPINGS,
      array_flip(static::$MAPPINGS),
      static::$VIRTUAL
    );
  }
}
