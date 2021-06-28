<?php
/*
 * @author Peter Liscovius (peterdd)
 */

if (!defined('IN_FS')) {
	die('Do not access this file directly.');
}

if (!$proj->prefs['use_kanban']) {
        Flyspray::show_error(28);
}

# experimental, maybe later add a user group permission for that
if (!$user->perms('is_admin')) {
	Flyspray::show_error(4);
}

if (!$user->perms('view_reports')) {
	Flyspray::show_error(28);
}

if (!$user->can_view_project($proj->id)) {
	$proj = new Project(0);
}

// Get the visibility state of all columns
$visible = explode(' ', trim($proj->id ? $proj->prefs['visible_columns'] : $fs->prefs['visible_columns']));
if (!is_array($visible) || !count($visible) || !$visible[0]) {
	$visible = array('id');
}

// Remove columns the user is not allowed to see
if (in_array('estimated_effort', $visible) && !$user->perms('view_estimated_effort')) {
	unset($visible[array_search('estimated_effort', $visible)]);
}

if (in_array('effort', $visible) && !$user->perms('view_current_effort_done')) {
	unset($visible[array_search('effort', $visible)]);
}

# task status as columns
$result=$db->query('
	SELECT * FROM {list_status}
	WHERE show_in_list=1
	AND (project_id=0 OR project_id=?)
	ORDER BY list_position ASC',
	array($proj->id)
);
$stati=$db->fetchAllArray($result);
$page->assign('stati', $stati);
$page->uses('tasks');

/**
 * bad loop, but as there are status count vary normally around 3-6 should be ok.
 * easy to change to single SQL request if needed
 */
$cols=array();
foreach($stati as $status) {
	$tasklist = Backend::get_task_list(
		array(
			'status'=>array($status['status_id'])
		),
		array('task_id', 'summary', 'tasktype', 'assignedto')
	);
	$cols[]=$tasklist;
}
$page->assign('cols', $cols);
$page->pushTpl('kanban.tpl');
