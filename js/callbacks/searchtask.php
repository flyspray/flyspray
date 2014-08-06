<?php

define('IN_FS', true);

require_once('../../header.php');

$_POST['detail'] = "%" . trim($_POST['detail']) . "%";

$sql = $db->Query('SELECT count(*) 
		     FROM {tasks} t
		     WHERE t.item_summary = ? AND t.detailed_desc like ?',
		     $_POST);

$sametask = $db->fetchOne($sql);
echo $sametask;

?>
