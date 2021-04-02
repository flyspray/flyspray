<?php
define('IN_FS', true);

header('Content-type: text/html; charset=utf-8');

require_once '../../header.php';
require_once '../../includes/events.inc.php';

$csp->emit();

if (!isset($_GET['type']) or !is_string($_GET['type'])) {
	die('bad type');
} else {
	$type=$_GET['type'];
}

if (!isset($_GET['id']) or !is_string($_GET['id'])) {
	die('bad id');
} else {
	$id = Get::num('id');
}

$property = array('tag', 'category');

if (!in_array($_GET['type'], $property, true)) {
	die('unknown list type');
}

# recalculate $proj for permission check
$ADODB_GETONE_EOF = "-1";
$result = $db->query('
	SELECT project_id
	FROM {list_' . $type . '}
	WHERE ' .$type. '_id = ?',
	array($id)
);

$project_id = $db->fetchOne($result);
if (!$project_id and $project_id !=='0') {
	die('unknown list item');
}

$proj = new Project($project_id);

// Initialise user
if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')) {
	$user = new User(Cookie::val('flyspray_userid'));
	$user->check_account_ok();
} else {
	$user = new User(0, $proj);
}

load_translations();

if (!$user->infos['is_admin'] or !$user->infos['manage_project']) {
	die('no permission');
}

if ($type==='tag') {
	$result=$db->query('
	SELECT t.task_id, t.item_summary FROM {tasks} t
	JOIN {task_tag} ttg ON ttg.task_id=t.task_id AND tag_id=?
	LIMIT 10', array($id));
} elseif ($type==='category') {
	$result=$db->query('
	SELECT task_id, item_summary FROM {tasks}
	WHERE product_category = ?
	LIMIT 10', array($id));
} else{
	$result=$db->query('
	SELECT task_id, item_summary FROM {tasks}
	WHERE ' .$type. '_id = ?
	LIMIT 10', array($id));
}
$tasks=$db->fetchAllArray($result);

$page = new FSTpl;
$page->setTheme($proj->prefs['theme_style']);
$page->assign('tasks', $tasks);
$page->display('getusage.callback.tpl');
