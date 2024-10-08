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

$result = $db->query('
  SELECT u.user_id, u.user_name, u.real_name, g.group_id, g.group_name, g.project_id
  FROM {users} u
  JOIN {users_in_groups} uig ON u.user_id = uig.user_id
  JOIN {groups} g ON g.group_id = uig.group_id
  WHERE (g.show_as_assignees = 1 OR g.is_admin = 1)
  AND (g.project_id = 0 OR g.project_id = ?) AND u.account_enabled = 1
  ORDER BY g.project_id ASC, g.group_name ASC, u.user_name ASC', $proj->id);

$userlist = array();
$userids=array();
while ($row = $db->fetchRow($result)) {
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
if (isset($_POST['rassigned_to']) && is_array($_POST['rassigned_to'])) {
	foreach	($_POST['rassigned_to'] as $ass) {
		if (is_numeric($ass)) {
			$assignees[] = $ass;
		}
	}
}
$page->assign('assignees', $assignees);

# tag choose helper
$taglist = array();
if ($proj->prefs['use_tags']) {
	$restaglist=$db->query('
		SELECT * FROM {list_tag}
		WHERE (project_id=0 OR project_id=?)
		AND show_in_list=1
		ORDER BY list_position ASC',
		array($proj->id)
	);
	$taglist=$db->fetchAllArray($restaglist);
}
$page->assign('taglist', $taglist);

$page->assign('userlist', $userlist);
$page->assign('old_assigned', '');

$addanothertask = 0;

if (!$user->isAnon() && array_key_exists('addanothertask', $_SESSION) && $_SESSION['addanothertask'] == 1) {
	$addanothertask = 1;
}

// Clear the "addanothertask" flag here so it can't linger in the session
if (array_key_exists('addanothertask', $_SESSION)) {
	unset($_SESSION['addanothertask']);
}

$page->assign('addanothertask', $addanothertask);
$page->pushTpl('newtask.tpl');

?>
