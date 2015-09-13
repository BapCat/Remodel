<?php namespace BapCat\Remodel;

use BapCat\Interfaces\Persist\Directory;
use BapCat\Persist\Drivers\Filesystem\FilesystemDriver;
use BapCat\Tailor\PersistTemplateFinder;

class RemodelTemplateFinder extends PersistTemplateFinder {
  public function __construct(Directory $compiled) {
    $filesystem = new FilesystemDriver(__DIR__ . '/../templates');
    $templates  = $filesystem->get('/');
    
    parent::__construct($templates, $compiled);
  }
}
