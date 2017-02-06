<?php
namespace CsvToTable\Connection;

class MongoConn {
  private $connection;

  public function __construct($uri) {
    $this->setConnection($uri);
    return $this->getConnection();
  }

  public function setConnection($uri) {
    $this->connection = new MongoClient($uri);
  }

  public function getConnection() {
    return $this->connection;
  }

  public function insert($collection, $field) {
    return true;
  }
}
?>
