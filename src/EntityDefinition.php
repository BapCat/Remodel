<?php declare(strict_types=1); namespace BapCat\Remodel;

use BapCat\Remodel\Relations\HasManyThrough;
use BapCat\Remodel\Relations\ManyToMany;
use BapCat\Remodel\Relations\Relation;

use BapCat\Propifier\PropifierTrait;
use BapCat\Values\Timestamp;

/**
 * An entity builder
 *
 * @property-read  string  $full_name
 * @property-read  string  $name
 * @property-read  string  $namespace
 * @property-read  TraitDefinition $traits
 * @property-read  string  $table
 * @property-read  EntityDefinitionOptions  $id
 * @property-read  EntityDefinitionOptions[]  $required
 * @property-read  EntityDefinitionOptions[]  $optional
 * @property-read  Relation[]  $has_many
 * @property-read  HasManyThrough[]  $has_many_through
 * @property-read  Relation[]  $belongs_to
 * @property-read  ManyToMany[]  $many_to_many
 * @property-read  callable[]  $scopes
 * @property-read  VirtualField[]  $virtuals
 */
class EntityDefinition {
  use PropifierTrait;

  /** @var  string  $full_name */
  private $full_name;

  /** @var  string  $name */
  private $name;

  /** @var  string  $namespace */
  private $namespace;

  /** @var TraitDefinition|null $traits */
  private $traits;

  /** @var  string  $table */
  private $table;

  /** @var  EntityDefinitionOptions  $id */
  private $id;

  /** @var  EntityDefinitionOptions[]  $required */
  private $required = [];

  /** @var  EntityDefinitionOptions[]  $optional */
  private $optional = [];

  /** @var  Relation[]  $has_many */
  private $has_many = [];

  /** @var  HasManyThrough[]  $has_many_through */
  private $has_many_through = [];

  /** @var  Relation[]  $belongs_to */
  private $belongs_to = [];

  /** @var  ManyToMany[]  $many_to_many */
  private $many_to_many = [];

  /** @var  callable[]  $scopes */
  private $scopes = [];

  /** @var  VirtualField[]  $virtuals */
  private $virtuals = [];

  /**
   * @param  string  $name  The fully-qualified name of the `Entity` class
   */
  public function __construct(string $name) {
    $split = explode('\\', $name);

    $this->full_name = $name;
    $this->name = array_pop($split);
    $this->namespace = implode('\\', $split);
    $this->table(pluralize(underscore($this->name)));
    $this->id($name . 'Id');
  }

  /**
   * Sets the database table name
   *
   * @param  string  $table
   *
   * @return  void
   */
  public function table(string $table): void {
    $this->table = $table;
  }

  /**
   * Sets the ID type
   *
   * @param  string  $type  A value object class name
   *
   * @return  EntityDefinitionOptions
   */
  public function id(string $type): EntityDefinitionOptions {
    return $this->id = new EntityDefinitionOptions('id', $type);
  }

  /**
   * Adds a required column
   *
   * @param  string  $alias  The name of the column (does not have to match the database)
   * @param  string  $type   A value object class name
   *
   * @return  EntityDefinitionOptions  Options for the column
   */
  public function required(string $alias, string $type): EntityDefinitionOptions {
    return $this->required[$alias] = new EntityDefinitionOptions($alias, $type);
  }

  /**
   * Adds an optional column
   *
   * @param  string  $alias  The name of the column (does not have to match the database)
   * @param  string  $type   A value object class name
   *
   * @return  EntityDefinitionOptions  Options for the column
   */
  public function optional(string $alias, string $type): EntityDefinitionOptions {
    return $this->optional[$alias] = new EntityDefinitionOptions($alias, $type);
  }

  /**
   * Adds `created_at` and `updated_at` timestamps
   *
   * @return  void
   */
  public function timestamps(): void {
    $this->optional('created_at', Timestamp::class)->readOnly();
    $this->optional('updated_at', Timestamp::class)->readOnly();
  }

  /**
   * Add a `has many` relationship
   *
   * @param  string  $alias   The name of the local column
   * @param  string  $entity  The fully-qualified class name of the related `Entity`
   *
   * @return  Relation  Options for the relation
   */
  public function hasMany(string $alias, string $entity): Relation {
    return $this->has_many[$entity] = new Relation($alias, $this->full_name, $entity);
  }

  /**
   * Add a `has many through` relationship
   *
   * @param  string  $alias           The name of the local column
   * @param  string  $entity_join     The fully-qualified class name of the intermediate `Entity`
   * @param  string  $entity_foreign  The fully-qualified class name of the foreign `Entity`
   *
   * @return  HasManyThrough  Options for the relation
   */
  public function hasManyThrough(string $alias, string $entity_join, string $entity_foreign): HasManyThrough {
    return $this->has_many_through[] = new HasManyThrough($alias, $entity_join, $entity_foreign);
  }

  /**
   * Add a `belongs to` relationship
   *
   * @param  string  $alias   The name of the local column
   * @param  string  $entity  The fully-qualified class name of the related `Entity`
   *
   * @return  Relation  Options for the relation
   */
  public function belongsTo(string $alias, string $entity): Relation {
    return $this->belongs_to[$entity] = new Relation($alias, $this->full_name, $entity);
  }

  /**
   * Adds a `has many through` relationship to the left and right `Entity`s through this `Entity`
   *
   * @param  string  $alias_join    The name of the join column
   * @param  string  $alias_left    The name of the left column
   * @param  string  $entity_left   The fully-qualified class name of the left `Entity`
   * @param  string  $alias_right   The name of the right column
   * @param  string  $entity_right  The fully-qualified class name of the right `Entity`
   *
   * @return  ManyToMany  Options for the relation
   */
  public function associates(string $alias_join, string $alias_left, string $entity_left, string $alias_right, string $entity_right): ManyToMany {
    return $this->many_to_many[] = new ManyToMany($alias_join, $alias_left, $this->full_name, $entity_left, $alias_right, $entity_right);
  }
  
  /**
   * Specify Traits to add to this `Entity`; For more information: {@see TraitDefinition}
   *
   * @param TraitDefinition $traits
   *
   * @return void
   */
  public function traits(TraitDefinition $traits): void {
    $this->traits = $traits;
  }

  /**
   * Adds a scope to to the `Repository`.  The `callable` will be executed whenever
   * the scope is used, and passed the GatewayQuery object.  It may append to the
   * query in any way it pleases.
   *
   * @param  string    $name      The name of the scope
   * @param  callable  $callback  Receives a GatewayQuery object, and all other arguments that the caller passes to the scope
   *
   * @return  void
   */
  public function scope(string $name, callable $callback): void {
    $this->scopes[$name] = $callback;
  }

  /**
   * Adds a virtual field to the `Entity`.  The `callable` will be executed whenever
   * the virtual field is accessed, and passed the `Entity`.
   *
   * @param  string    $name
   * @param  string    $type
   * @param  callable  $callback
   */
  public function virtual(string $name, string $type, callable $callback): void {
    $this->virtuals[$name] = new VirtualField($name, $type, $callback);
  }

  /**
   * @return  string
   */
  protected function getFullName(): string {
    return $this->full_name;
  }

  /**
   * @return  string
   */
  protected function getName(): string {
    return $this->name;
  }

  /**
   * @return  string
   */
  protected function getNamespace(): string {
    return $this->namespace;
  }
  
  /** @return TraitDefinition|null */
  protected function getTraits(): ?TraitDefinition {
    return $this->traits;
  }
  
  /**
   * @return  string
   */
  protected function getTable(): string {
    return $this->table;
  }

  /**
   * @return  EntityDefinitionOptions
   */
  protected function getId(): EntityDefinitionOptions {
    return $this->id;
  }

  /**
   * @return  EntityDefinitionOptions[]
   */
  protected function getRequired(): array {
    return $this->required;
  }

  /**
   * @return  EntityDefinitionOptions[]
   */
  protected function getOptional(): array {
    return $this->optional;
  }

  /**
   * @return  Relation[]
   */
  protected function getHasMany(): array {
    return $this->has_many;
  }

  /**
   * @return  HasManyThrough[]
   */
  protected function getHasManyThrough(): array {
    return $this->has_many_through;
  }

  /**
   * @return  Relation[]
   */
  protected function getBelongsTo(): array {
    return $this->belongs_to;
  }

  /**
   * @return  ManyToMany[]
   */
  protected function getManyToMany(): array {
    return $this->many_to_many;
  }

  /**
   * @return  callable[]
   */
  protected function getScopes(): array {
    return $this->scopes;
  }

  /**
   * @return  VirtualField[]
   */
  protected function getVirtuals(): array {
    return $this->virtuals;
  }

  /**
   * @return  array
   */
  public function toArray(): array {
    return [
      'namespace'  => $this->namespace,
      'traits'     => $this->traits,
      'name'       => $this->name,
      'table'      => $this->table,
      'id'         => $this->id,
      'required'   => $this->required,
      'optional'   => $this->optional,
      'has_many'   => $this->has_many,
      'has_many_through' => $this->has_many_through,
      'belongs_to' => $this->belongs_to,
      'scopes'     => array_keys($this->scopes),
      'virtuals'   => $this->virtuals,
    ];
  }
}
