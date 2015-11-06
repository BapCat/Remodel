<?php namespace BapCat\Remodel;

use BapCat\Interfaces\Ioc\Ioc;
use BapCat\Interfaces\Persist\Directory;
use BapCat\Tailor\Compilers\Compiler;
use BapCat\Tailor\Compilers\NomPreprocessor;
use BapCat\Tailor\Tailor;
use ICanBoogie\Inflector;

class Registry {
  private static $globalFunctionsRegistered = false;
  
  private $tailor;
  
  public function __construct(Ioc $ioc, Directory $cache_dir) {
    $finder       = $ioc->make(RemodelTemplateFinder::class, [$cache_dir]);
    $preprocessor = $ioc->make(NomPreprocessor::class);
    $compiler     = $ioc->make(Compiler::class);
    
    $this->tailor = $ioc->make(Tailor::class, [$finder, $preprocessor, $compiler]);
    
    if(!self::$globalFunctionsRegistered) {
      self::$globalFunctionsRegistered = true;
      
      function titlize($input) {
        return Inflector::get()->camelize($input);
      }
      
      function camelize($input) {
        return Inflector::get()->camelize($input, true);
      }
      
      function underscore($input) {
        return Inflector::get()->underscore($input);
      }
      
      function pluralize($input) {
        return Inflector::get()->pluralize($input);
      }
      
      function singularize($input) {
        return Inflector::get()->singularize($input);
      }
      
      function humanize($input) {
        return Inflector::get()->humanize($input);
      }
    }
  }
  
  public function register(EntityDefinition $builder) {
    $options = [
      'namespace' => $builder->namespace,
      'name'      => $builder->name,
      'table'     => $builder->table,
      'id'        => $builder->id,
      'required'  => $builder->required,
      'optional'  => $builder->optional,
      'virtual'   => $builder->virtual
    ];
    
    $this->tailor->bind($builder->fullname, 'Entity', $options);
    $this->tailor->bind($builder->fullname . 'Id', 'Id', $options);
    $this->tailor->bind($builder->fullname . 'Gateway', 'Gateway', $options);
    $this->tailor->bind($builder->fullname . 'Repository', 'Repository', $options);
    $this->tailor->bind($builder->fullname . 'NotFoundException', 'NotFoundException', $options);
  }
}
