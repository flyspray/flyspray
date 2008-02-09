<?php

  /********************************************************\
  | Task Creation                                          |
  | ~~~~~~~~~~~~~                                          |
  \********************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

if (!$user->can_open_task($proj)) {
    Flyspray::show_error(15);
}

$page->setTitle($fs->prefs['page_title'] . $proj->prefs['project_title'] . ': ' . L('newtask'));

$result = $db->Query('SELECT DISTINCT u.user_id, u.user_name, u.real_name, g.group_name, g.project_id
                            FROM {users} u
                       LEFT JOIN {users_in_groups} uig ON u.user_id = uig.user_id
                       LEFT JOIN {groups} g ON g.group_id = uig.group_id
                           WHERE (g.show_as_assignees = 1 OR g.is_admin = 1)
                                 AND (g.project_id = 0 OR g.project_id = ?) AND u.account_enabled = 1
                        ORDER BY g.project_id ASC, g.group_name ASC, u.user_name ASC', $proj->id);
$userlist = array();
while ($row = $db->FetchRow($result)) {
    $userlist[$row['group_name']][] = array(0 => $row['user_id'], 
                        1 => sprintf('%s (%s)', $row['user_name'], $row['real_name']));
}
$assignees = array();
if (is_array(Post::val('rassigned_to'))) {
    $assignees = Post::val('rassigned_to');
}

$page->assign('assignees', $assignees);
$page->assign('userlist', $userlist);
$page->assign('old_assigned', '');
$page->pushTpl('newtask.tpl');

?>
