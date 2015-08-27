<<?= '?php' ?> namespace <?= $namespace ?>;

<?php

/*use function BapCat\Remodel\titlize;*/

if(!function_exists('repoVirtualToParam')) {
  function repoVirtualToParam(array $def) {
    return "&\${$def['alias']}";
  }

  function repoVirtualsToParams(array $defs) {
    $args = '';
    
    foreach($defs as $i => $def) {
      $args .= repoVirtualToParam($def);
      
      if($i < count($defs) - 1) {
        $args .= ', ';
      }
    }
    
    return $args;
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
  
  public function __construct(\BapCat\Interfaces\Ioc\Ioc $ioc, \<?= $namespace ?>\<?= $name ?>Gateway $gateway) {
    $this->ioc     = $ioc;
    $this->entity  = \<?= $namespace ?>\<?= $name ?>::class;
    $this->gateway = $gateway;
  }
  
  private function buildQuery() {
    $query = $this
      ->gateway
      ->query()
      ->select(static::$SELECT_FIELDS)
    ;
    
    foreach($this->scopes as $col => $value) {
      $query = $query->where($col, $value);
    }
    
    foreach($this->order_bys as $order_by) {
      $query = $query->orderBy($order_by);
    }
    
    $scopes = [];
    
    return $query;
  }
  
  private function buildEntity(array $raw) {
    $className = $this->entity;
    
    $required = [];
    
    foreach(self::$REQUIRED as $col => $type) {
      $required[$col] = $this->ioc->make($type, [$raw[$col]]);
    }
    
    $entity = $this->ioc->execute($className, 'fromRepository', array_merge($required, [function(<?= repoVirtualsToParams($virtual) ?>) use($raw) {
<?php foreach($virtual as $def): ?>
      $<?= $def['alias'] ?> = $this->ioc->make(\<?= $def['type'] ?>::class, [$raw['<?= $def['alias'] ?>']]);
<?php endforeach; ?>
    }]));
    
    foreach(self::$OPTIONAL as $col => $type) {
      $entity->$col = $this->ioc->make($type, [$raw[$col]]);
    }
    
    return $entity;
  }
  
  public function save(\<?= $namespace ?>\<?= $name ?> $entity) {
    $fields = [];
    
    foreach(self::$REQUIRED as $alias => $type) {
      $fields[$alias] = $entity->$alias;
      
      if($fields[$alias] !== null) {
        $fields[$alias] = $fields[$alias]->raw;
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
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
  
  public function with<?= \BapCat\Remodel\titlize($def->alias) ?>(\<?= $def->type ?> $<?= $def->alias ?>) {
    $this->scopes['<?= $def->alias ?>'] = $<?= $def->alias ?>;
    return $this;
  }
<?php endforeach; ?>
<?php foreach(array_merge([$id], $required, $optional) as $def): ?>
  
  public function orderBy<?= \BapCat\Remodel\titlize($def->alias) ?>() {
    $this->order_bys[] = '<?= $def->alias ?>';
    return $this;
  }
<?php endforeach; ?>
  
  public function limit($count) {
    $this->limit = $count;
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
