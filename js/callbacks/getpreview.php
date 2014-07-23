<?php
/*
    This script is the AJAX callback that performs a search
    for users, and returns them in an ordered list.
*/

define('IN_FS', true);

header('Content-type: text/html; charset=utf-8');

$webdir = dirname(dirname(dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'utf-8'))));
require_once('../../header.php');

if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')) {
    $user = new User(Cookie::val('flyspray_userid'));
    $user->check_account_ok();
    $user->save_search();
} else {
    $user = new User(0, $proj);
}

echo TextFormatter::render(Post::val('text'));

?>
