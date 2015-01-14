<?php
/*
    This script gets the history of a task and
    returns it for HTML display in a page.
*/

define('IN_FS', true);

header('Content-type: text/html; charset=utf-8');

require_once('../../header.php');
require_once('../../includes/events.inc.php');
$baseurl = dirname(dirname($baseurl)) .'/' ;

// Initialise user
if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')) {
    $user = new User(Cookie::val('flyspray_userid'));
    $user->check_account_ok();
} else {
    $user = new User(0, $proj);
}

// Check permissions
if (!$user->perms('view_history')) {
    die();
}

// Load translations
load_translations();

if ($details = Get::num('details')) {
    $details = " AND h.history_id = $details";
} else {
    $details = null;
}

$sql = get_events(Get::num('task_id'), $details);
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
