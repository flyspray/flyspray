<?php

  /*********************************************************\
  | User Profile Edition                                    |
  | ~~~~~~~~~~~~~~~~~~~~                                    |
  \*********************************************************/

if (!defined('IN_FS')) {
  die('Do not access this file directly.');
}

if ($user->isAnon()) {
  Flyspray::show_error(13);
}

# maybe add some checks for output if a task or project or user changed permissions
# for example the user is moved from developer to basic
# or a task is changed to private modus
# or a task is closed now
# maybe add 'AND t.is_closed<>1' if we want only show votes of active tasks, that are taken for the votes limit.
# How can a user unvote such now unvisible tasks to get back under his voting limit for the project?
$votes = $db->query('
  SELECT v.*, t.project_id, t.item_summary, t.task_type, t.is_closed, p.project_title
  FROM {votes} v
  JOIN {tasks} t ON t.task_id=v.task_id
  LEFT JOIN {projects} p ON p.project_id=t.project_id
  WHERE user_id = ?
  ORDER BY t.project_id, t.task_id',
  $user->id
);
$votes = $db->fetchAllArray($votes);
$page->assign('votes', $votes);

// assuming this feature is used sparsely we do not set a limit yet
$myreminderq = $db->query('
  SELECT r.*, t.project_id, t.item_summary, t.task_type, t.is_closed, p.project_title
  FROM {reminders} r
  JOIN {tasks} t ON t.task_id=r.task_id
  LEFT JOIN {projects} p ON p.project_id=t.project_id
  WHERE r.to_user_id = ?
  ORDER BY t.project_id, t.task_id',
  $user->id
);
$myreminders = $db->fetchAllArray($myreminderq);
$page->assign('myreminders', $myreminders);

$page->assign('groups', Flyspray::listGroups());
$page->assign('project_groups', Flyspray::listGroups($proj->id));
$page->assign('theuser', $user);

$page->setTitle($fs->prefs['page_title'] . L('editmydetails'));
$page->pushTpl('myprofile.tpl');

?>
