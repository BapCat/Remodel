<?php namespace BapCat\Remodel;

use Doctrine\DBAL\Driver as DoctrineDriver;
use Illuminate\Database\Connection;
use Illuminate\Database\Grammar;
use Illuminate\Database\Query\Processors\Processor;

use DateTime;
use PDO;

class RemodelConnection extends Connection {
  private $doctrine;
  
  public function __construct(PDO $pdo, Grammar $grammar, Processor $processor, DoctrineDriver $doctrine = null) {
    parent::__construct($pdo);
    $this->queryGrammar  = new GrammarWrapper($grammar);
    $this->postProcessor = $processor;
    
    $this->doctrine = $doctrine;
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
      
      return $statement->fetchAll(PDO::FETCH_ASSOC);
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
              $value = (float)$value;
            break;
            
            case 'timestamp':
            case 'datetime':
              if(strpos($value, '.') === false) {
                $value = strtotime($value);
              } else {
                $value = (float)DateTime::createFromFormat('Y-m-d H:i:s.u', $value)->format('U.u');
              }
            break;
          }
        }
      }
    }
    
    return $rows;
  }
  
  protected function getDoctrineDriver() {
    return $this->doctrine;
  }
}
