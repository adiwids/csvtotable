<?php
use PHPUnit\Framework\TestCase;

$root = realpath(dirname(__FILE__)."/..");
echo $root;
require_once($root."/class.csv_to_table.php");

class CsvToTableTest extends TestCase {
  public function testInitializeWithInvalidArgument() {
    $options = [
      'source' => '../tmp/file.csv',
      'connection' => [
        'mongodb' => 'mongodb://localhost:12345'
      ]
    ];

    $csvtotable = new CsvToTable($options);
    $this->expectExceptionMessage("table argument should be defined");
  }
}
?>
