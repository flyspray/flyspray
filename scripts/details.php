<?php

  /*************************************************************\
  | Details a task (and edit it)                                |
  | ~~~~~~~~~~~~~~~~~~~~~~~~~~~~                                |
  | This script displays task details when in view mode,        |
  | and allows the user to edit task details when in edit mode. |
  | It also shows comments, attachments, notifications etc.     |
  \*************************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

$task_id = Req::num('task_id');

if ( !($task_details = Flyspray::GetTaskDetails($task_id)) ) {
    Flyspray::show_error(10);
}
if (!$user->can_view_task($task_details)) {
    Flyspray::show_error( $user->isAnon() ? 102 : 101);
}

require_once(BASEDIR . '/includes/events.inc.php');

if($proj->prefs['use_effort_tracking'])
{
    require_once(BASEDIR . '/includes/class.effort.php');
    $effort = new effort($task_id,$user->id);
    $effort->populateDetails();
    $page->assign('effort',$effort);
}

$page->uses('task_details');

// Send user variables to the template
$page->assign('assigned_users', $task_details['assigned_to']);
$page->assign('old_assigned', implode(' ', $task_details['assigned_to']));

$page->setTitle(sprintf('FS#%d : %s', $task_details['task_id'], $task_details['item_summary']));

if ((Get::val('edit') || (Post::has('item_summary') && !isset($_SESSION['SUCCESS']))) && $user->can_edit_task($task_details)) {
    $result = $db->Query('SELECT DISTINCT u.user_id, u.user_name, u.real_name, g.group_name, g.project_id
                            FROM {users} u
                       LEFT JOIN {users_in_groups} uig ON u.user_id = uig.user_id
                       LEFT JOIN {groups} g ON g.group_id = uig.group_id
                           WHERE (g.show_as_assignees = 1 OR g.is_admin = 1)
                                 AND (g.project_id = 0 OR g.project_id = ?) AND u.account_enabled = 1
                        ORDER BY g.project_id ASC, g.group_name ASC, u.user_name ASC', ($proj->id ? $proj->id : -1)); // FIXME: -1 is a hack. when $proj->id is 0 the query fails
    $userlist = array();
    while ($row = $db->FetchRow($result)) {
        $userlist[$row['group_name']][] = array(0 => $row['user_id'], 
                            1 => sprintf('%s (%s)', $row['user_name'], $row['real_name']));
    }
    if (is_array(Post::val('rassigned_to'))) {
        $page->assign('assignees', Post::val('rassigned_to'));
    } else {
        $assignees = $db->Query('SELECT user_id FROM {assigned} WHERE task_id = ?', $task_details['task_id']);
        $page->assign('assignees', $db->FetchCol($assignees));
    }
    $page->assign('userlist', $userlist);
    $page->pushTpl('details.edit.tpl');
}
else {
    $prev_id = $next_id = 0;

    if (isset($_SESSION['tasklist']) && ($id_list = $_SESSION['tasklist'])
            && ($i = array_search($task_id, $id_list)) !== false)
    {
        $prev_id = isset($id_list[$i - 1]) ? $id_list[$i - 1] : '';
        $next_id = isset($id_list[$i + 1]) ? $id_list[$i + 1] : '';
    }

    // Sub-Tasks
    $subtasks = $db->Query('SELECT  t.task_id, p.project_title 
                                 FROM  {tasks} t
			    LEFT JOIN  {projects} p ON t.project_id = p.project_id
                                WHERE  t.supertask_id = ?
                                ORDER BY t.list_order', 
                                array($task_id));
    
    // Parent categories
    $parent = $db->Query('SELECT  *
                            FROM  {list_category}
                           WHERE  lft < ? AND rgt > ? AND project_id  = ? AND lft != 1
                        ORDER BY  lft ASC',
                        array($task_details['lft'], $task_details['rgt'], $task_details['cproj']));

    // Check for task dependencies that block closing this task
    $check_deps   = $db->Query('SELECT  t.*, s.status_name, r.resolution_name, d.depend_id, p.project_title
                                  FROM  {dependencies} d
                             LEFT JOIN  {tasks} t on d.dep_task_id = t.task_id
                             LEFT JOIN  {list_status} s ON t.item_status = s.status_id
                             LEFT JOIN  {list_resolution} r ON t.resolution_reason = r.resolution_id
			     LEFT JOIN  {projects} p ON t.project_id = p.project_id
                                 WHERE  d.task_id = ?', array($task_id));

    // Check for tasks that this task blocks
    $check_blocks = $db->Query('SELECT  t.*, s.status_name, r.resolution_name
                                  FROM  {dependencies} d
                             LEFT JOIN  {tasks} t on d.task_id = t.task_id
                             LEFT JOIN  {list_status} s ON t.item_status = s.status_id
                             LEFT JOIN  {list_resolution} r ON t.resolution_reason = r.resolution_id
                                 WHERE  d.dep_task_id = ?', array($task_id));

    // Check for pending PM requests
    $get_pending  = $db->Query("SELECT  *
                                  FROM  {admin_requests}
                                 WHERE  task_id = ?  AND resolved_by = 0",
                                 array($task_id));

    // Get info on the dependencies again
    $open_deps    = $db->Query('SELECT  COUNT(*) - SUM(is_closed)
                                  FROM  {dependencies} d
                             LEFT JOIN  {tasks} t on d.dep_task_id = t.task_id
                                 WHERE  d.task_id = ?', array($task_id));

    $watching     =  $db->Query('SELECT  COUNT(*)
                                   FROM  {notifications}
                                  WHERE  task_id = ?  AND user_id = ?',
                                  array($task_id, $user->id));

    // Check if task has been reopened some time
    $reopened     =  $db->Query('SELECT  COUNT(*)
                                   FROM  {history}
                                  WHERE  task_id = ?  AND event_type = 13',
                                  array($task_id));

    // Check for cached version
    $cached = $db->Query("SELECT content, last_updated
                            FROM {cache}
                           WHERE topic = ? AND type = 'task'",
                           array($task_details['task_id']));
    $cached = $db->FetchRow($cached);

    // List of votes
    $get_votes = $db->Query('SELECT u.user_id, u.user_name, u.real_name, v.date_time
                               FROM {votes} v
                          LEFT JOIN {users} u ON v.user_id = u.user_id
                               WHERE v.task_id = ?
                            ORDER BY v.date_time DESC',
                            array($task_id));

    if ($task_details['last_edited_time'] > $cached['last_updated'] || !defined('FLYSPRAY_USE_CACHE')) {
        $task_text = TextFormatter::render($task_details['detailed_desc'], 'task', $task_details['task_id']);
    } else {
        $task_text = TextFormatter::render($task_details['detailed_desc'], 'task', $task_details['task_id'], $cached['content']);
    }

    ///////////////////////////////////get tags///////////////////////////////////
    $result2 = $db->Query('SELECT tag FROM {tags} WHERE task_id = ?',array($task_id));
    $tags = $db->fetchAllArray($result2);
    $tagList = '';
    foreach ($tags as $tag)
	    $tagList = $tagList.' '.$tag['tag'];

    $page->assign('tag_list', $tagList);
    /////////////////////////////////////////////////////////////////////////////
    $page->assign('prev_id',   $prev_id);
    $page->assign('next_id',   $next_id);
    $page->assign('task_text', $task_text);
    $page->assign('subtasks', $db->fetchAllArray($subtasks));
    $page->assign('deps',      $db->fetchAllArray($check_deps));
    $page->assign('parent',    $db->fetchAllArray($parent));
    $page->assign('blocks',    $db->fetchAllArray($check_blocks));
    $page->assign('votes',    $db->fetchAllArray($get_votes));
    $page->assign('penreqs',   $db->fetchAllArray($get_pending));
    $page->assign('d_open',    $db->fetchOne($open_deps));
    $page->assign('watched',   $db->fetchOne($watching));
    $page->assign('reopened',  $db->fetchOne($reopened));
    $page->pushTpl('details.view.tpl');

    ////////////////////////////
    // tabbed area

    // Comments + cache
    $sql = $db->Query('  SELECT * FROM {comments} c
                      LEFT JOIN {cache} ca ON (c.comment_id = ca.topic AND ca.type = ?)
                          WHERE task_id = ?
                       ORDER BY date_added ASC',
                           array('comm', $task_id));

    $page->assign('comments', $db->fetchAllArray($sql));

    // Comment events
    $sql = get_events($task_id, ' AND (event_type = 3 OR event_type = 14)');
    $comment_changes = array();
    while ($row = $db->FetchRow($sql)) {
        $comment_changes[$row['event_date']][] = $row;
    }
    $page->assign('comment_changes', $comment_changes);

    // Comment attachments
    $attachments = array();
    $sql = $db->Query('SELECT *
                         FROM {attachments} a, {comments} c
                        WHERE c.task_id = ? AND a.comment_id = c.comment_id',
                       array($task_id));
    while ($row = $db->FetchRow($sql)) {
        $attachments[$row['comment_id']][] = $row;
    }
    $page->assign('comment_attachments', $attachments);

    // Comment links
    $links = array();
    $sql = $db->Query('SELECT *
	                 FROM {links} l, {comments} c
			WHERE c.task_id = ? AND l.comment_id = c.comment_id',
	               array($task_id));
    while ($row = $db->FetchRow($sql)) {
	$links[$row['comment_id']][] = $row;
    }
    $page->assign('comment_links', $links);

    // Relations, notifications and reminders
    $sql = $db->Query('SELECT  t.*, r.*, s.status_name, res.resolution_name
                         FROM  {related} r
                    LEFT JOIN  {tasks} t ON (r.related_task = t.task_id AND r.this_task = ? OR r.this_task = t.task_id AND r.related_task = ?)
                    LEFT JOIN  {list_status} s ON t.item_status = s.status_id
                    LEFT JOIN  {list_resolution} res ON t.resolution_reason = res.resolution_id
                        WHERE  t.task_id is NOT NULL AND is_duplicate = 0 AND ( t.mark_private = 0 OR ? = 1 )
                     ORDER BY  t.task_id ASC',
            array($task_id, $task_id, $user->perms('manage_project')));
    $page->assign('related', $db->fetchAllArray($sql));

    $sql = $db->Query('SELECT  t.*, r.*, s.status_name, res.resolution_name
                         FROM  {related} r
                    LEFT JOIN  {tasks} t ON r.this_task = t.task_id
                    LEFT JOIN  {list_status} s ON t.item_status = s.status_id
                    LEFT JOIN  {list_resolution} res ON t.resolution_reason = res.resolution_id
                        WHERE  is_duplicate = 1 AND r.related_task = ?
                     ORDER BY  t.task_id ASC',
                      array($task_id));
    $page->assign('duplicates', $db->fetchAllArray($sql));

    $sql = $db->Query('SELECT  *
                         FROM  {notifications} n
                    LEFT JOIN  {users} u ON n.user_id = u.user_id
                        WHERE  n.task_id = ?', array($task_id));
    $page->assign('notifications', $db->fetchAllArray($sql));

    $sql = $db->Query('SELECT  *
                         FROM  {reminders} r
                    LEFT JOIN  {users} u ON r.to_user_id = u.user_id
                        WHERE  task_id = ?
                     ORDER BY  reminder_id', array($task_id));
    $page->assign('reminders', $db->fetchAllArray($sql));


    $page->pushTpl('details.tabs.tpl');

    if ($user->perms('view_comments') || $proj->prefs['others_view'] || ($user->isAnon() && $task_details['task_token'] && Get::val('task_token') == $task_details['task_token'])) {
        $page->pushTpl('details.tabs.comment.tpl');
    }

    $page->pushTpl('details.tabs.related.tpl');

    if ($user->perms('manage_project')) {
        $page->pushTpl('details.tabs.notifs.tpl');
        $page->pushTpl('details.tabs.remind.tpl');
    }

    $page->pushTpl('details.tabs.history.tpl');

    $page->pushTpl('details.tabs.efforttracking.tpl');
}
?>
