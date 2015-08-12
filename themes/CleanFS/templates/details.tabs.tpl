<ul id="submenu">
  <?php if ($user->perms('view_comments') || $proj->prefs['others_view'] || ($user->isAnon() && $task_details['task_token'] && Get::val('task_token') == $task_details['task_token'])): ?>
  <li id="commentstab">
  <a href="#comments"><?php echo Filters::noXSS(L('comments')); ?> (<?php echo count($comments); ?>)</a>
  </li>
  <?php endif; ?>

  <li id="relatedtab">
  <a href="#related"><?php echo Filters::noXSS(L('relatedtasks')); ?> (<?php echo count($related); ?>/<?php echo count($duplicates); ?>)</a>
  </li>

  <?php if ($user->perms('manage_project')): ?>
  <li id="notifytab">
  <a href="#notify"><?php echo Filters::noXSS(L('notifications')); ?> (<?php echo count($notifications); ?>)</a>
  </li>
  <?php if (!$task_details['is_closed']): ?>
  <li id="remindtab">
  <a href="#remind"><?php echo Filters::noXSS(L('reminders')); ?> (<?php echo count($reminders); ?>)</a>
  </li>
  <?php endif; ?>
  <?php endif; ?>

  <?php if ($user->perms('view_history')): ?>
  <li id="historytab">
    <a id="historytaba" onmousedown="getHistory('<?php echo Filters::noXSS($task_details['task_id']); ?>', '<?php echo Filters::noJsXSS($baseurl); ?>', 'history', '<?php echo Filters::noXSS(Get::num('details')); ?>');"
       href="<?php echo Filters::noXSS(CreateURL('details', $task_details['task_id'], null)); ?>#history"><?php echo Filters::noXSS(L('history')); ?></a>
  </li>
  <?php endif; ?>

    <?php if ($proj->prefs['use_effort_tracking']){ ?>
    <?php if ($user->perms('view_effort')){ ?>
    <li id="efforttab">
        <a href="#effort"><?php echo Filters::noXSS(L('efforttracking')); ?></a>
    </li>

    <?php
     }
   } ?>
</ul>
