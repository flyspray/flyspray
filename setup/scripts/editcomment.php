<?php

  /************************************\
  | Edit comment                       |
  | ~~~~~~~~~~~~                       |
  | This script allows users           |
  | to edit comments attached to tasks |
  \************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

$sql = $db->Query("SELECT  c.*, u.real_name
                     FROM  {comments} c
               INNER JOIN  {users}    u ON c.user_id = u.user_id
                    WHERE  comment_id = ? AND task_id = ?",
                    array(Get::num('id', 0), Get::num('task_id', 0)));

$page->assign('comment', $comment = $db->FetchRow($sql));

if (!$user->can_edit_comment($comment)) {
    Flyspray::show_error(11);
}

$page->pushTpl('editcomment.tpl');

?>
