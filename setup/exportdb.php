<?php
 
error_reporting(E_ALL);
 
 
require_once '../adodb/adodb.inc.php';
require_once '../adodb/adodb-xmlschema03.inc.php';
 
$conf    = @parse_ini_file('../flyspray.conf.php', true) or die('Cannot open config file.');
 
/* Start by creating a normal ADODB connection.
 */
 
$db = ADONewConnection($conf['database']['dbtype']);
$db->Connect( $conf['database']['dbhost'], $conf['database']['dbuser'],
              $conf['database']['dbpass'], $conf['database']['dbname']) or die('Cannot connect to DB.');
$db->debug= true;
 
/* Use the database connection to create a new adoSchema object.
 */
 
$schema = new adoSchema($db);
 
$data = $schema->ExtractSchema();
$data = str_replace('flyspray_', '', $data);
 
file_put_contents('flyspray-schema.xml', $data);
 
?>