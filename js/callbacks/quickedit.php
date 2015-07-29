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
}elseif( Post::val('csrftoken')==$_SESSION['csrftoken']){
        # empty
}else{
        header(':', true, 412); # 'Precondition Failed'
        die('wrongtoken');
}

$task = Flyspray::GetTaskDetails(Post::val('task_id'));
if (!$user->can_edit_task($task)){
    header(':', true, 403); # 'Forbidden'
    #Flyspray::show_error(L('nopermission'));
    die(L('nopermission'));
}
if(Post::val('name') == "due_date"){
    $value = Flyspray::strtotime(Post::val('value'));
    $value = intval($value);
}
elseif(Post::val('name') == "estimated_effort"){
    $value = effort::EditStringToSeconds(Post::val('value'), $proj->prefs['hours_per_manday'], $proj->prefs['estimated_effort_format']);
    $value = intval($value);
}
else {
    $value = Post::val('value');
}

$oldvalue = $task[Post::val('name')];

$time=time();
$sql = $db->Query("UPDATE {tasks} SET " . Post::val('name') . " = ?,last_edited_time = ? WHERE task_id = ?", array($value, $time, Post::val('task_id')));

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
