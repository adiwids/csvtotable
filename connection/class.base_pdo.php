<?php
namespace CsvToTable\Connection;

use \PDO;

class BasePdo extends PDO {
  private $uri;

  public function __construct($connection_string) {
    $this->uri = parse_url($connection_string);
    $dns = sprintf("%s:host=%s;dbname=%s", $this->uri['scheme'], $this->uri['host'], $this->getDBnameFromURI());
    parent::__construct($dns, $this->uri['user'], array_key_exists('pass', $this->uri) ? $this->uri['pass'] : null);
  }

  private function getDBnameFromURI() {
    $first_char = substr($this->uri['path'], 0, 1);
    return $first_char == "/" ? substr($this->uri['path'], 1) : $this->uri['path'];
  }

  public function insert($table_name, $columns) {
    $cols = [];
    $values = [];
    foreach($columns as $column_name => $value) {
      if($this->isBlank($column_name) || $this->isBlank($value)) { continue; }
      array_push($cols, $column_name);
      array_push($values, $this->quote($value));
    }
    $statement = $this->prepare("INSERT INTO $table_name (".implode(',', $cols).") VALUES (".implode(',', $values).")");
    return $statement->execute();
  }

  public function cleanTable($table_name) {
    $statement = $this->prepare("TRUNCATE TABLE :table_name");
    return $statement->execute([':table_name' => $table_name]);
  }

  private function isBlank($string) {
    return empty($string) || is_null($string);
  }
}
?>
