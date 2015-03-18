<?php
/*
    This script gets the history of a task and
    returns it for HTML display in a page.
*/

define('IN_FS', true);

header('Content-type: text/html; charset=utf-8');

require_once('../../header.php');
$baseurl = dirname(dirname($baseurl)) .'/' ;

// Initialise user
if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')) {
    $user = new User(Cookie::val('flyspray_userid'));
    $user->check_account_ok();
} else {
    $user = new User(0, $proj);
}

// don't allow anonymous users to access this page at all
if ($user->isAnon()) {
    die();
}

$user->save_search();
$page = new FSTpl;
$page->setTheme($proj->prefs['theme_style']);
$page->display('links.searches.tpl');
?>
