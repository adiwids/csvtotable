[![CircleCI](https://circleci.com/gh/adiwids/csvtotable/tree/master.svg?style=svg)](https://circleci.com/gh/adiwids/csvtotable/tree/master)

## Overview

Library class to import CSV file into specific database table.

## Development and Testing

Requirements:

  - PHP 5.6.x with PECL + PDO

  - PHPUnit

  - MongoDB + extension (`mongodb-clients mongo-db-server libmongo-client0dev`) \*

  - MySQL

  - PostgreSQL

_\*_ not implemented yet


How to test:

  1. Create database and table by executing query from `test/csvtotable_test.my.sql` (for MySQL Server) and
     `test/csvtotable_test.pg.sql` (for PostgreSQL)

  2. Change URL on test to your database server credential, both for MySQL and PostgreSQL test case.

  3. Run `phpunit test/`


## License

This code is licensed by MIT License. See license detail [here](LICENSE)

## How to contribute?

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for detail!

