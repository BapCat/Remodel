<<?= '?php' ?> namespace <?= $namespace ?>;

class <?= $name ?>Id extends \BapCat\Interfaces\Values\Value {
  private $id;
  
  public function __construct($id) {
    $this->validate($id);
    $this->id = (integer)$id;
  }
  
  private function validate($id) {
    if(filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
      throw new \InvalidArgumentException("Expected a valid ID, but got [$id] instead");
    }
  }
  
  public function __toString() {
    return (string)$this->id;
  }
  
  public function jsonSerialize() {
    return $this->id;
  }
  
  protected function getRaw() {
    return $this->id;
  }
}
