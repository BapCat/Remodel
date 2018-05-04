<?php
/**
 * @var  string  $namespace
 * @var  string  $name
 */
?>

<<?= '?php' ?> namespace <?= $namespace ?>;

use BapCat\Remodel\EntityNotFoundException;

/**
 * Indicates that a `<?= $name ?>` was not found
 */
class <?= $name ?>NotFoundException extends EntityNotFoundException {
  
}
