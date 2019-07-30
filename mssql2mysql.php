<?php
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

/*
 * SOME HELPER CONSTANT
 */
define('CHUNK_SIZE', 1000);

/*
 * STOP EDITING!
 */

set_time_limit(0);

function addQuote($string)
{
	return "'".$string."'";
}

function addTilde($string)
{
	return "`".$string."`";
}

// Connect MS SQL
$connectionInfo = array( "Database"=>MSSQL_DATABASE, "UID"=>MSSQL_USER, "PWD"=>MSSQL_PASSWORD);
$conn = sqlsrv_connect( MSSQL_HOST, $connectionInfo);

if( $conn ) {
     echo "Connection established.<br />";
}else{
     echo "Connection could not be established.<br />";
     die( print_r( sqlsrv_errors(), true));
}

// Connect to MySQL
$mysql_connect = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD) or die("Couldn't connect to MySQL on '".MYSQL_HOST."'' user '".MYSQL_USER."'\n");
echo "\n=> Connected to Source MySQL Server on ".MYSQL_HOST."\n";

// Select MySQL Database
$sqlsrv_db = mysql_select_db(MYSQL_DATABASE, $mysql_connect) or die("Couldn't open database '".MYSQL_DATABASE."'\n"); 
echo "=> Found database '".MYSQL_DATABASE."'\n";

$sqlsrv_tables = array();

// Get MS SQL tables
$sql = "SELECT * FROM sys.Tables;";
$res = sqlsrv_query($conn, $sql);
echo "\n=> Getting tables..\n";
while ($row = sqlsrv_fetch_array($res))
{
	array_push($sqlsrv_tables, $row['name']);
	echo ($row['name'])."\n";
}
echo "==> Found ". number_format(count($sqlsrv_tables),0,',','.') ." tables\n\n";

// Get Table Structures
if (!empty($sqlsrv_tables))
{
	$i = 1;
	foreach ($sqlsrv_tables as $table)
	{
		echo '====> '.$i.'. '.$table."\n";
		echo "=====> Getting info table ".$table." from SQL Server\n";

		$sql = "select * from information_schema.columns where table_name = '".$table."'";
		$res = sqlsrv_query($conn, $sql);

		if ($res) 
		{
			$sqlsrv_tables[$table] = array();

			$mysql = "DROP TABLE IF EXISTS `".$table."`";
			mysql_query($mysql);
			$mysql = "CREATE TABLE `".$table."`";
			$strctsql = $fields = array();

			while ($row = sqlsrv_fetch_array($res))
			{
				//print_r($row); echo "\n";
				array_push($sqlsrv_tables[$table], $row);

				switch ($row['DATA_TYPE']) {
					case 'bit':
					case 'tinyint':
					case 'smallint':
					case 'int':
					case 'bigint':
						$data_type = $row['DATA_TYPE'].(!empty($row['NUMERIC_PRECISION']) ? '('.$row['NUMERIC_PRECISION'].')' : '' );
						break;
					
					case 'money':
						$data_type = 'decimal(19,4)';
						break;
					case 'smallmoney':
						$data_type = 'decimal(10,4)';
						break;
					
					case 'real':
					case 'float':
					case 'decimal':
					case 'numeric':
						$data_type = $row['DATA_TYPE'].(!empty($row['NUMERIC_PRECISION']) ? '('.$row['NUMERIC_PRECISION'].(!empty($row['NUMERIC_SCALE']) ? ','.$row['NUMERIC_SCALE'] : '').')' : '' );
						break;

					case 'date':
					case 'datetime':
					case 'timestamp':
					case 'time':
						$data_type = $row['DATA_TYPE'];
					case 'datetime2':
					case 'datetimeoffset':
					case 'smalldatetime':
						$data_type = 'datetime';
						break;

					case 'nchar':
					case 'char':
						$data_type = 'char'.(!empty($row['CHARACTER_MAXIMUM_LENGTH']) && $row['CHARACTER_MAXIMUM_LENGTH'] > 0 ? '('.$row['CHARACTER_MAXIMUM_LENGTH'].')' : '(255)' );
						break;
					case 'nvarchar':
					case 'varchar':
						$data_type = 'varchar'.(!empty($row['CHARACTER_MAXIMUM_LENGTH']) && $row['CHARACTER_MAXIMUM_LENGTH'] > 0 ? '('.$row['CHARACTER_MAXIMUM_LENGTH'].')' : '(255)' );
						break;
					case 'ntext':
					case 'text':
						$data_type = 'text';
						break;

					case 'binary':
					case 'varbinary':
						$data_type = $data_type = $row['DATA_TYPE'];
					case 'image':
						$data_type = 'blob';
						break;

					case 'uniqueidentifier':
						$data_type = 'char(36)';//'CHAR(36) NOT NULL';
						break;

					case 'cursor':
					case 'hierarchyid':
					case 'sql_variant':
					case 'table':
					case 'xml':
					default:
						$data_type = false;
						break;
				}

				if (!empty($data_type))
				{
					$ssql = "`".$row['COLUMN_NAME']."` ".$data_type." ".($row['IS_NULLABLE'] == 'YES' ? 'NULL' : 'NOT NULL');
					array_push($strctsql, $ssql);
					array_push($fields, $row['COLUMN_NAME']);	
				}
				
			}

			$mysql .= "(".implode(',', $strctsql).");";
			echo "======> Creating table ".$table." on MySQL... ";
			$q = mysql_query($mysql);
			echo (($q) ? 'Success':'Failed!'."\n".$mysql."\n")."\n";
			
			echo "=====> Getting data from table ".$table." on SQL Server\n";
			$sql = "SELECT * FROM ".$table;
			$qres = sqlsrv_query($conn, $sql);
			$numrow = sqlsrv_num_rows($qres);
			echo "======> Found ".number_format($numrow,0,',','.')." rows\n";

			if ($qres)
			{
				echo "=====> Inserting to table ".$table." on MySQL\n";
				$numdata = 0;
				if (!empty($fields))
				{
					$sfield = array_map('addTilde', $fields);
					while ($qrow = sqlsrv_fetch_array($qres))
					{
						$datas = array();
						foreach ($fields as $field) 
						{
							$ddata = (!empty($qrow[$field])) ? $qrow[$field] : '';
							array_push($datas,"'".mysql_real_escape_string($ddata)."'");
						}

						if (!empty($datas))
						{
							//$datas = array_map('addQuote', $datas);
							//$fields = 
							$mysql = "INSERT INTO `".$table."` (".implode(',',$sfield).") VALUES (".implode(',',$datas).");";
							//$mysql = mysql_real_escape_string($mysql);
							//echo $mysql."\n";
							$q = mysql_query($mysql);
							$numdata += ($q ? 1 : 0 );
						}
						if ($numData % CHUNK_SIZE == 0) {
							echo "===> ".number_format($numdata,0,',','.')." data inserted so far\n";
						}
					}
				}
				echo "======> ".number_format($numdata,0,',','.')." data inserted total\n\n";
			}
		}
		$i++;
	}

}

echo "Done!\n";

sqlsrv_close($sqlsrv_connect);
mysql_close($mysql_connect);

?>
