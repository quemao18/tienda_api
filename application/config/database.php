<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'premium';
$active_record = TRUE;
/* OJO ya no estoy usando la base de datos en rodasalias.com sino en el servidor directo 
en rodasaliasservidor.changeip.net
*/
if($_SERVER['SERVER_NAME']=='rodasalias.com')
{
	$db['premium']['username'] = $db['web']['username'] = 'rodasali';
	$db['premium']['password'] = $db['web']['password'] = 'Ro@j317461070';	
	$db['premium']['database'] = 'rodasali_admin001000';
	$db['web']['database']     = 'rodasali_admin001000_web';
}else{	
	$db['premium']['username'] = $db['web']['username'] = 'root';
	$db['premium']['password'] = $db['web']['password'] = 'admin';	
	$db['premium']['database'] = 'admin001000';
	$db['web']['database']     = 'admin001000_web';
}

if($_SERVER['SERVER_NAME']=='rodasalias.com' || $_SERVER['SERVER_NAME']=='rodasalias.changeip.net')
$db['premium']['hostname'] = $db['web']['hostname'] = 'rodasalias.changeip.net';
if($_SERVER['SERVER_NAME']=='localhost' )
$db['premium']['hostname'] = $db['web']['hostname'] = 'localhost';
if($_SERVER['SERVER_NAME']=='servidor' )
$db['premium']['hostname'] = $db['web']['hostname'] = 'servidor';

/*
$db['premium']['username'] = $db['web']['username'] = 'root';
$db['premium']['password'] = $db['web']['password'] = 'admin';	
$db['premium']['database'] = 'admin001000';
$db['web']['database']     = 'admin001000_web';
$db['premium']['hostname'] = $db['web']['hostname'] = 'http://rodasaliasservidor.changeip.net'; //190.75.77.252
//$config['dsn'] = 'mysql:host=rodasaliasservidor.changeip.net;dbname=admin001000';
//$db['premium']['hostname'] = '190.75.77.252'; 
//$db['web']['hostname'] = '190.75.77.252'; 
*/


$db['web']['dbdriver'] = $db['premium']['dbdriver'] = 'mysqli';
$db['web']['dbprefix'] = $db['premium']['dbprefix'] = '';
$db['web']['pconnect'] = $db['premium']['pconnect'] = FALSE; 
//p connect true to false  por error MySQL server has gone away parece q es error CI 3.x
$db['web']['db_debug'] = $db['premium']['db_debug'] = TRUE;
$db['web']['cache_on'] = $db['premium']['cache_on'] = FALSE;
$db['web']['cachedir'] = $db['premium']['cachedir'] = '';
$db['web']['char_set'] = $db['premium']['char_set'] = 'utf8';
$db['web']['dbcollat'] = $db['premium']['dbcollat'] = 'utf8_general_ci';
$db['web']['swap_pre'] = $db['premium']['swap_pre'] = '';
$db['web']['autoinit'] = $db['premium']['autoinit'] = TRUE;
$db['web']['stricton'] = $db['premium']['stricton'] = FALSE;

//agregado por error MySQL server has gone away 
ini_set ('mysqli.reconnect', 1);

/* End of file database.php */
/* Location: ./application/config/database.php */