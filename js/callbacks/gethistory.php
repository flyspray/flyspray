<?php
/*
    This script gets the history of a task and
    returns it for HTML display in a page.
*/

define('IN_FS', true);

header('Content-type: text/html; charset=utf-8');

require_once '../../header.php';
require_once '../../includes/events.inc.php';

$csp->emit();

if( !isset($_GET['task_id']) or !is_numeric($_GET['task_id'])){
	die();
} else {
	$task_id = Get::num('task_id');
}

# recalculate $proj for permission check
$result = $db->query('SELECT project_id FROM {tasks} WHERE task_id = ?', array($task_id));
$project_id = $db->fetchOne($result);
if (!$project_id) {
	die();
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

# set project of task asked for and then check permissions based on that
if ( !($task = Flyspray::getTaskDetails($task_id)) ) {
	die();
}

# also check the calculated view task permission in addition to view_history permission
if (!$user->can_view_task($task) or !$user->perms('view_history')) {
	die();
}

if ($details = Get::num('details')) {
    $details = " AND h.history_id = $details";
} else {
    $details = null;
}

$sql = get_events($task_id, $details);
$histories = $db->fetchAllArray($sql);

$page = new FSTpl;
$page->setTheme($proj->prefs['theme_style']);
$page->uses('histories', 'details');
if ($details) {
    event_description($histories[0]); // modifies global variables
    $page->assign('details_previous', $GLOBALS['details_previous']);
    $page->assign('details_new', $GLOBALS['details_new']);
}
$page->display('details.tabs.history.callback.tpl');

?>
