=== {$proj->prefs['project_title']} ===

<?php foreach($data as $milestone): ?>
{L('roadmapfor')} {$milestone['name']}

{$milestone['percent_complete']}{L('of')} {count($milestone['all_tasks'])} {L('tasks')} {L('completed')} <?php
   if(count($milestone['open_tasks'])):
   ?>{count($milestone['open_tasks'])} {L('opentasks')}:<?php
   endif; ?>
<?php
    if($proj->prefs['use_effort_tracking'])
    {

    $total_estimated = 0;
    $actual_effort = 0;

    foreach($milestone['open_tasks'] as $task)
    {
    $total_estimated += $task['estimated_effort'];
    $effort = new effort($task['task_id'],0);
    $effort->populateDetails();

    foreach($effort->details as $details)
    {
    $actual_effort += $details['effort'];
    }
    $effort = null;
    }

?>

{L('opentasks')} - {L('totalestimatedeffort')}: <?php echo ConvertSeconds($total_estimated *60 *60); ?>

{L('opentasks')} - {L('actualeffort')}: <?php echo ConvertSeconds($actual_effort); ?>
<?php } ?>

<?php if(count($milestone['open_tasks'])): ?>

<?php foreach($milestone['open_tasks'] as $task):
          if(!$user->can_view_task($task)) continue; ?>
FS#{$task['task_id']} - {$task['item_summary']}

<?php endforeach; ?>

<?php endif; ?>

<?php endforeach; ?>
