<?php namespace BapCat\Remodel;

use BapCat\Remodel\Relations\HasManyThrough;
use BapCat\Remodel\Relations\ManyToMany;
use BapCat\Remodel\Relations\Relation;

use BapCat\Propifier\PropifierTrait;
use BapCat\Values\Timestamp;

use function BapCat\Remodel\pluralize;
use function BapCat\Remodel\underscore;

class EntityDefinition {
  use PropifierTrait;
  
  private $full_name;
  private $name;
  private $namespace;
  private $table;
  private $id;
  private $required = [];
  private $optional = [];
  private $has_many = [];
  private $has_many_through = [];
  private $belongs_to = [];
  private $many_to_many = [];
  private $scopes = [];
  
  public function __construct($name) {
    $split = explode('\\', $name);
    
    $this->full_name = $name;
    $this->name = array_pop($split);
    $this->namespace = implode('\\', $split);
    $this->table(pluralize(underscore($this->name)));
    $this->id($name . 'Id');
  }
  
  public function table($table) {
    $this->table = $table;
  }
  
  public function id($type) {
    return $this->id = new EntityDefinitionOptions('id', $type);
  }
  
  public function required($alias, $type) {
    return $this->required[$alias] = new EntityDefinitionOptions($alias, $type);
  }
  
  public function optional($alias, $type) {
    return $this->optional[$alias] = new EntityDefinitionOptions($alias, $type);
  }
  
  public function timestamps() {
    $this->optional('created_at', Timestamp::class)->readOnly();
    $this->optional('updated_at', Timestamp::class)->readOnly();
  }
  
  public function hasMany($alias, $entity) {
    return $this->has_many[$entity] = new Relation($alias, $this->full_name, $entity);
  }
  
  public function hasManyThrough($alias, $entity_join, $entity_foreign) {
    return $this->has_many_through[] = new HasManyThrough($alias, $entity_join, $entity_foreign);
  }
  
  public function belongsTo($alias, $entity) {
    return $this->belongs_to[$entity] = new Relation($alias, $this->full_name, $entity);
  }
  
  public function associates($alias_join, $alias_left, $entity_left, $alias_right, $entity_right) {
    return $this->many_to_many[] = new ManyToMany($alias_join, $alias_left, $this->full_name, $entity_left, $alias_right, $entity_right);
  }
  
  public function scope($name, callable $callback) {
    $this->scopes[$name] = $callback;
  }
  
  protected function getFullName() {
    return $this->full_name;
  }
  
  protected function getName() {
    return $this->name;
  }
  
  protected function getNamespace() {
    return $this->namespace;
  }
  
  protected function getTable() {
    return $this->table;
  }
  
  protected function getId() {
    return $this->id;
  }
  
  protected function getRequired() {
    return $this->required;
  }
  
  protected function getOptional() {
    return $this->optional;
  }
  
  protected function getHasMany() {
    return $this->has_many;
  }
  
  protected function getHasManyThrough() {
    return $this->has_many_through;
  }
  
  protected function getBelongsTo() {
    return $this->belongs_to;
  }
  
  protected function getManyToMany() {
    return $this->many_to_many;
  }
  
  protected function getScopes() {
    return $this->scopes;
  }
  
  public function toArray() {
    return [
      'namespace'  => $this->namespace,
      'name'       => $this->name,
      'table'      => $this->table,
      'id'         => $this->id,
      'required'   => $this->required,
      'optional'   => $this->optional,
      'has_many'   => $this->has_many,
      'has_many_through' => $this->has_many_through,
      'belongs_to' => $this->belongs_to,
      'scopes'     => array_keys($this->scopes)
    ];
  }
}
