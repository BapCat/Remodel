<?php namespace BapCat\Remodel;

use BapCat\Interfaces\Ioc\Ioc;
use BapCat\Hashing\Algorithms\Sha1WeakHasher;
use BapCat\Nom\Compiler;
use BapCat\Nom\NomPreprocessor;
use BapCat\Persist\Directory;
use BapCat\Persist\Drivers\Local\LocalDriver;
use BapCat\Tailor\Tailor;

class Registry {
  private $tailor;
  
  public function __construct(Ioc $ioc, Directory $cache_dir) {
    $preprocessor = $ioc->make(NomPreprocessor::class);
    $compiler     = $ioc->make(Compiler::class);
    
    $filesystem = new LocalDriver(__DIR__ . '/../templates');
    $templates  = $filesystem->getDirectory('/');
    
    $hasher = new Sha1WeakHasher();
    
    $this->tailor = $ioc->make(Tailor::class, [$templates, $cache_dir, $compiler, $hasher]);
    $this->tailor->addPreprocessor($preprocessor);
  }
  
  public function register(EntityDefinition $builder) {
    $options = [
      'namespace'  => $builder->namespace,
      'name'       => $builder->name,
      'table'      => $builder->table,
      'id'         => $builder->id,
      'required'   => $builder->required,
      'optional'   => $builder->optional,
      'virtual'    => $builder->virtual,
      'has_many'   => $builder->has_many,
      'belongs_to' => $builder->belongs_to
    ];
    
    $this->tailor->bind($builder->fullname, 'Entity', $options);
    $this->tailor->bind($builder->fullname . 'Id', 'Id', $options);
    $this->tailor->bind($builder->fullname . 'Gateway', 'Gateway', $options);
    $this->tailor->bind($builder->fullname . 'Repository', 'Repository', $options);
    $this->tailor->bind($builder->fullname . 'NotFoundException', 'NotFoundException', $options);
  }
}
