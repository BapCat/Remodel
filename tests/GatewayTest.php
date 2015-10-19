<?php

use BapCat\Phi\Phi;
use BapCat\Persist\Drivers\Filesystem\FilesystemDriver;
use BapCat\Remodel\RemodelTemplateFinder;

class GatewayTest extends PHPUnit_Framework_TestCase {
  public function setUp() {
    $ioc = Phi::instance();
    
    $ioc->bind(RemodelTemplateFinder::class, function() {
      $persist = new FilesystemDriver(__DIR__);
      $compiled  = $persist->get('/cache');
      
      return new RemodelTemplateFinder($compiled);
    });
    
    $pdo = new PDO('mysql::memory:;Version=3;New=True');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $connection = new SQLiteConnection($pdo);
  }
}
