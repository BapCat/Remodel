<?php declare(strict_types=1);
/**
 * @var  string  $namespace
 * @var  string  $name
 */
?>

<<?= '?php' ?> declare(strict_types=1); namespace <?= $namespace ?>;

use BapCat\Remodel\EntityNotFoundException;

/**
 * Indicates that a `<?= $name ?>` was not found
 */
class <?= $name ?>NotFoundException extends EntityNotFoundException {

}
