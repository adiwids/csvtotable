<?php
use PHPUnit\Framework\TestCase;

$root = realpath(dirname(__FILE__)."/..");
require_once($root."/class.csv_to_table.php");

class CsvToTableTest extends TestCase {
  private $root;

  private $options;

  private $csv_sample_path;

  protected function setUp() {
    parent::setUp();

    $this->root = realpath(dirname(__FILE__)."/..");
    $this->csv_sample_path = implode(DIRECTORY_SEPARATOR, [$this->root, "test", "file.csv"]);
    $this->options = [
      'source' => $this->csv_sample_path,
      'connection' => [
        'mysql' => 'mysql://root:12345678@127.0.0.1:3306/csvtotable_test'
      ],
      'table' => [
        'books' => ['number', 'book_title', 'isbn', 'quantity']
      ],
      'mapping' => [
        'NO' => 'number',
        'TITLE' => 'book_title',
        'ISBN' => 'isbn',
        'QUANTITY' => 'quantity'
      ]
    ];
  }

  protected function tearDown() {
    parent::tearDown();
    $mysql = new CsvToTable\Connection\Mysql($this->options['connection']['mysql']);
    $table_name = array_keys($this->options['table'])[0];
    $mysql->cleanTable($table_name);
  }

  public function testInitializeWithInvalidArgument() {
    $_options = $this->options;
    unset($_options['table']);
    $this->expectException(Exception::class);
    $csvtotable = new CsvToTable($_options);
  }

  public function testUnsupportedConnectionDriver() {
    $_options = $this->options;
    $_options['connection'] = ['mongodb' => 'mongodb://localhost:12345'];
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("mongodb is not supported");
    $csvtotable = new CsvToTable($_options);
  }

  public function testInvalidConnectionString() {
    $_options = $this->options;
    $_options['connection'] = ['mysql' => 'mongodb://localhost:12345'];
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Invalid connection URI for 'mysql'");
    $csvtotable = new CsvToTable($_options);
  }

  public function testInvalidMappingToNonExistingTableColumn() {
    $_options = $this->options;
    $_options['mapping']['NO'] = 'book_number';
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("book_number does not exist");
    $csvtotable = new CsvToTable($_options);
  }

  public function testConnectToMySQL() {
    $csvtotable = new CsvToTable($this->options);
    $obj_reflector = new ReflectionObject($csvtotable);
    $connect_method = $obj_reflector->getMethod('connect');
    $connect_method->setAccessible(true);
    $connect_method->invoke($csvtotable);
    $this->assertEquals(get_class($csvtotable->getConnection()), "CsvToTable\Connection\Mysql");
  }

  public function testImportDataToMySQLTestTable() {
    $csvtotable = new CsvToTable($this->options);
    $this->assertEquals(count($csvtotable->getContent()), $csvtotable->import());
  }
}
?>
