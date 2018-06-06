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

$active_group = 'us1';
$active_record = TRUE;

// US
$db['us1']['hostname'] = 'localhost';
$db['us1']['username'] = 'root';
$db['us1']['password'] = 'root';
$db['us1']['database'] = 'db_test_synca1';
$db['us1']['dbdriver'] = 'mysqli';
$db['us1']['dbprefix'] = '';
$db['us1']['ismaster'] = TRUE;
$db['us1']['tbl_name'] = 'synca_db_1';
$db['us1']['dbactive'] = TRUE;

// US BACKUP
$db['us2']['hostname'] = 'localhost';
$db['us2']['username'] = 'root';
$db['us2']['password'] = 'root';
$db['us2']['database'] = 'db_test_synca1';
$db['us2']['dbdriver'] = 'mysqli';
$db['us2']['dbprefix'] = '';
$db['us2']['ismaster'] = FALSE;
$db['us2']['tbl_name'] = 'synca_db_2';
$db['us2']['dbactive'] = TRUE;

// RU
$db['ru1']['hostname'] = "localhost";
$db['ru1']['username'] = "root";
$db['ru1']['password'] = "root";
$db['ru1']['database'] = "db_test_synca2";
$db['ru1']['dbdriver'] = "mysqli";
$db['ru1']['dbprefix'] = "";
$db['ru1']['ismaster'] = FALSE;
$db['ru1']['tbl_name'] = 'synca_db_1';
$db['ru1']['dbactive'] = TRUE;

// UK
$db['uk1']['hostname'] = "localhost";
$db['uk1']['username'] = "root";
$db['uk1']['password'] = "root";
$db['uk1']['database'] = "db_test_synca3";
$db['uk1']['dbdriver'] = "mysqli";
$db['uk1']['dbprefix'] = "";
$db['uk1']['ismaster'] = FALSE;
$db['uk1']['tbl_name'] = TRUE;
$db['uk1']['dbactive'] = FALSE;

// CONSTANT PARAMS
$db['us1']['pconnect'] = TRUE;
$db['us1']['db_debug'] = TRUE;
$db['us1']['cache_on'] = FALSE;
$db['us1']['cachedir'] = '';
$db['us1']['char_set'] = 'utf8';
$db['us1']['dbcollat'] = 'utf8_general_ci';
$db['us1']['swap_pre'] = '';
$db['us1']['autoinit'] = TRUE;
$db['us1']['stricton'] = FALSE;


// LIST
// Search thru DBs to find active ones and make a list of their group names

// While at it find master and put it into a separate array
// Automatically find the master
foreach ($db as $group_name => $db_array)
{
  // If it is a master db, create a separate array element
  ($db_array['dbactive'] === true && $db_array['ismaster'] === true) ? $db[$active_group]['dbmaster'] = $group_name : false;
  
  ($db_array['dbactive'] === true) ? $db[$active_group]['db_array'][$group_name] = $group_name : false;
}

// =======================
// DEBUG
// =======================
//echo '<pre>v1: '; print_r($db_master); exit;
//echo '<pre>v1: '; print_r($db['default']['master']); exit;
//echo '<pre>'; print_r($this->db->db['master']->get('synca_db_1')); exit;
//echo '<pre>'; print_r($this->db->db['rus']->get('synca_db_1')); exit;
//$db_master = array_search(true, array_column($tables_to_sync, 'master'));
//echo '<pre>v3: '; print_r($db[$active_group]);
//exit;
//$db_master = array_search(true, array_column($db_array, 'dbmaster'));


/* End of file database.php */
/* Location: ./application/config/database.php */