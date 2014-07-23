<?php

  /********************************************************\
   *   | Multiple Tasks Creation                                          |
   *     | ~~~~~~~~~~~~~                                          |
   *       \********************************************************/

if (!defined('IN_FS')) {
	    die('Do not access this file directly.');
}

if (!$user->can_open_task($proj)) {
	    Flyspray::show_error(15);
}

$page->setTitle($fs->prefs['page_title'] . $proj->prefs['project_title'] . ': ' . L('newtask'));

$page->assign('old_assigned', '');
$page->pushTpl('newmultitasks.tpl');

?>
