SQL strings testing for PHPUnit
===============================

[![Build Status](https://travis-ci.org/czukowski/phpunit-sql.svg?branch=phpunit-7)](https://travis-ci.org/czukowski/phpunit-sql)

A constraint and assert method for testing SQL strings equality while ignoring whitespace.
Can be useful for testing results of query builders (especially those for long and complex
queries) against well-formatted 'expected' queries stored in files.

This does not replace the need to verify that the queries actually do the intended job.

Installation
------------

Pick your version! Version numbering follows major PHPUnit version numbers, so for a given
PHPUnit N.x, the installation command would look like this:

```sh
composer require czukowski/phpunit-sql "~N.0"
```

Usage
-----

Use `Cz\PHPUnit\SQL\AssertTrait` trait in a test case class, this will enable methods for
comparing SQL queries equality equal except for space and a terminal semicolon. An SQL query
may be denoted as strings or objects castable to strings. Arrays of SQL queries are also
acceptable and can be used to compare series of queries. For the purposes of the comparison,
array with a single SQL query element is equal to the SQL query element itself, so there's
no need to remember to eg. convert arguments to arrays all the time.

1. `assertEqualsSQLQueries` method will verify equality of two queries or series of queries.
   
   ```php
   $this->assertEqualsSQLQueries($expected, $actual);
   ```

2. `assertExecutedSQLQueries` method will verify that a query or a series of queries has been
   executed by a database abstraction layer. In order to be able to do it, a `getDatabaseDriver`
   method must be implemented by the test case class, that returns an object implementing the
   `Cz\PHPUnit\SQL\DatabaseDriverInterface` interface. That can be a database abstraction layer
   connection class with a fake database driver or something, which is injected into the tested
   application code.
   
   ```php
   $this->assertExecutedSQLQueries($expected);
   ```
   
   The interface implementation is available in `Cz\PHPUnit\SQL\DatabaseDriverTrait` for easy
   inclusion into custom implementations.

3. `loadSQLQueries` method will load SQL query or a series thereof from a file and return an
   array of queries. Splitting of queries by a delimiter `;` works only if the next query after
   a delimiter starts from the following line. Other than that, there may be newlines and blank
   lines inside the queries and in between of them, they do not get removed on load. By default,
   the method looks for the file in a subfolder named after the file name of the current class
   (presumably test case). That behavior can be changed by overriding `getLoadFilePath` method.
   
   ```php
   $this->loadSQLQueries($expected);
   ```
   
   The assertion methods will flatten arrays of queries, so multiple files may be loaded without
   a need to process them further.
   
   ```php
   $this->assertExecutedSQLQueries([
       $this->loadSQLQueries('SelectItems.sql'),
       $this->loadSQLQueries('InsertNewItems.sql'),
       $this->loadSQLQueries('DeleteOldItems.sql'),
   ]);
   ```

**Does not match your specific needs?** No problem, the `AssertTrait` is extremely simple, you can
clone and adjust it for your project or come up with a completely different implementation.

Known issues
------------

In order to compare SQL queries, a rather naive tokenizer function is used to convert query
strings to arrays. It may not cover some edge cases when uncommon operators or SQL syntax is
used in queries (specifically DDL was not tested), but it should be fairly easy to fix.

License
-------

This work is released under the MIT License. See LICENSE.md for details.
