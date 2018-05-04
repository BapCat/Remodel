<?php

/**
 * @var  string                     $namespace
 * @var  string                     $name
 * @var  EntityDefinitionOptions    $id
 * @var  EntityDefinitionOptions[]  $required
 * @var  EntityDefinitionOptions[]  $optional
 * @var  string[]                   $scopes
 */

use BapCat\Remodel\EntityDefinitionOptions;
use function BapCat\Remodel\camelize;
use function BapCat\Remodel\pluralize;

?>

<<?= '?php' ?> namespace <?= $namespace ?>;

use BapCat\Interfaces\Ioc\Ioc;
use BapCat\Remodel\GatewayQuery;

/**
 * Handles interactions between `<?= $name ?>`s and the database
 */
class <?= $name ?>Repository {
  /** @var  Ioc  $ioc */
  private $ioc;

  /** @var  string  $entity */
  private $entity;

  /** @var  <?= $name ?>Gateway  $gateway */
  private $gateway;

  /** @var  array[]  $scopes */
  private $scopes = [];

  /** @var  string[]  $order_bys */
  private $order_bys = [];

  /** @var  int  $limit */
  private $limit = 0;

  /** @var  bool  $relations */
  private $relations = false;

  /** @var  callable[]  $scope_callbacks */
  private $scope_callbacks = [];

  /** @var  array  $REQUIRED */
  private static $REQUIRED = [
<?php foreach(array_merge([$id], $required) as $def): ?>
    '<?= $def->alias ?>' => \<?= $def->type ?>::class,
<?php endforeach; ?>
  ];

  /** @var  array  $OPTIONAL */
  private static $OPTIONAL = [
<?php foreach($optional as $def): ?>
    '<?= $def->alias ?>' => \<?= $def->type ?>::class,
<?php endforeach; ?>
  ];

  /** @var  array  $SELECT_FIELDS */
  private static $SELECT_FIELDS = [
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
    '<?= $def->alias ?>',
<?php endforeach; ?>
  ];

  /** @var  array  $READ_ONLY */
  private static $READ_ONLY = [
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
<?php if($def->read_only): ?>
    '<?= $def->alias ?>',
<?php endif; ?>
<?php endforeach; ?>
  ];

  /**
   * @param  Ioc  $ioc
   * @param  <?= $name ?>Gateway  $gateway
   */
  public function __construct(Ioc $ioc, <?= $name ?>Gateway $gateway) {
    $this->ioc     = $ioc;
    $this->entity  = <?= $name ?>::class;
    $this->gateway = $gateway;

    $this->scope_callbacks = $ioc->make("bap.remodel.scopes.<?= str_replace('\\', '.', $namespace) ?>.<?= $name ?>");
  }

  /**
   * Reset scopes/order by/limit/etc.
   *
   * @return  void
   */
  public function reset() {
    $this->scopes    = [];
    $this->order_bys = [];
    $this->limit     = 0;
    $this->relations = false;
  }

  /**
   * Build a GatewayQuery based on the state of this Repository
   *
   * @return  GatewayQuery
   */
  private function buildQuery() {
    $query = $this
      ->gateway
      ->query()
      ->select(static::$SELECT_FIELDS)
    ;
    
    foreach($this->scopes as $scope) {
      $query = $scope[0]($query, ...$scope[1]);
    }
    
    foreach($this->order_bys as $order_by) {
      $query = $query->orderBy($order_by);
    }
    
    return $query;
  }

  /**
   * Build a `<?= $name ?>` with an array of raw data
   *
   * @param  array  $raw
   *
   * @return  <?= $name ?>

   */
  private function buildEntity(array $raw) {
    $class_name = $this->entity;

    $params = [];
    foreach(self::$REQUIRED as $col => $type) {
      $params[$col] = $this->ioc->make($type, [$raw[$col]]);
    }

    foreach(self::$OPTIONAL as $col => $type) {
      if($raw[$col] !== null) {
        $params[$col] = $this->ioc->make($type, [$raw[$col]]);
      } else {
        $params[$col] = null;
      }
    }

    $entity = $this->ioc->call([$class_name, 'fromRepository'], $params);

    if($this->relations) {
      $entity->cacheRelations();
    }

    return $entity;
  }

  /**
   * Save a <?= $name ?>

   *
   * @param  <?= $name ?>  $entity
   *
   * @return  void
   */
  public function save(<?= $name ?> $entity) {
    $fields = [];
    
    foreach(self::$REQUIRED as $alias => $type) {
      if(!in_array($alias, static::$READ_ONLY)) {
        $fields[$alias] = $entity->$alias;
        
        if($fields[$alias] !== null) {
          $fields[$alias] = $fields[$alias]->raw;
        }
      }
    }
    
    foreach(self::$OPTIONAL as $alias => $type) {
      if(!in_array($alias, static::$READ_ONLY)) {
        $fields[$alias] = $entity->$alias;
        
        if($fields[$alias] !== null) {
          $fields[$alias] = $fields[$alias]->raw;
        }
      }
    }
    
    $query = $this
      ->gateway
      ->query()
    ;
    
    if($entity->id === null) {
      $entity->id = $this->ioc->make(\<?= $id->type ?>::class, [$query->insertGetId($fields)]);
    } else {
      unset($fields['id']);
      $query
        ->where('<?= $id->alias ?>', $entity->id)
        ->update($fields)
      ;
    }
  }

  /**
   * Delete all entities that this repository currently targets
   *
   * @return  void
   */
  public function delete() {
    $query = $this
      ->gateway
      ->query()
    ;
    
    foreach($this->scopes as $scope) {
      $query = $scope[0]($query, ...$scope[1]);
    }
    
    $query->delete();
    
    $this->reset();
  }
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>

  /**
   * Target entities with a specific `<?= camelize($def->alias) ?>`
   *
   * @param  \<?= $def->type ?>  $value
   *
   * @return  self
   */
  public function with<?= camelize($def->alias) ?>(\<?= $def->type ?> $value) {
    $this->scopes[] = [function(GatewayQuery $query) use($value) {
      return $query->where('<?= $def->alias ?>', $value);
    }, []];
    
    return $this;
  }

  /**
   * Target entities with a specific `<?= camelize($def->alias) ?>` (`OR`ed with previous target)
   *
   * @param  \<?= $def->type ?>  $value
   *
   * @return  self
   */
  public function orWith<?= camelize($def->alias) ?>(\<?= $def->type ?> $value) {
    $this->scopes[] = [function(GatewayQuery $query) use($value) {
      return $query->orWhere('<?= $def->alias ?>', $value);
    }, []];
    
    return $this;
  }

  /**
   * Target entities with specific `<?= camelize($def->alias) ?>`s
   *
   * @param  \<?= $def->type ?>[]  $values
   *
   * @return  self
   */
  public function withMany<?= pluralize(camelize($def->alias)) ?>(array $values) {
    $this->scopes[] = [function(GatewayQuery $query) use($values) {
      return $query->whereIn('<?= $def->alias ?>', $values);
    }, []];
    
    return $this;
  }

  /**
   * Order results by `<?= camelize($def->alias) ?>`
   *
   * @return  self
   */
  public function orderBy<?= camelize($def->alias) ?>() {
    $this->order_bys[] = '<?= $def->alias ?>';
    return $this;
  }
<?php endforeach; ?>
<?php foreach($scopes as $scope): ?>

  /**
   * Use scope `<?= $scope ?>`
   *
   * @param  mixed[]  $args  Values to be passed to the scope
   *
   * @return  self
   */
  public function <?= $scope ?>(...$args) {
    $this->scopes[] = [$this->scope_callbacks['<?= $scope ?>'], $args];
    return $this;
  }
<?php endforeach; ?>

  /**
   * Limit the number of results returned
   *
   * @param  int  $count
   *
   * @return  self
   */
  public function limit($count) {
    $this->limit = $count;
    return $this;
  }

  /**
   * Fetch relations along with `<?= $name ?>`s.  This will fetch all related
   * entities up front, rather than one-by-one as they are accessed.
   *
   * @return  self
   */
  public function withRelations() {
    $this->relations = true;
    return $this;
  }

  /**
   * Get the `<?= $name ?>`s targeted by this repository from the database
   *
   * @return  <?= $name ?>[]
   */
  public function get() {
    $raw = $this->buildQuery();
    
    if($this->limit !== 0) {
      $raw = $raw->limit($this->limit);
    }
    
    $raw = $raw->get();
    
    $entities = [];
    
    foreach($raw as $row) {
      $entities[] = $this->buildEntity($row);
    }
    
    $this->reset();
    
    return $entities;
  }

  /**
   * Get the first `<?= $name ?>` targeted by this repository from the database
   *
   * @throws  <?= $name ?>NotFoundException  If no `<?= $name ?>` was found
   *
   * @return  <?= $name ?>

   */
  public function first() {
    $this->limit(1);
    $entities = $this->get();
    
    if(count($entities) === 0) {
      throw new <?= $name ?>NotFoundException();
    }
    
    return $entities[0];
  }
}
