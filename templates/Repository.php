<<?= '?php' ?> namespace <?= $namespace ?>;

<?php

use function BapCat\Remodel\camelize;
use function BapCat\Remodel\pluralize;

if(!function_exists('repoVirtualToParam')) {
  function repoVirtualToParam(array $def) {
    return "&\${$def['alias']}";
  }

  function repoVirtualsToParams(array $defs) {
    return implode(', ', array_map(function($def) {
      return repoVirtualToParam($def);
    }, $defs));
  }
}

?>

class <?= $name ?>Repository {
  private $ioc;
  private $entity;
  private $gateway;
  private $scopes = [];
  private $order_bys = [];
  private $limit = 0;
  private $relations = false;
  
  private static $REQUIRED = [
<?php foreach(array_merge([$id], $required) as $def): ?>
    '<?= $def->alias ?>' => \<?= $def->type ?>::class,
<?php endforeach; ?>
  ];
  
  private static $OPTIONAL = [
<?php foreach($optional as $def): ?>
    '<?= $def->alias ?>' => \<?= $def->type ?>::class,
<?php endforeach; ?>
  ];
  
  private static $SELECT_FIELDS = [
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
    '<?= $def->alias ?>',
<?php endforeach; ?>
<?php foreach($virtual as $def): ?>
    '<?= $def['alias'] ?>',
<?php endforeach; ?>
  ];
  
  private static $READ_ONLY = [
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
<?php if($def->read_only): ?>
    '<?= $def->alias ?>',
<?php endif; ?>
<?php endforeach; ?>
  ];
  
  public function __construct(\BapCat\Interfaces\Ioc\Ioc $ioc, \<?= $namespace ?>\<?= $name ?>Gateway $gateway) {
    $this->ioc     = $ioc;
    $this->entity  = \<?= $namespace ?>\<?= $name ?>::class;
    $this->gateway = $gateway;
  }
  
  public function reset() {
    $this->scopes    = [];
    $this->order_bys = [];
    $this->limit     = 0;
    $this->relations = false;
  }
  
  private function buildQuery() {
    $query = $this
      ->gateway
      ->query()
      ->select(static::$SELECT_FIELDS)
    ;
    
    foreach($this->scopes as $col => $value) {
      if(is_array($value)) {
        $query = $query->whereIn($col, $value);
        continue;
      }
   	  
      $query = $query->where($col, $value);
    }
    
    foreach($this->order_bys as $order_by) {
      $query = $query->orderBy($order_by);
    }
    
    return $query;
  }
  
  private function buildEntity(array $raw) {
    $className = $this->entity;
    
    $required = [];
    
    foreach(self::$REQUIRED as $col => $type) {
      $required[$col] = $this->ioc->make($type, [$raw[$col]]);
    }
    
    $entity = $this->ioc->call([$className, 'fromRepository'], array_merge($required, [function(<?= repoVirtualsToParams($virtual) ?>) use($raw) {
<?php foreach($virtual as $def): ?>
      if($raw['<?= $def['alias'] ?>'] !== null) {
        $<?= $def['alias'] ?> = $this->ioc->make(\<?= $def['type'] ?>::class, [$raw['<?= $def['alias'] ?>']]);
      }
<?php endforeach; ?>
    }]));
    
    foreach(self::$OPTIONAL as $col => $type) {
      if($raw[$col] !== null) {
        $entity->$col = $this->ioc->make($type, [$raw[$col]]);
      }
    }
    
    if($this->relations) {
      $entity->cacheRelations();
    }
    
    return $entity;
  }
  
  public function save(\<?= $namespace ?>\<?= $name ?> $entity) {
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
  
  public function delete() {
    $query = $this
      ->gateway
      ->query()
    ;
    
    foreach($this->scopes as $col => $value) {
      $query = $query->where($col, $value);
    }
    
    $query->delete();
    
    $this->reset();
  }
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
  
  public function with<?= camelize($def->alias) ?>(\<?= $def->type ?> $<?= $def->alias ?>) {
    $this->scopes['<?= $def->alias ?>'] = $<?= $def->alias ?>;
    return $this;
  }
<?php endforeach; ?>
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
  
  public function withMany<?= pluralize(camelize($def->alias)) ?>(array $<?= $def->alias ?>) {
    $this->scopes['<?= $def->alias ?>'] = $<?= $def->alias ?>;
    return $this;
  }
<?php endforeach; ?>
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
  
  public function orderBy<?= camelize($def->alias) ?>() {
    $this->order_bys[] = '<?= $def->alias ?>';
    return $this;
  }
<?php endforeach; ?>
  
  public function limit($count) {
    $this->limit = $count;
    return $this;
  }
  
  public function withRelations() {
    $this->relations = true;
    return $this;
  }
  
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
  
  public function first() {
    $this->limit(1);
    $entities = $this->get();
    
    if(count($entities) === 0) {
      throw new <?= $name ?>NotFoundException();
    }
    
    return $entities[0];
  }
}
