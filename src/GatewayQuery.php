<?php namespace BapCat\Remodel;

use Illuminate\Database\Query\Builder;

class GatewayQuery {
  private $gateway;
  private $builder;
  private $from_db;
  private $to_db;
  private $virtual;
  
  public function __construct($gateway, Builder $builder, array $to_db, array $from_db, array $virtual) {
    $this->gateway = $gateway;
    $this->builder = $builder;
    $this->to_db   = $to_db;
    $this->from_db = $from_db;
    $this->virtual = $virtual;
  }
  
  public function get() {
    // Map explicit column names
    if($this->builder->columns !== null) {
      foreach($this->builder->columns as &$column) {
        if(array_key_exists($column, $this->to_db)) {
          $column = $this->to_db[$column];
        } elseif(array_key_exists($column, $this->virtual)) {
          $mapping = $this->virtual[$column];
          
          if(!is_array($mapping)) {
            $column = $mapping;
          } else {
            $concat = 'CONCAT(';
            
            foreach($mapping as $part) {
              $concat .= $part . ',';
            }
            
            $concat = substr($concat, 0, strlen($concat) - 1) . ") AS $column";
            $column = $this->builder->raw($concat);
          }
        }
      }
    }
    
    // Map where column names
    if($this->builder->wheres !== null) {
      foreach($this->builder->wheres as &$where) {
        if(array_key_exists($where['column'], $this->to_db)) {
          $where['column'] = $this->to_db[$where['column']];
        }
      }
    }
    
    $rows = $this->builder->get();
    
    $mapped = [];
    
    foreach($rows as $row) {
      $data = [];
      
      foreach($row as $col => $value) {
        if(array_key_exists($col, $this->from_db)) {
          $data[$this->from_db[$col]] = $value;
        } else {
          $data[$col] = $value;
        }
      }
      
      $mapped[] = $data;
    }
    
    return $mapped;
  }
  
  public function __call($name, array $args) {
    $this->builder = call_user_func_array([$this->builder, $name], $args);
    return $this;
  }
}
