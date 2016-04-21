<?php
/***********************************\
| Top level project overview        |
\***********************************/

if (!defined('IN_FS')) {
  die('Do not access this file directly.');
}

if ($proj->id && $user->can_view_project($proj->prefs)) {
  $projects = array(
    0 => array(
      'project_id' => $proj->id,
      'project_title' => $proj->prefs['project_title'],
      'project_is_active' => $proj->prefs['project_is_active']
    )
  );
} else {
  $projects = $fs->projects;
  # anon users should not see details of a restricted project but anon tasks creation allowed
  # but in /index.php we filter now by 'can_select_project', not 'can_view_project' anymore.
  $projects= array_filter($projects, array($user, 'can_view_project'));
}

if(count($projects)>0){

$most_wanted = array();
$stats = array();
$assigned_to_myself = array();
$projprefs = array();

# Most wanted tasks for each project
foreach ($projects as $project) {
  $sql = $db->Query('SELECT v.task_id, count(*) AS num_votes
    FROM {votes} v
    LEFT JOIN {tasks} t ON v.task_id = t.task_id AND t.project_id = ?
    WHERE t.is_closed = 0
    GROUP BY v.task_id
    ORDER BY num_votes DESC',
    array($project['project_id']), 5
  );

  if ($db->CountRows($sql)) {
    $most_wanted[$project['project_id']] = $db->FetchAllArray($sql);
  }
}

# Project stats
foreach ($projects as $project) {
  $sql = $db->Query('SELECT count(*) FROM {tasks} WHERE project_id = ?', array($project['project_id']));
  $stats[$project['project_id']]['all'] = $db->fetchOne($sql);

  $sql = $db->Query('SELECT count(*) FROM {tasks} WHERE project_id = ? AND is_closed = 0', array($project['project_id']));
  $stats[$project['project_id']]['open'] = $db->fetchOne($sql);

  $sql = $db->Query('SELECT avg(percent_complete) FROM {tasks} WHERE project_id = ? AND is_closed = 0', array($project['project_id']));
  $stats[$project['project_id']]['average_done'] = round($db->fetchOne($sql), 0);

  if ($proj->id) {
    $prefs = $proj->prefs;
  } else {
    $currentproj = new Project($project['project_id']);
    $prefs = $currentproj->prefs;
  }

  $projprefs[$project['project_id']] = $prefs;
    
  if ($prefs['use_effort_tracking']) {
    $sql = $db->Query('
      SELECT t.task_id, t.estimated_effort
      FROM {tasks} t
      WHERE project_id = ? AND is_closed = 0',
      array($project['project_id'])
    );
    $stats[$project['project_id']]['tasks'] = $db->FetchAllArray($sql);
  }
}

# Assigned to myself
foreach ($projects as $project) {
  $sql = $db->Query('
    SELECT a.task_id
    FROM {assigned} a
    LEFT JOIN {tasks} t ON a.task_id = t.task_id AND t.project_id = ?
    WHERE t.is_closed = 0 and a.user_id = ?',
    array($project['project_id'], $user->id), 5
  );
  if ($db->CountRows($sql)) {
    $assigned_to_myself[$project['project_id']] = $db->FetchAllArray($sql);
  }
}
$page->uses('most_wanted', 'stats', 'projects', 'assigned_to_myself', 'projprefs');
$page->setTitle($fs->prefs['page_title'] . $proj->prefs['project_title'] . ': ' . L('toplevel'));
$page->pushTpl('toplevel.tpl');
} else{
  # mmh what we want to show anon users with only the 'create anon task' permission enabled?...
}
?>
