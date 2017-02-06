<?php
  require_once('./class.csv_to_table.php');

  $args = [
    'source' => './file.csv',
    'connection' => [
      'mongodb' => 'mongodb://localhost:27017'
    ],
    'table' => [
      'table_name' => ['column_name1']
    ]
  ];

  $csv = new CsvToTable($args);
  $csv->import();
?>
