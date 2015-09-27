<?php

define('IN_FS', true);

require_once('../../header.php');

// Require inputs
if(!Post::has('detail') || !Post::has('summary'))
{
  return;
}

// Prepare SQL params
$params = array(
  'details' => "%" . Post::val('detail') . "%",
  'summary' => "%" . Post::val('summary') . "%"
);


$sql = $db->Query('SELECT count(*) 
		     FROM {tasks} t
		     WHERE t.item_summary = ? AND t.detailed_desc like ?',
		     $params);

$sametask = $db->fetchOne($sql);
echo $sametask;

?>
