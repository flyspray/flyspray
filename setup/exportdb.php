<?php
 
error_reporting(E_ALL);

die('Enable me by commenting this out by editing '.basename(__FILE__).' at line '.__LINE__);

require_once '../vendor/adodb/adodb-php/adodb.inc.php';
require_once '../vendor/adodb/adodb-php/adodb-xmlschema03.inc.php';

$conf = @parse_ini_file('../flyspray.conf.php', true) or die('Cannot open config file.');

/* Start by creating a normal ADODB connection. */
$db = ADONewConnection($conf['database']['dbtype']);
$db->Connect( $conf['database']['dbhost'], $conf['database']['dbuser'],
              $conf['database']['dbpass'], $conf['database']['dbname']) or die('Cannot connect to DB.');
$db->debug= true;

/* Use the database connection to create a new adoSchema object. */
$schema = new adoSchema($db);

$withdata=false;
$stripprefix=true;
$data = $schema->ExtractSchema( $withdata, '  ', $conf['database']['dbprefix'], $stripprefix);

file_put_contents('flyspray-schema.xml', $data);

?>
