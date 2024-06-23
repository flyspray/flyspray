<ul id="submenu">
  <?php if ($user->perms('view_comments') || $proj->prefs['others_view'] || ($user->isAnon() && $task_details['task_token'] && Get::val('task_token') == $task_details['task_token'])): ?>
  <li id="commentstab">
  <a href="#comments"><span class="fas fa-comments"></span><span><?php echo Filters::noXSS(L('comments')); ?> (<?php echo count($comments); ?>)</span></a>
  </li>
  <?php endif; ?>

  <li id="relatedtab">
  <a href="#related"><span class="fas fa-border-all"></span><span><?php echo Filters::noXSS(L('relatedtasks')); ?> (<?php echo count($related); ?>/<?php echo count($duplicates); ?>)</span></a>
  </li>

  <?php if ($user->perms('manage_project')): ?>
  <li id="notifytab">
  <a href="#notify"><span class="fas fa-bell"></span><span><?php echo Filters::noXSS(L('notifications')); ?> (<?php echo count($notifications); ?>)</span></a>
  </li>
  <?php if (!$task_details['is_closed']): ?>
  <li id="remindtab">
  <a href="#remind"><span class="fas fa-user-clock"></span><span><?php echo Filters::noXSS(L('reminders')); ?> (<?php echo count($reminders); ?>)</span></a>
  </li>
  <?php endif; ?>
  <?php endif; ?>

  <?php if ($user->perms('view_history')): ?>
  <li id="historytab">
    <a id="historytaba" onmousedown="getHistory('<?php echo Filters::noXSS($task_details['task_id']); ?>', '<?php echo Filters::noJsXSS($baseurl); ?>', 'history', '<?php echo Filters::noXSS(Get::num('details')); ?>');"
       href="<?php echo Filters::noXSS(CreateURL('details', $task_details['task_id'], null)); ?>#history"><span class="fas fa-timeline"></span><span><?php echo Filters::noXSS(L('history')); ?></span></a>
  </li>
  <?php endif; ?>

    <?php if ($proj->prefs['use_effort_tracking']){ ?>
    <?php if ($user->perms('view_current_effort_done')){ ?>
    <li id="efforttab">
        <a href="#effort"><span class="fas fa-hourglass-half"></span><span><?php echo Filters::noXSS(L('efforttracking')); ?></span></a>
    </li>

    <?php
     }
   } ?>
</ul>
