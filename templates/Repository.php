<<?= '?php' ?> namespace <?= $namespace ?>;

<?php /*use function BapCat\Remodel\titlize;*/ ?>

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
  
  public function __construct(\BapCat\Interfaces\Ioc\Ioc $ioc, \<?= $namespace ?>\<?= $name ?>Gateway $gateway) {
    $this->ioc     = $ioc;
    $this->entity  = \<?= $namespace ?>\<?= $name ?>::class;
    $this->gateway = $gateway;
  }
  
  private function buildQuery() {
    $query = $this->gateway->query();
    
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
    
    $entity = $this->ioc->execute($className, 'from', $required);
    
    foreach(self::$OPTIONAL as $col => $type) {
      $entity->$col = $this->ioc->make($type, [$raw[$col]]);
    }
    
    return $entity;
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
    //TODO: do not assume there will be results
    
    $this->limit(1);
    return $this->get()[0];
  }
}
