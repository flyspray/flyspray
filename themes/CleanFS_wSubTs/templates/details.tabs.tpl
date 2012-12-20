<ul id="submenu">
  <?php if ($user->perms('view_comments') || $proj->prefs['others_view'] || ($user->isAnon() && $task_details['task_token'] && Get::val('task_token') == $task_details['task_token'])): ?>
  <li id="commentstab">
  <a href="#comments">{L('comments')} ({!count($comments)})</a>
  </li>
  <?php endif; ?>

  <li id="relatedtab">
  <a href="#related">{L('relatedtasks')} ({!count($related)}/{!count($duplicates)})</a>
  </li>

  <?php if ($user->perms('manage_project')): ?>
  <li id="notifytab">
  <a href="#notify">{L('notifications')} ({!count($notifications)})</a>
  </li>
  <?php if (!$task_details['is_closed']): ?>
  <li id="remindtab">
  <a href="#remind">{L('reminders')} ({!count($reminders)})</a>
  </li>
  <?php endif; ?>
  <?php endif; ?>

  <?php if ($user->perms('view_history')): ?>
  <li id="historytab">
    <a id="historytaba" onmousedown="getHistory('{$task_details['task_id']}', '{#$baseurl}', 'history', '{Get::num('details')}');"
       href="{CreateURL('details', $task_details['task_id'], null)}#history">{L('history')}</a>
  </li>
  <?php endif; ?>
</ul>
