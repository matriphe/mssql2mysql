# mssql2mysql #

Simple MSSQL Server to MySQL table converter using PHP CLI.

## Prerequisites ##

Make sure you have [PHP mssql extension](http://php.net/manual/en/book.mssql.php) and [PHP mysql extension](http://php.net/manual/en/book.mysql.php) extensions installed.

#### On Debian/Ubuntu Based

`sudo apt-get install php5-mssql php5-mysql`

#### On Centos/Redhat Based

`sudo yum install php5-mssql php5-mysql`

#### On MacOSX using Brew

`brew install php5-mssql php5-mysql`

## Usage ##

Edit the MSSQL and MySQL *hostname*, *user*, *password*, and *database* section. Run the database from command line using PHP CLI.

#### Example:

1. Edit the file `mssql2mysql.php` using your favourite editor.

2. Change `MSSQL` and `MYSQL` variables:

    ```
    /*
     * SOURCE: MS SQL
     */
    define('MSSQL_HOST','mssql_host');
    define('MSSQL_USER','mssql_user');
    define('MSSQL_PASSWORD','mssql_password');
    define('MSSQL_DATABASE','mssql_database');

    /*
     * DESTINATION: MySQL
     */
    define('MYSQL_HOST', 'mysql_host');
    define('MYSQL_USER', 'mysql_user');
    define('MYSQL_PASSWORD','mysql_password');
    define('MYSQL_DATABASE','mysql_database');
    ```

3. Run the php script (make sure php is accessible in the path or environment variables)

  `php mssql2mysql.php`

## Common errors and fixes

Sometimes you will get an error like:

```bash
PHP Warning:  mssql_query(): message:
Unicode data in a Unicode-only collation or ntext data cannot be sent to clients using DB-Library
(such as ISQL) or ODBC version 3.7 or earlier. (severity 16) in ... on line 181
```

The easiest fix for users on unix systems would be to configure `freetds`. Make sure the version is `7.0` not `4.2`:

```
sudo vim /etc/freetds/freetds.conf

[global]
# TDS protocol version
tds version = 7.0
```

Extra Info for non-unix OS: [PHP Docs](http://php.net/manual/en/function.mssql-query.php) and [StackOverflow](http://stackoverflow.com/questions/5414890/mssql-query-issue-in-php-and-querying-text-data)

## Limitations ##

* Just converts tables
* No indexes
* No store procedure
* No triggers
* No views
* No Advanced MSSQL features
