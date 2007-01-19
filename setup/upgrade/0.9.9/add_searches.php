<?php
   /**********************************************************\
   | This script addes some default saved searches for every   |
   | user.                                                     |
   \***********************************************************/

$check_sql = $db->Query('SELECT user_id FROM {users}');

while ($row = $db->FetchRow($check_sql))
{
    $db->Query('DELETE FROM {searches} WHERE (name = ? OR name = ? OR name = ?) AND user_id = ?', array('Tasks I watch', 'Tasks assigned to me', 'Tasks I opened', $row['user_id']));
    $db->Query('INSERT INTO {searches} (user_id, name, search_string, time) VALUES (?, \'Tasks I watch\', \'a:16:{s:6:"string";N;s:4:"type";a:1:{i:0;s:0:"";}s:3:"sev";a:1:{i:0;s:0:"";}s:3:"due";a:1:{i:0;s:0:"";}s:3:"dev";N;s:3:"cat";a:1:{i:0;s:0:"";}s:6:"status";a:1:{i:0;s:4:"open";}s:5:"order";N;s:4:"sort";N;s:7:"percent";a:1:{i:0;s:0:"";}s:6:"opened";N;s:18:"search_in_comments";N;s:14:"search_for_all";N;s:8:"reported";a:1:{i:0;s:0:"";}s:12:"only_primary";N;s:12:"only_watched";s:1:"1";}\', ' . time() . ')',
               array($row['user_id']));
    $db->Query('INSERT INTO {searches} (user_id, name, search_string, time) VALUES (?, \'Tasks assigned to me\', \'a:16:{s:6:"string";N;s:4:"type";a:1:{i:0;s:0:"";}s:3:"sev";a:1:{i:0;s:0:"";}s:3:"due";a:1:{i:0;s:0:"";}s:3:"dev";s:' . strlen($row['user_id']) . ':"' . $row['user_id'] .'";s:3:"cat";a:1:{i:0;s:0:"";}s:6:"status";a:1:{i:0;s:4:"open";}s:5:"order";N;s:4:"sort";N;s:7:"percent";a:1:{i:0;s:0:"";}s:6:"opened";N;s:18:"search_in_comments";N;s:14:"search_for_all";N;s:8:"reported";a:1:{i:0;s:0:"";}s:12:"only_primary";N;s:12:"only_watched";N;}\', ' . time() . ')',
               array($row['user_id']));
    $db->Query('INSERT INTO {searches} (user_id, name, search_string, time) VALUES (?, \'Tasks I opened\', \'a:16:{s:6:"string";N;s:4:"type";a:1:{i:0;s:0:"";}s:3:"sev";a:1:{i:0;s:0:"";}s:3:"due";a:1:{i:0;s:0:"";}s:3:"dev";N;s:3:"cat";a:1:{i:0;s:0:"";}s:6:"status";a:1:{i:0;s:4:"open";}s:5:"order";N;s:4:"sort";N;s:7:"percent";a:1:{i:0;s:0:"";}s:6:"opened";s:' . strlen($row['user_id']) . ':"' . $row['user_id'] .'";s:18:"search_in_comments";N;s:14:"search_for_all";N;s:8:"reported";a:1:{i:0;s:0:"";}s:12:"only_primary";N;s:12:"only_watched";N;}\', ' . time() . ')',
               array($row['user_id']));
}


?>

