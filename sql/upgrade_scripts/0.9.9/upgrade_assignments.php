<?php
   /**********************************************************\
   | This script moves data from {dbprefix)tasks.assigned_to   |
   | to {dbprefix}assigned.  This is to implement multiple     |
   | assignees per task as described in FS#329.  It only needs |
   | to be run once to do the conversion.                      |
   \***********************************************************/
   
define('IN_FS', true);

require_once '../../../includes/class.flyspray.php';
require_once '../../../includes/constants.inc.php';
require_once BASEDIR . '/includes/class.database.php';

$db = new Database;
$db->dbOpenFast($conf['database']);

$db->Query("ALTER TABLE {assigned} DROP user_or_group");
if (!strcasecmp($conf['database']['dbtype'], 'pgsql')) {
    $db->Query('ALTER TABLE {assigned} ALTER assignee_id TYPE integer');
    $db->Query('ALTER TABLE {assigned} ALTER assignee_id SET DEFAULT 0');
    $db->Query('ALTER TABLE {assigned} ALTER assignee_id SET NOT NULL');
    $db->Query('ALTER TABLE {assigned} RENAME assignee_id TO user_id');
} else {
    $db->Query("ALTER TABLE {assigned} CHANGE assignee_id user_id MEDIUMINT( 5 ) DEFAULT '0' NOT NULL");
}

$db->Query("ALTER TABLE {assigned} ADD INDEX ( task_id , user_id )");
                          
$check_sql = $db->Query("SELECT task_id, assigned_to
                           FROM {tasks}
                          WHERE assigned_to > '0'");

while ($row = $db->FetchRow($check_sql))
{
   $db->Query('INSERT INTO {assigned}
                           (task_id, user_id)
                    VALUES (?,?)',
                           array($row['task_id'], $row['assigned_to']));

   $db->Query('UPDATE {tasks}
                  SET assigned_to = 0
                WHERE task_id = ?',
                      array($row['task_id']));
}

$db->Query("ALTER TABLE {tasks} DROP assigned_to");
?>