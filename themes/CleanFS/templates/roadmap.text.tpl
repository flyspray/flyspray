=== <?php echo Filters::noXSS($proj->prefs['project_title']); ?> ===

<?php foreach($data as $milestone): ?>
<?php echo Filters::noXSS(L('roadmapfor')); ?> <?php echo Filters::noXSS($milestone['name']); ?>


<?php echo Filters::noXSS($milestone['percent_complete']); ?><?php echo Filters::noXSS(L('of')); ?> <?php echo Filters::noXSS(count($milestone['all_tasks'])); ?> <?php echo Filters::noXSS(L('tasks')); ?> <?php echo Filters::noXSS(L('completed')); ?> <?php
   if(count($milestone['open_tasks'])):
   ?><?php echo Filters::noXSS(count($milestone['open_tasks'])); ?> <?php echo Filters::noXSS(L('opentasks')); ?>:<?php
   endif; ?>
<?php
    if ($proj->prefs['use_effort_tracking']) {
        $total_estimated = 0;
        $actual_effort = 0;

        foreach($milestone['open_tasks'] as $task) {
            $total_estimated += $task['estimated_effort'];
            $effort = new effort($task['task_id'],0);
            $effort->populateDetails();

            foreach($effort->details as $details) {
                $actual_effort += $details['effort'];
            }
            $effort = null;
        }
    // }
?>

<?php
    if ($user->perms('view_effort')) {
        echo Filters::noXSS(L('opentasks')); ?> - <?php echo Filters::noXSS(L('totalestimatedeffort')); ?>: <?php echo effort::SecondsToString($total_estimated, $proj->prefs['hours_per_manday'], $proj->prefs['estimated_effort_format']);
} ?>

<?php
    if ($user->perms('view_actual_effort')) {
        echo Filters::noXSS(L('opentasks')); ?> - <?php echo Filters::noXSS(L('actualeffort')); ?>: <?php echo effort::SecondsToString($actual_effort, $proj->prefs['hours_per_manday'], $proj->prefs['current_effort_done_format']);
} ?>
<?php } ?>

<?php if(count($milestone['open_tasks'])): ?>

<?php foreach($milestone['open_tasks'] as $task):
          if(!$user->can_view_task($task)) continue; ?>
FS#<?php echo Filters::noXSS($task['task_id']); ?> - <?php echo Filters::noXSS($task['item_summary']); ?>


<?php endforeach; ?>

<?php endif; ?>

<?php endforeach; ?>
