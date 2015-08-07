<<?= '?php' ?> namespace <?= $namespace ?>;

class <?= $name ?>Repository {
  private $ioc;
  private $entity;
  private $gateway;
  private $scopes = [];
  
  private static $REQUIRED = [
<?php foreach(array_merge($ids, $required) as $def): ?>
    '<?= $def['mapped'] ?>' => \<?= $def['type'] ?>::class,
<?php endforeach; ?>
  ];
  
  private static $OPTIONAL = [
<?php foreach($optional as $def): ?>
    '<?= $def['mapped'] ?>' => \<?= $def['type'] ?>::class,
<?php endforeach; ?>
  ];
  
  public function __construct(\BapCat\Interfaces\Ioc\Ioc $ioc, \<?= $namespace ?>\<?= $name ?>Gateway $gateway) {
    $this->ioc     = $ioc;
    $this->entity  = \<?= $namespace ?>\<?= $name ?>::class;
    $this->gateway = $gateway;
  }
<?php foreach(array_merge($required, $optional) as $def): ?>
  
  public function <?= $def['mapped'] ?>(\<?= $def['type'] ?> $<?= $def['mapped'] ?>) {
    $this->scopes['<?= $def['mapped'] ?>'] = $<?= $def['mapped'] ?>;
    return $this;
  }
<?php endforeach; ?>
  
  private function buildQuery() {
    $query = $this->gateway->query();
    
    foreach($this->scopes as $col => $value) {
      $query = $query->where($col, $value);
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
  
  public function get() {
    $raw = $this
      ->buildQuery()
      ->get()
    ;
    
    $entities = [];
    
    foreach($raw as $row) {
      $entities[] = $this->buildEntity($row);
    }
    
    return $entities;
  }
  
  public function first() {
    $raw = $this
      ->buildQuery()
      ->limit(1)
      ->get()
        [0]
    ;
    
    return $this->buildEntity($raw);
  }
}