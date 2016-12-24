<?php namespace BapCat\Remodel;

use BapCat\Interfaces\Ioc\Ioc;
use BapCat\Hashing\Algorithms\Sha1WeakHasher;
use BapCat\Nom\Compiler;
use BapCat\Nom\NomTransformer;
use BapCat\Nom\Pipeline;
use BapCat\Persist\Directory;
use BapCat\Persist\Drivers\Local\LocalDriver;
use BapCat\Tailor\Generator;
use BapCat\Tailor\Tailor;

class Registry {
  const CLASS_SUFFIXES = ['Id', 'Gateway', 'Repository', 'NotFoundException'];
  
  /**
   * @var  Ioc  $ioc
   */
  private $ioc;
  
  /**
   * @var  Tailor  $tailor
   */
  private $tailor;
  
  /**
   * @var  array<string, EntityDefinition>  $defs
   */
  private $defs = [];
  
  /**
   * @var  EntityDefinition[]  $unchecked
   */
  private $unchecked = [];
  
  /**
   * @param  Ioc        $ioc
   * @param  Directory  $cache  Where to cache generated classes
   */
  public function __construct(Ioc $ioc, Directory $cache) {
    $this->ioc = $ioc;
    
    $preprocessor = $ioc->make(NomTransformer::class);
    $compiler     = $ioc->make(Compiler::class);
    $pipeline     = $ioc->make(Pipeline::class, [$cache, $compiler, [$preprocessor]]);
    
    $filesystem = new LocalDriver(__DIR__ . '/../templates');
    $templates  = $filesystem->getDirectory('/');
    
    $hasher = new Sha1WeakHasher();
    
    $this->tailor = $ioc->make(Tailor::class, [$templates, $cache, $pipeline, $hasher]);
  }
  
  /**
   * Register an Entity definition.  Note this method DOES NOT generate the classes.  They are generated when needed.
   * 
   * @param  EntityDefinition  $builder
   * 
   * @return  void
   */
  public function register(EntityDefinition $builder) {
    $this->defs[$builder->full_name] = $builder;
    $this->unchecked[] = $builder;
    
    $this->ioc->bind('bap.remodel.scopes.' . str_replace('\\', '.', $builder->full_name), function() use($builder) {
      return $builder->scopes;
    });
    
    $this->tailor->bindCallback($builder->full_name, function(Generator $gen) use($builder) {
      $this->checkDefinitions();
      
      $file = $gen->generate('Entity', $builder->toArray());
      $gen->includeFile($file);
    });
    
    foreach(static::CLASS_SUFFIXES as $class) {
      $this->tailor->bindCallback($builder->full_name . $class, function(Generator $gen) use($builder, $class) {
        $this->checkDefinitions();
        
        $file = $gen->generate($class, $builder->toArray());
        $gen->includeFile($file);
      });
    }
  }
  
  /**
   * Forces the pre-generation of every registered Entity (and supporting classes, eg. Repositories)
   * 
   * Note: for the sake of your IDE, it's a good idea to clear your cache directory before doing this
   * 
   * @return  void
   */
  public function generateAll() {
    $this->checkDefinitions();
    
    foreach($this->defs as $def) {
      class_exists($def->full_name);
      
      foreach(static::CLASS_SUFFIXES as $class) {
        class_exists($def->full_name . $class);
      }
    }
  }
  
  /**
   * Links up any definitions that were left open for Remodel to interpret
   * 
   * @return  void
   */
  private function checkDefinitions() {
    // Add many to many stuff if necessary
    foreach($this->unchecked as $def) {
      foreach($def->many_to_many as $relation) {
        //echo "Processing many to many for {$def->name}\n";
        
        // Set up left keys
        $related = $this->defs[$relation->entity_left];
        
        if(empty($relation->id_left)) {
          //echo "Setting {$relation->alias_join} id_left to {$related->id->alias}\n";
          $relation->idLeft($related->id->alias);
        }
        
        if(empty($relation->key_left)) {
          //echo "Setting {$relation->alias_join} key_left to " . keyify($related->name) . "\n";
          $relation->keyLeft(keyify($related->name));
        }
        //
        
        // Set up right keys
        $related = $this->defs[$relation->entity_right];
        
        if(empty($relation->id_right)) {
          //echo "Setting {$relation->alias_join} id_right to {$related->id->alias}\n";
          $relation->idRight($related->id->alias);
        }
        
        if(empty($relation->key_right)) {
          //echo "Setting {$relation->alias_join} key_right to " . keyify($related->name) . "\n";
          $relation->keyRight(keyify($related->name));
        }
        //
        
        // Left side relations
        $related = $this->defs[$relation->entity_left];
        
        //echo "Adding {$related->name} hasManyThrough {$relation->alias_join} to {$relation->alias_right}\n";
        $related->hasManyThrough(pluralize($relation->alias_right), $relation->entity_join, $relation->entity_right)
          ->idLocal($relation->id_left)
          ->idForeign($relation->id_right)
          ->keyLocal($relation->key_left)
          ->keyForeign($relation->key_right)
        ;
        
        if(!array_key_exists($relation->alias_join, $def->has_many)) {
          //echo "Adding {$related->name} hasMany {$def->name}\n";
          $related->hasMany($relation->alias_join, $def->full_name)
            ->localKey  ($relation->id_left)
            ->foreignKey($relation->key_left)
          ;
        }
        
        if(!array_key_exists($relation->alias_left, $def->belongs_to)) {
          //echo "Adding {$def->name} belongsTo {$related->name}\n";
          $def->belongsTo($relation->alias_left, $related->full_name);
        }
        //
        
        // Right side relations
        $related = $this->defs[$relation->entity_right];
        
        //echo "Adding {$related->name} hasManyThrough {$relation->alias_join} to {$relation->alias_left}\n";
        $related->hasManyThrough(pluralize($relation->alias_left), $relation->entity_join, $relation->entity_left)
          ->idLocal($relation->id_right)
          ->idForeign($relation->id_left)
          ->keyLocal($relation->key_right)
          ->keyForeign($relation->key_left)
        ;
        
        if(!array_key_exists($relation->alias_join, $def->has_many)) {
          //echo "Adding {$related->name} hasMany {$def->name}\n";
          $related->hasMany($relation->alias_join, $def->full_name)
            ->localKey  ($relation->id_right)
            ->foreignKey($relation->key_right)
          ;
        }
        
        if(!array_key_exists($relation->alias_right, $def->belongs_to)) {
          //echo "Adding {$def->name} belongsTo {$related->name}\n";
          $def->belongsTo($relation->alias_right, $related->full_name);
        }
        //
      }
    }
    
    // Add has many stuff if necessary
    foreach($this->unchecked as $def) {
      foreach($def->has_many as $relation) {
        //echo "Processing has many for {$def->name}\n";
        
        $related = $this->defs[$relation->foreign_entity];
        
        if(empty($relation->local_key)) {
          //echo "Setting {$relation->alias} local_key to {$def->id->alias}\n";
          $relation->localKey($def->id->alias);
        }
        
        if(empty($relation->foreign_key)) {
          //echo "Setting {$relation->alias} foreign_key to " . keyify($related->name) . "\n";
          $relation->foreignKey(keyify($related->name));
        }
      }
    }
    
    // Add belongs to stuff if necessary
    foreach($this->unchecked as $def) {
      foreach($def->belongs_to as $relation) {
        //echo "Processing belongs to for {$def->name}\n";
        
        $related = $this->defs[$relation->foreign_entity];
        
        if(empty($relation->local_key)) {
          //echo "Setting {$relation->alias} local_key to " . keyify($related->name) . "\n";
          $relation->localKey(keyify($related->name));
        }
        
        if(empty($relation->foreign_key)) {
          //echo "Setting {$relation->alias} foreign_key to {$def->id->alias}\n";
          $relation->foreignKey($related->id->alias);
        }
        
        if(!array_key_exists($relation->local_key, $related->required)) {
          //echo "Adding {$def->name} column {$relation->local_key} of type {$related->id->type}\n";
          $def->required($relation->local_key, $related->id->type);
        }
      }
    }
    
    // We've checked everything in the queue.  Clear it out.
    $this->unchecked = [];
  }
}
