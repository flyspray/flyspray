=== <?php echo Filters::noXSS($proj->prefs['project_title']); ?> ===

<?php foreach($data as $milestone): ?>
<?php echo Filters::noXSS(L('roadmapfor')); ?> <?php echo Filters::noXSS($milestone['name']); ?>


<?php echo Filters::noXSS($milestone['percent_complete']); ?><?php echo Filters::noXSS(L('of')); ?> <?php echo Filters::noXSS(count($milestone['all_tasks'])); ?> <?php echo Filters::noXSS(L('tasks')); ?> <?php echo Filters::noXSS(L('completed')); ?> <?php
   if(count($milestone['open_tasks'])):
   ?><?php echo Filters::noXSS(count($milestone['open_tasks'])); ?> <?php echo Filters::noXSS(L('opentasks')); ?>:<?php
   endif; ?>

<?php if(count($milestone['open_tasks'])): ?>

    <?php foreach($milestone['open_tasks'] as $task):
          if(!$user->can_view_task($task)) continue; ?>
    FS#<?php echo Filters::noXSS($task['task_id']); ?> - <?php echo Filters::noXSS($task['item_summary']); ?>


    <?php endforeach; ?>

<?php endif; ?>

<?php endforeach; ?>
