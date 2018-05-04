<?php
/**
 * @var  string  $namespace
 * @var  string  $name
 */
?>

<<?= '?php' ?> namespace <?= $namespace ?>;

use BapCat\Interfaces\Values\Value;
use InvalidArgumentException;

/**
 * A `<?= $name ?>Id` value object
 *
 * @property-read  int  $raw  The raw value this object wraps
 */
class <?= $name ?>Id extends Value {
  /** @var  int  $id */
  private $id;

  /**
   * @param  int  $id
   */
  public function __construct($id) {
    $this->validate($id);
    $this->id = (integer)$id;
  }

  /**
   * Ensures the ID is an integer greater than 0
   *
   * @throws  InvalidArgumentException  If the ID is invalid
   *
   * @param  int  $id
   */
  private function validate($id) {
    if(filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
      throw new InvalidArgumentException("Expected a valid ID, but got [$id] instead");
    }
  }

  /**
   * @return  string
   */
  public function __toString() {
    return (string)$this->id;
  }

  /**
   * @return  string
   */
  public function jsonSerialize() {
    return $this->id;
  }

  /**
   * @return  int
   */
  protected function getRaw() {
    return $this->id;
  }
}
