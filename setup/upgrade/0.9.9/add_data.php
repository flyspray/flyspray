<?php
   /**********************************************************\
   | This script adds/deletes data what can't be added to      |
   | the XML schema files.                                     |
   \***********************************************************/

// New status list, make sure data is only inserted if we have an empty table
$sql = $db->query('SELECT count(*) FROM {list_status}');
if ($db->fetchOne($sql) < 1) {
    $db->query("INSERT INTO {list_status} (status_name, list_position, show_in_list, project_id) VALUES ('Unconfirmed', 1, 1, 0)");
    $db->query("INSERT INTO {list_status} (status_name, list_position, show_in_list, project_id) VALUES ('New', 2, 1, 0)");
    $db->query("INSERT INTO {list_status} (status_name, list_position, show_in_list, project_id) VALUES ('Assigned', 3, 1, 0)");
    $db->query("INSERT INTO {list_status} (status_name, list_position, show_in_list, project_id) VALUES ('Researching', 4, 1, 0)");
    $db->query("INSERT INTO {list_status} (status_name, list_position, show_in_list, project_id) VALUES ('Waiting on Customer', 5, 1, 0)");
    $db->query("INSERT INTO {list_status} (status_name, list_position, show_in_list, project_id) VALUES ('Requires testing', 6, 1, 0)");
}

if (Post::val('replace_resolution')) {
    $db->query('UPDATE {list_resolution} SET resolution_name = ? WHERE resolution_id = ?', array('Duplicate (the real one)', 6));
}

$db->query("DELETE FROM {list_status} WHERE status_id = 7");
$db->query("DELETE FROM {notifications} WHERE user_id = 0 OR task_id = 0");

$db->query("UPDATE {tasks} SET closure_comment='' WHERE closure_comment='0'");
$db->query("UPDATE {groups} SET add_to_assignees = '1' WHERE assign_others_to_self =1 ");
$db->query("UPDATE {groups} SET add_votes = 1 WHERE group_id = 2 OR group_id = 3 OR group_id = 6");
$db->query("UPDATE {groups} SET edit_assignments = '1' WHERE group_id = 2");
$db->query("UPDATE {history} SET event_type = 3 WHERE event_type = 0");
$db->query("UPDATE {history} SET event_type = 11 WHERE event_type = 15");
$db->query("UPDATE {history} SET event_type = 12 WHERE event_type = 16");
$db->query("UPDATE {history} SET field_changed = 'project_id' WHERE field_changed = 'attached_to_project'");

?>
