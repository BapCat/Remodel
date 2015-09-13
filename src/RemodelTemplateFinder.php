<?php namespace BapCat\Remodel;

use BapCat\Interfaces\Persist\Directory;
use BapCat\Persist\Drivers\Filesystem\FilesystemDriver;
use BapCat\Tailor\PersistTemplateFinder;

class RemodelTemplateFinder extends PersistTemplateFinder {
  private $templates;
  private $compiled;
  
  public function __construct(Directory $compiled) {
    $filesystem = new FilesystemDriver(__DIR__ . '/../templates');
    $templates  = $persist->get('/');
    
    parent::__construct($templates, $compiled);
  }
}
