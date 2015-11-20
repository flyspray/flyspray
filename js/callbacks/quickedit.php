<?php

define('IN_FS', true);

header('Content-type: text/html; charset=utf-8');

require_once('../../header.php');
global $proj, $fs;

$baseurl = dirname(dirname($baseurl)) .'/' ;

if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')) {
    $user = new User(Cookie::val('flyspray_userid'));
    $user->check_account_ok();
} else {
    $user = new User(0, $proj);
}

// don't allow anonymous users to access this page at all
if ($user->isAnon()) {
    die();
}
load_translations();

if( !Post::has('csrftoken') ){
        header(':', true, 428); # 'Precondition Required'
        die('missingtoken'); 
} elseif( Post::val('csrftoken')==$_SESSION['csrftoken']){
        # ok
} else{
        header(':', true, 412); # 'Precondition Failed'
        die('wrongtoken');
}

$task = Flyspray::GetTaskDetails(Post::val('task_id'));
if (!$user->can_edit_task($task)){
    header(':', true, 403); # 'Forbidden'
    #Flyspray::show_error(L('nopermission'));
    die(L('nopermission'));
}

# check field for update against allowed dbfields for quickedit.
# maybe FUTURE: add (dynamic read from database) allowed CUSTOM FIELDS checks for the project and user
# (if there is urgent request for implementing custom fields into Flyspray and using of tag-feature isn't enough to accomplish - like numbers/dates/timestamps as custom fields)
$allowedFields=array('due_date','item_status','percent_complete','task_type','product_category','task_severity','task_priority','product_version','closedby_version');
if ($proj->prefs['use_effort_tracking'] && $user->perms('track_effort')){
	$allowedFields[]='estimated_effort';
}

if (!in_array(Post::val('name'), $allowedFields)){
	header(':', true, 403);
	die(L('invalidfield'));
}

$value = Post::val('value');

# check if user is not sending manipulated invalid values
switch(Post::val('name')){
	case 'due_date':
		$value = Flyspray::strtotime(Post::val('value'));
		$value = intval($value);
		break;

	case 'estimated_effort':
		$value = effort::EditStringToSeconds(Post::val('value'), $proj->prefs['hours_per_manday'], $proj->prefs['estimated_effort_format']);
		$value = intval($value);
		break;

	case 'task_priority':
	case 'task_severity':
		if(!preg_match("/^[1-5]$/", $value)){
			header(':', true, 403);
			die(L('invalidvalue'));
		}
		break;

	case 'percent_complete':
		if(!is_numeric($value) || $value<0 || $value>100){
			header(':', true, 403);
			die(L('invalidvalue'));
		}
		break;

	case 'item_status':
		$res=$db->Query('SELECT * FROM {list_status} WHERE (project_id=0 OR project_id=?) AND show_in_list=1 AND status_id=?', array($task['project_id'], $value) );
		if($db->countRows($res)<1){
			header(':', true, 403);
			die(L('invalidvalue'));
		}
		break;

	case 'task_type':
		$res=$db->Query('SELECT * FROM {list_tasktype} WHERE (project_id=0 OR project_id=?) AND show_in_list=1 AND tasktype_id=?', array($task['project_id'], $value) );
		if($db->countRows($res)<1){
			header(':', true, 403);
			die(L('invalidvalue'));
		}
		break;

	case 'operating_system':
		$res=$db->Query('SELECT * FROM {list_os} WHERE (project_id=0 OR project_id=?) AND show_in_list=1 AND os_id=?', array($task['project_id'], $value) );
		if($db->countRows($res)<1){
			header(':', true, 403);
			die(L('invalidvalue'));
		}
		break;

	case 'product_category':
		$res=$db->Query('SELECT * FROM {list_category} WHERE (project_id=0 OR project_id=?) AND show_in_list=1 AND category_id=?', array($task['project_id'], $value) );
		if($db->countRows($res)<1){
			header(':', true, 403);
			die(L('invalidvalue'));
		}
		break;

	case 'product_version':
		$res=$db->Query('SELECT * FROM {list_version} WHERE (project_id=0 OR project_id=?) AND show_in_list=1 AND version_id=? AND version_tense=2', array($task['project_id'], $value) );
		if($db->countRows($res)<1){
			header(':', true, 403);
			die(L('invalidvalue'));
		}
		break;
	case 'closedby_version':
		$res=$db->Query('SELECT * FROM {list_version} WHERE (project_id=0 OR project_id=?) AND show_in_list=1 AND version_id=? AND version_tense=3', array($task['project_id'], $value) );
		if($db->countRows($res)<1){
			header(':', true, 403);
			die(L('invalidvalue'));
		}
		break;
	default:
		header(':', true, 403);
		die(L('invalidField'));
		break;
}

$oldvalue = $task[Post::val('name')];

$time=time();
$sql = $db->Query("UPDATE {tasks} SET ".Post::val('name')." = ?,last_edited_time = ? WHERE task_id = ?", array($value, $time, Post::val('task_id')));

# load $proj again of task with correct project_id for getting active notification types in notification class
$proj= new Project($task['project_id']);

// Log the changed field in task history
Flyspray::logEvent($task['task_id'], 3, $value, $oldvalue, Post::val('name'), $time);

// Get the details of the task we just updated to generate the changed-task message
$new_details_full = Flyspray::GetTaskDetails($task['task_id']);
$changes = Flyspray::compare_tasks($task, $new_details_full);
if (count($changes) > 0) {
    $notify = new Notifications;
    $notify->Create(NOTIFY_TASK_CHANGED, $task['task_id'], $changes, null, NOTIFY_BOTH, $proj->prefs['lang_code']);
}

?>
