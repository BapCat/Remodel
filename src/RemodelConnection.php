<?php namespace BapCat\Remodel;

use Illuminate\Database\Connection;
use Illuminate\Database\Grammar;
use Illuminate\Database\Query\Processors\Processor;

use PDO;

class RemodelConnection extends Connection {
  public function __construct(PDO $pdo, Grammar $grammar, Processor $processor) {
    parent::__construct($pdo);
    $this->queryGrammar  = new GrammarWrapper($grammar);
    $this->postProcessor = $processor;
  }

  public function select($query, $bindings = [], $useReadPdo = true) {
    $types = [];
    
    $rows = $this->run($query, $bindings, function($me, $query, $bindings) use($useReadPdo, &$types) {
      if($me->pretending()) {
        return [];
      }
      
      $statement = $this->getPdoForSelect($useReadPdo)->prepare($query);
      $statement->execute($me->prepareBindings($bindings));
      
      for($i = 0; $i < $statement->columnCount(); $i++) {
        $meta = $statement->getColumnMeta($i);
        
        $type = $meta['native_type'];
        
        if(isset($meta['sqlite:decl_type'])) {
          if($meta['sqlite:decl_type'] == 'datetime') {
            $type = $meta['sqlite:decl_type'];
          }
        }
        
        $types[$meta['name']] = strtolower($type);
      }
      
      return $statement->fetchAll();
    });
    
    foreach($rows as &$row) {
      foreach($row as $col => &$value) {
        if($value !== null) {
          switch($types[$col]) {
            case 'long':
            case 'integer':
              $value = (int)$value;
            break;
            
            case 'double':
              $value = (double)$value;
            break;
            
            case 'timestamp':
            case 'datetime':
              $value = strtotime($value);
            break;
          }
        }
      }
    }
    
    return $rows;
  }
}
