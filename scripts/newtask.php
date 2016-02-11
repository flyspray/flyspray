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

$result = $db->Query('
  SELECT u.user_id, u.user_name, u.real_name, g.group_id, g.group_name, g.project_id
  FROM {users} u
  JOIN {users_in_groups} uig ON u.user_id = uig.user_id
  JOIN {groups} g ON g.group_id = uig.group_id
  WHERE (g.show_as_assignees = 1 OR g.is_admin = 1)
  AND (g.project_id = 0 OR g.project_id = ?) AND u.account_enabled = 1
  ORDER BY g.project_id ASC, g.group_name ASC, u.user_name ASC', $proj->id);

$userlist = array();
$userids=array();
while ($row = $db->FetchRow($result)) {
  if (!in_array($row['user_id'], $userids)){
    $userlist[$row['group_id']][] = array(
      0 => $row['user_id'],
      1 => sprintf('%s (%s)', $row['user_name'], $row['real_name']),
      2 => $row['project_id'],
      3 => $row['group_name']
    );
  $userids[]=$row['user_id'];
  } else{
    # user is probably in a global group with assignee permission listed, so no need to show second time in a project group.
  }
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
