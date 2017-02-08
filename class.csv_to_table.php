<?php
  function requireFileInDirectory($dir_path) {
    $reader = dir($dir_path);
    while(false !== ($entry = $reader->read())) {
      $path = implode(DIRECTORY_SEPARATOR,[$dir_path, $entry]);
      if( file_exists($path) && is_file($path) ) {
        require_once($path);
      }
    }
    $reader->close();
  }

  requireFileInDirectory('./connection');

  class CsvToTable {
    const SUPPORTED_DRIVERS = ['mysql' => 'CsvToTable\Connection\Mysql'];
    const DELIMITER = ['col' => ',', 'row' => ';'];

    private $source;
    private $source_path;

    private $connection;
    private $driver;
    private $connection_string;

    private $table_name;
    private $columns = [];

    private $mapping = [];
    private $content = [];

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
            $this->setTableColumn($this->getTableName(), $column_name);
          }
        }
      }
      if($this->isValidargument('mapping', $args)) {
        foreach($args['mapping'] as $header => $table_column) {
          $this->mapCsvHeaderToColumn($header, $table_column);
        }
      }
    }

    public function getSource() {
      return $this->source;
    }

    public function setSource($path) {
      $this->source_path = $path;
      $this->openFile();
      $this->csvToArray();
    }

    public function setConnectionDriver($driver) {
      $this->driver = $driver;
    }

    public function setConnectionString($uri) {
      if($this->isValidConnectionStringForDriver($uri)) {
        $this->connection_string = $uri;
      } else {
        throw new Exception(sprintf("Invalid connection URI for '%s'", $this->getConnectionDriver()));
      }
    }

    public function getConnectionDriver() {
      return $this->driver;
    }

    public function setTableName($table_name) {
      $this->table_name = $table_name;
    }

    public function getTableName() {
      return $this->table_name;
    }

    public function setTableColumn($table_name, $column_name) {
      array_push($this->columns, sprintf("%s.%s", $table_name, $column_name));
    }

    public function getTableColumns($only_name = true) {
      $column_names = [];
      foreach($this->columns as $col) {
        list($table_name, $column_name) = explode('.', $col, 2);
        array_push($column_names, $column_name);
      }
      return $only_name ? $column_names : $this->columns;
    }

    private function connect() {
      $klass = self::SUPPORTED_DRIVERS[$this->getConnectionDriver()];
      $this->connection = new $klass($this->getConnectionString());
    }

    public function import() {
      $this->connect();
      $imported = 0;
      foreach($this->getContent() as $r) {
        $attributes = [];
        foreach($r as $head => $value) {
          $attributes[$this->mapping[$head]] = $value;
        }
        if($this->connection->insert($this->getTableName(), $attributes)) {
          $imported++;
        }
      }
      return $imported;
    }

    public function mapCsvHeaderToColumn($header, $column_name) {
      if(!in_array($column_name, $this->getTableColumns())) {
        throw new Exception(sprintf("%s does not exist", $column_name));
      }
      $this->mapping[$header] = $column_name;
    }

    protected function openFile() {
      $this->source = fopen($this->source_path, 'r');
    }

    private function isValidArgument($arg, $args) {
      if(!array_key_exists($arg, $args) || is_null($args[$arg])) {
        throw new Exception(sprintf("%s argument should be defined", $arg));
        return false;
      };
      return true;
    }

    private function isDriverSupported($driver) {
      if(!in_array($driver, array_keys(self::SUPPORTED_DRIVERS))) {
        throw new Exception(sprintf("%s is not supported", $driver));
        return false;
      }
      return true;
    }

    private function isValidConnectionStringForDriver($uri) {
      if($this->isBlank($this->getConnectionDriver())) {
        throw new Exception("Connection driver is undefined");
      }
      return preg_match("/".$this->getConnectionDriver()."\:\/\//", $uri) > 0;
    }

    private function isBlank($string) {
      return $string === null || strlen($string) == 0;
    }

    public function getConnection() {
      return $this->connection;
    }

    public function getConnectionString() {
      return $this->connection_string;
    }

    private function csvToArray() {
      $header = [];
      $line = 0;
      $this->emptyContent();
      while(($data = fgetcsv($this->getSource(), 1000, self::DELIMITER['col'], self::DELIMITER['row'])) !== false) {
        if($line == 0) {
          $header = $data;
          $line++;
          continue;
        }
        $row = [];
        $i = 0;
        while($i < count($data)) {
          $row[$header[$i]] = $data[$i];
          $i++;
        }
        array_push($this->content, $row);
      }
    }

    public function getContent() {
      return $this->content;
    }

    public function emptyContent() {
      $this->content = [];
    }
  }
?>
