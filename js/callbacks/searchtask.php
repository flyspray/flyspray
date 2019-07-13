<?php
define('IN_FS', true);
require_once '../../header.php';


// Require inputs
if(!Post::has('detail') || !Post::has('summary') || !Post::has('project_id'))
{
  return;
}


// Load user profile
if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')){
  $user = new User(Cookie::val('flyspray_userid'));
  $user->check_account_ok();
} else {
  $user = new User(0, $proj);
}

// Require right to open a task on current project
if(!$user->can_open_task($proj)){
  return;
}


// Prepare SQL params
$params = array(
  'project_id' => Post::num('project_id'),
  'summary' => "%" . trim(Post::val('summary')) . "%",
  'details' => "%" . trim(Post::val('detail')) . "%"
);

$sql = $db->query('SELECT count(*)
		   FROM {tasks} t
		   WHERE t.project_id = ?
		   	AND t.item_summary like ?
		   	AND t.detailed_desc like ?',
		   $params);
$sametask = $db->fetchOne($sql);
echo $sametask;

?>
