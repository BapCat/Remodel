<?php namespace BapCat\Remodel;

use BapCat\Remodel\Relations\Relation;

use BapCat\Propifier\PropifierTrait;
use BapCat\Values\Timestamp;

use function BapCat\Remodel\pluralize;
use function BapCat\Remodel\underscore;

class EntityDefinition {
  use PropifierTrait;
  
  private $fullname;
  private $name;
  private $namespace;
  private $table;
  private $id;
  private $required = [];
  private $optional = [];
  private $virtual = [];
  private $has_many = [];
  private $belongs_to = [];
  
  public function __construct($name) {
    $split = explode('\\', $name);
    
    $this->fullname = $name;
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
    return $this->required[] = new EntityDefinitionOptions($alias, $type);
  }
  
  public function optional($alias, $type) {
    return $this->optional[] = new EntityDefinitionOptions($alias, $type);
  }
  
  public function virtual($alias, $type, $raw) {
    $this->virtual[] = ['raw' => $raw, 'alias' => $alias, 'type' => $type];
  }
  
  public function timestamps() {
    $this->optional('created_at', Timestamp::class)->readOnly();
    $this->optional('updated_at', Timestamp::class)->readOnly();
  }
  
  public function hasMany($alias, $entity) {
    return $this->has_many[] = new Relation($alias, $this->fullname, $entity);
  }
  
  public function belongsTo($alias, $entity) {
    return $this->belongs_to[] = new Relation($alias, $this->fullname, $entity);
  }
  
  protected function getFullname() {
    return $this->fullname;
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
  
  protected function getVirtual() {
    return $this->virtual;
  }
  
  protected function getHasMany() {
    return $this->has_many;
  }
  
  protected function getBelongsTo() {
    return $this->belongs_to;
  }
}
