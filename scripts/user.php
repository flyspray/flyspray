<?php

  /*********************************************************\
  | View a user's profile                                   |
  | ~~~~~~~~~~~~~~~~~~~~                                    |
  \*********************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

$page->assign('groups', Flyspray::listGroups());

if ($proj->id) {
    $page->assign('project_groups', Flyspray::listGroups($proj->id));
}

$id = Flyspray::validUserId(Get::val('id', Get::val('uid')));
if (!$id) {
  $id = Flyspray::usernameToId(Get::val('user_name'));
}

$theuser = new User($id);
if ($theuser->isAnon()) {
    Flyspray::show_error(19);
}

// Some possibly interesting information about the user
$sql = $db->query('SELECT count(*) FROM {comments} WHERE user_id = ?', array($theuser->id));
$page->assign('comments', $db->fetchOne($sql));

$sql = $db->query('SELECT count(*) FROM {tasks} WHERE opened_by = ?', array($theuser->id));
$page->assign('tasks', $db->fetchOne($sql));

$sql = $db->query('SELECT count(*) FROM {assigned} WHERE user_id = ?', array($theuser->id));
$page->assign('assigned', $db->fetchOne($sql));

$page->assign('theuser', $theuser);

$page->setTitle($fs->prefs['page_title'] . L('viewprofile'));
$page->pushTpl('profile.tpl');

?>
