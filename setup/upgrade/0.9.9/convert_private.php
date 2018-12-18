<?php
   /**********************************************************\
   | This script converts the private/public history entries   |
   \***********************************************************/
   

$check_sql = $db->query('SELECT * FROM {history} WHERE event_type = 26 OR event_type = 27');

while ($row = $db->fetchRow($check_sql))
{
    $db->query('DELETE FROM {history} WHERE history_id = ?', array($row['history_id']));
    if ($row['event_type'] == 26) {
        $row['old_value'] = 0;
        $row['new_value'] = 1;
    }
    if ($row['event_type'] == 27) {
        $row['old_value'] = 1;
        $row['new_value'] = 0;
    }
    $db->query("INSERT INTO {history} (task_id, user_id, event_date, event_type, field_changed, old_value, new_value)
                                      VALUES(?, ?, ?, 0, 'mark_private', ?, ?)",
                                      array($row['task_id'], $row['user_id'], $row['event_date'], $row['old_value'], $row['new_value']));
}


?>
