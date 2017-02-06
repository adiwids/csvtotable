<?php
  function requireFileInDirectory($dir_path) {
    $reader = dir($dir_path);
    while(false !== ($entry = $reader->read())) {
      if( file_exists($entry) ) {
        require_once($entry);
      }
    }
    $reader->close();
  }

  requireFileInDirectory('./connection');

  class CsvToTable {
    const SUPPORTED_DRIVERS = ['mongodb' => 'CsvToTable\Connection\MongoConn'];
    const DELIMITER = ['col' => ',', 'row' => ';'];

    private $source;
    private $source_path;

    private $connection;
    private $driver;
    private $connection_string;

    private $table_name;
    private $columns = [];

    private $csv;

    private $mapping = [];

    public function __construct(array $args = []) {
      if($this->isValidArgument('source', $args)) {
        $this->setSource($args['source']);
      }
      if($this->isValidArgument('connection', $args)) {
        foreach($args['connection'] as $driver => $uri) {
          if($this->isDriverSupported($driver)) {
            $this->setConnectionDriver($driver);
            $this->setConnectionString($uri);
          }
        }
      }
      if($this->isValidArgument('table', $args)) {
        foreach($args['table'] as $table_name => $columns) {
          $this->setTableName($table_name);
          foreach($columns as $column_name) {
            $this->setColumn($this->getTableName(), $column_name);
          }
        }
      }
      if($this->isValidargument('mapping'$args)) {
        foreach($args['mapping'] as $header => $table_column) {
          $this->mapCsvHeaderToColumn($header, $table_column);
        }
      }
    };

    public function getSource() {
      return $this->source;
    };

    public function setSource($path) {
      $this->source_path = $path;
      $this->openFile();
      $this->csv = fgetcsv($this->getSource(), 1000, DELIMITER['col'], DELIMITER['row']);
    };

    public function setConnectionDriver($driver) {
      $this->driver = $driver;
    };

    public function setConnectionString($uri) {
      if($this->isValidConnectionStringForDriver($uri)) {
        $this->connection_string = $uri;
      };
    };

    public function getConnectionDriver() {
      return $this->driver;
    };

    public function setTableName($table_name) {
      $this->table_name = $table_name;
    };

    public function getTableName() {
      return $this->table_name;
    };

    public function setTableColumn($table_name, $column_name) {
      array_push($this->columns, sprintf("%s.%s", $table_name, $column_name));
    };

    public function getTableColumns($only_name = true) {
      $column_names = [];
      foreach($this->columns as $col) {
        list($table_name, $column_name) = explode('.', $col, 2);
      };
      return $only_name ? $column_names : $this->columns;
    };

    private function connect() {
      $klass = SUPPORTED_DRIVERS[$this->getConnectionDriver()];
      $this->connection = new $klass($this->getConnectionString());
    };

    public function import() {
      $this->connect();
      // read CSV and get value by it header index
      // then write to appropriate table column specified by mapping array
      // use $this->connection->insert() method to save each line data on CSV to table
    };

    public function mapCsvHeaderToColumn($header, $column_name) {
      if(!in_array($this->getTableColumns(), $column_name)) {
        throw new Exception(sprintf("%s does not exist", $column_name));
      };
    };

    protected function openFile() {
      $this->source = fopen($this->source_path, 'r');
    };

    private function isValidArgument($arg, $args) {
      if(!array_key_exists($arg, $args) || is_null($args[$arg])) {
        throw new Exception(sprintf("%s argument should be defined", $arg));
        return false;
      };
      return true;
    };

    private function isDriverSupported($driver) {
      if(!in_array($driver, SUPPORTED_DRIVERS)) {
        throw new Exception(sprintf("%s is not supported", $driver));
        return false;
      }
      return true;
    };

    private function isValidConnectionStringForDriver($uri) {
      if($this->isBlank($this->getConnectionDriver)) {
        throw new Exception("Connection driver is undefined");
      }
      return preg_match($this->getConnectionDriver()."\:\/\/", $uri) > 0;
    };

    private function isBlank($string) {
      return $string === null || strlen($string) == 0;
    };
  }
?>
