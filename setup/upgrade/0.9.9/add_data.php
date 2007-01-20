<?php
   /**********************************************************\
   | This script adds/deletes data what can't be added to      |
   | the XML schema files.                                     |
   \***********************************************************/

// New status list, make sure data is only inserted if we have an empty table
$sql = $db->Query('SELECT count(*) FROM {list_status}');
if ($db->FetchOne($sql) < 1) {
    $db->Query("INSERT INTO {list_status} (`status_id`, `status_name`, `list_position`, `show_in_list`, `project_id`) VALUES (1, 'Unconfirmed', 1, 1, 0)");
    $db->Query("INSERT INTO {list_status} (`status_id`, `status_name`, `list_position`, `show_in_list`, `project_id`) VALUES (2, 'New', 2, 1, 0)");
    $db->Query("INSERT INTO {list_status} (`status_id`, `status_name`, `list_position`, `show_in_list`, `project_id`) VALUES (3, 'Assigned', 3, 1, 0)");
    $db->Query("INSERT INTO {list_status} (`status_id`, `status_name`, `list_position`, `show_in_list`, `project_id`) VALUES (4, 'Researching', 4, 1, 0)");
    $db->Query("INSERT INTO {list_status} (`status_id`, `status_name`, `list_position`, `show_in_list`, `project_id`) VALUES (5, 'Waiting on Customer', 5, 1, 0)");
    $db->Query("INSERT INTO {list_status} (`status_id`, `status_name`, `list_position`, `show_in_list`, `project_id`) VALUES (6, 'Requires testing', 6, 1, 0)");
}

if (Post::val('replace_resolution')) {
    $db->Query('UPDATE {list_resolution} SET resolution_name = ? WHERE resolution_id = ?', array('Duplicate (the real one)', 6));
}

$db->Query("DELETE FROM {list_status} WHERE status_id = 7");
$db->Query("DELETE FROM {notifications} WHERE user_id = 0 OR task_id = 0");

$db->Query("UPDATE {tasks} SET closure_comment='' WHERE closure_comment='0'");
$db->Query("UPDATE {groups} SET `add_to_assignees` = '1' WHERE `assign_others_to_self` =1 ");
$db->Query("UPDATE {tasks} SET `due_date` = 0 WHERE `due_date` = ''");
$db->Query("UPDATE {groups} SET add_votes = 1 WHERE group_id = 2 OR group_id = 3 OR group_id = 6");
$db->Query("UPDATE {groups} SET `edit_assignments` = '1' WHERE `group_id` =2 LIMIT 1");
$db->Query("UPDATE {history} SET event_type = 3 WHERE event_type = 0");
$db->Query("UPDATE {history} SET event_type = 11 WHERE event_type = 15");
$db->Query("UPDATE {history} SET event_type = 12 WHERE event_type = 16");
$db->Query("UPDATE {history} SET field_changed = 'project_id' WHERE field_changed = 'attached_to_project'");

?>