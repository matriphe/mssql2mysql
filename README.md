# mssql2mysql #

Simple MSSQL Server to MySQL table converter.

## Prerequisites ##

Install the [mssql extension](http://php.net/manual/en/book.mssql.php)

`sudo apt-get install php5-mssql`

## Usage ##

Edit the MSSQL and MySQL hostname, user, password, and database. Run the database from command line using PHP CGI.

#### Example:

1. Edit the file:

    `vim mssql2mysql.php`

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

## Limitations ##

* Just converts tables
* No indexes
* No store procedure
* No triggers
* No views
* No Advanced MSSQL features
