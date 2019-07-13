<?php
/*
    Checks if a related task belongs to a different project.
*/

define('IN_FS', true);

require_once '../../header.php';

$sql = $db->query('SELECT project_id
                        FROM  {tasks}
                        WHERE  task_id = ?',
                  array(Get::val('related_task')));

$relatedproject = $db->fetchOne($sql);

if (Get::val('project') == $relatedproject || !$relatedproject) {
    echo 'ok';
}
?>
