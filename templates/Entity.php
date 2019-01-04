<?php declare(strict_types=1); ?>
@php declare(strict_types=1); namespace {! $namespace !};

<?php

use BapCat\Remodel\EntityDefinitionOptions;

if(!function_exists('defToParam')) {
  /**
   * @param  EntityDefinitionOptions  $def
   * @param  bool                     $nullable
   *
   * @return  string
   */
  function defToParam(EntityDefinitionOptions $def, bool $nullable = false): string {
    return "\\{$def->type} \${$def->alias}" . ($nullable ? ' = null' : '');
  }

  /**
   * @param  EntityDefinitionOptions[]  $defs
   * @param  bool                       $nullable
   *
   * @return  string
   */
  function defsToParams(array $defs, bool $nullable = false): string {
    return implode(', ', array_map(function(EntityDefinitionOptions $def) use($nullable) {
      return defToParam($def, $nullable);
    }, $defs));
  }

  /**
   * @param  EntityDefinitionOptions  $def
   *
   * @return  string
   */
  function defToArg(EntityDefinitionOptions $def): string {
    return "\${$def->alias}";
  }

  /**
   * @param  EntityDefinitionOptions[]  $defs
   *
   * @return  string
   */
  function defsToArgs(array $defs): string {
    return implode(', ', array_map(function(EntityDefinitionOptions $def) {
      return defToArg($def);
    }, $defs));
  }
}

?>

use BapCat\Phi\Ioc;
use BapCat\Propifier\PropifierTrait;
use BapCat\Remodel\Entity;

use JsonSerializable;

/**
 * {! $name !} entity
 *
@each(array_merge([$id], $required, $optional) as $def)
@if($def->read_only)
 * @property-read  \{! $def->type !}  ${! $def->alias !}

@else
 * @property  \{! $def->type !}  ${! $def->alias !}

@endif
@endeach
@each($has_many as $relation)
 * @property-read  \{! $relation->foreign_entity !}[]  ${! $relation->alias !}

@endeach
@each($has_many_through as $relation)
 * @property-read  \{! $relation->entity_foreign !}[]  ${! $relation->alias !}

@endeach
@each($belongs_to as $relation)
 * @property-read  \{! $relation->foreign_entity !}[]  ${! $relation->alias !}

@endeach
 */
class {! $name !} implements Entity, JsonSerializable {
  use PropifierTrait;

  public const ID_NAME = '{! $id->alias !}';

  /** @var  Ioc  $ioc */
  private $ioc;
@each(array_merge([$id], $required, $optional) as $def)

  /** @var  \{! $def->type !}  ${! $def->alias !} */
  private ${! $def->alias !};
@endeach
@each($has_many as $relation)

  /** @var  \{! $relation->foreign_entity !}[]  $cache_{! $relation->alias !} */
  private $cache_{! $relation->alias !};
@endeach
@each($has_many_through as $relation)

  /** @var  \{! $relation->entity_foreign !}[]  $cache_{! $relation->alias !} */
  private $cache_{! $relation->alias !};
@endeach

  private function __construct() {
    $this->ioc = Ioc::instance();
  }

  /**
   * Create a new {! $name !}

   *
@each($required as $def)
   * @param  {! defToParam($def) !}

@endeach
   *
   * @return  {! $name !}

   */
  public static function create({! defsToParams($required) !}): {! $name !} {
    return self::make(null, {! defsToArgs($required) !});
  }

  /**
   * Populate a {! $name !} from existing data (requires an ID)
   *
@each(array_merge([$id], $required) as $def)
   * @param  {! defToParam($def) !}

@endeach
   *
   * @return  {! $name !}

   */
  public static function from({! defsToParams(array_merge([$id], $required)) !}): {! $name !} {
    return self::make({! defsToArgs(array_merge([$id], $required)) !});
  }

  /**
   * For internal use only
   *
@each(array_merge([$id], $required) as $def)
   * @param  {! defToParam($def) !}

@endeach
   *
   * @return  {! $name !}

   */
  public static function fromRepository({! defsToParams(array_merge([$id], $required, $optional), true) !}): {! $name !} {
    $entity = static::from({! defsToArgs(array_merge([$id], $required)) !});

@each($optional as $def)
    $entity->{! $def->alias !} = ${! $def->alias !};
@endeach

    return $entity;
  }

  /**
   * Builds an entity based on ALL fields, required or not
   *
@each(array_merge([$id], $required, $optional) as $def)
   * @param  {! defToParam($def) !}

@endeach
   *
   * @return  {! $name !}

   */
  private static function make({! defsToParams(array_merge([$id], $required, $optional), true) !}): {! $name !} {
    $entity = new {! $name !}();
@each(array_merge([$id], $required, $optional) as $def)
    $entity->{! $def->alias !} = ${! $def->alias !};
@endeach

    return $entity;
  }

  /**
   * Cache the relationships of this `{! $name !}`.  This will fetch all related
   * entities up front, rather than one-by-one as they are accessed.
   *
   * @return  void
   */
  public function cacheRelations(): void {
@each($has_many as $relation)
    $this->cache_{! $relation->alias !} = $this->{! $relation->alias !};

    foreach($this->cache_{! $relation->alias !} as $entity) {
      $entity->cacheRelations();
    }
@endeach

@each($has_many_through as $relation)
    $this->cache_{! $relation->alias !} = $this->{! $relation->alias !};

    foreach($this->cache_{! $relation->alias !} as $entity) {
      $entity->cacheRelations();
    }
@endeach
  }
@each(array_merge([$id], $required) as $def)

  /**
   * @return  \{! $def->type !}

   */
  protected function get{! @camelize($def->alias) !}(): ?\{! $def->type !} {
    return $this->{! $def->alias !};
  }
@if(!$def->read_only)

  /**
   * @param  {! defToParam($def) !}

   *
   * @return  void
   */
  protected function set{! @camelize($def->alias) !}({! defToParam($def) !}): void {
    $this->{! $def->alias !} = ${! $def->alias !};
  }
@endif
@endeach
@each($optional as $def)

  /**
   * @return  \{! $def->type !}

   */
  protected function get{! @camelize($def->alias) !}(): ?\{! $def->type !} {
    return $this->{! $def->alias !};
  }
@if(!$def->read_only)

  /**
   * @param  {! defToParam($def) !}

   *
   * @return  void
   */
  protected function set{! @camelize($def->alias) !}({! defToParam($def, true) !}): void {
    $this->{! $def->alias !} = ${! $def->alias !};
  }
@endif
@endeach
@each($has_many as $relation)

  /**
   * @return  \{! $relation->foreign_entity !}[]
   */
  protected function get{! @camelize($relation->alias) !}(): array {
    if($this->cache_{! $relation->alias !} !== null) {
      return $this->cache_{! $relation->alias !};
    }

    /** @var  \{! $relation->foreign_entity !}Repository  $repo */
    $repo = $this->ioc->make(\{! $relation->foreign_entity !}Repository::class);
    return $repo->with{! @camelize($relation->foreign_key) !}($this->{! $relation->local_key !})->get();
  }
@endeach
@each($has_many_through as $relation)

  /**
   * @return  \{! $relation->entity_foreign !}[]
   */
  protected function get{! @camelize($relation->alias) !}(): array {
    /** @var  \{! $relation->entity_join !}Gateway  $gateway */
    $gateway = $this->ioc->make(\{! $relation->entity_join !}Gateway::class);
    $ids = array_column($gateway->query()->select('{! $relation->key_foreign !}')->where('{! $relation->key_local !}', $this->{! $relation->id_local !})->get()->all(), '{! $relation->key_foreign !}');

    /** @var  \{! $relation->entity_foreign !}Repository  $repo */
    $repo = $this->ioc->make(\{! $relation->entity_foreign !}Repository::class);
    return $repo->withMany{! \BapCat\Remodel\pluralize(@camelize($relation->id_foreign)) !}($ids)->get();
  }
@endeach
@each($belongs_to as $relation)

  /**
   * @throws  \{! $relation->foreign_entity !}NotFoundException if the {! @camelize($relation->alias) !} was not found
   *
   * @return  \{! $relation->foreign_entity !}

   */
  protected function get{! @camelize($relation->alias) !}(): \{! $relation->foreign_entity !} {
    /** @var  \{! $relation->foreign_entity !}Repository  $repo */
    $repo = $this->ioc->make(\{! $relation->foreign_entity !}Repository::class);
    return $repo->with{! @camelize($relation->foreign_key) !}($this->{! $relation->local_key !})->first();
  }
@endeach

  /**
   * Save this {! $name !}

   *
   * @return  void
   */
  public function save(): void {
    /** @var  {! $name !}Repository  $repo */
    $repo = $this->ioc->make({! $name !}Repository::class);
    $repo->save($this);
  }

  /**
   * Delete this {! $name !}

   *
   * @return  void
   */
  public function delete(): void {
    /** @var  {! $name !}Repository  $repo */
    $repo = $this->ioc->make({! $name !}Repository::class);
    $repo->withId($this->id)->delete();
  }

  /**
   * @return  string
   */
  public function __toString(): string {
    $output = '{! $namespace !}\{! $name !} ';

    if($this->id === null) {
      return $output . '(new)';
    }

    return $output . $this->id;
  }

  /**
   * @return  array
   */
  public function jsonSerialize(): array {
    $output = [
@each(array_merge([$id], $required, $optional) as $def)
      '{! $def->alias !}' => $this->{! $def->alias !},
@endeach
    ];

@each($has_many as $relation)
    if($this->cache_{! $relation->alias !} !== null) {
      $output['{! $relation->alias !}'] = $this->cache_{! $relation->alias !};
    }
@endeach

@each($has_many_through as $relation)
    if($this->cache_{! $relation->alias !} !== null) {
      $output['{! $relation->alias !}'] = $this->cache_{! $relation->alias !};
    }
@endeach

    return $output;
  }
}
