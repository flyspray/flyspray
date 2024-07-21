<div class="box">
<?php
$tenses = [
	1 => Filters::noXSS(L('past')),
	2 => Filters::noXSS(L('present')),
	3 => Filters::noXSS(L('future'))
];
?>
<h2><?php echo Filters::noXSS(L('roadmap')); ?>: <?php echo $proj->prefs['project_title']; ?></h2>

<script type="text/javascript">
function hideAll(allTasks) {
	for (i = 0; i < allTasks.length; i++) {
		if (!allTasks[i]) continue;

		hidestuff('dd'+ allTasks[i]);
		hidestuff('hide'+ allTasks[i]);
		showstuff('expand'+ allTasks[i], 'inline');
	}
}

function showAll(allTasks) {
	for (i = 0; i < allTasks.length; i++) {
		if (!allTasks[i]) continue;

		showstuff('dd'+ allTasks[i]);
		hidestuff('expand'+ allTasks[i]);
		showstuff('hide'+ allTasks[i], 'inline');
	}
}

<?php foreach($data as $milestone):
	$taskids = [];

	foreach($milestone['open_tasks'] as $task) {
		$taskids[] = $task['task_id'];
	}
?>
allTasks<?php echo Filters::noXSS($milestone['id']); ?> = [<?php echo implode(',', $taskids); ?>];
<?php endforeach; ?>
</script>
<?php if (!count($data)): ?>
	<p><em><?php echo Filters::noXSS(L('noroadmap')); ?></em></p>
<?php else: ?>
<div id="roadmaps">
<?php foreach($data as $milestone): ?>

<div class="box roadmap">
	<h3>
		<?php echo Filters::noXSS(L('roadmapfor')); ?> <?php echo Filters::noXSS($milestone['name']); ?> (<?php echo $tenses[$milestone['tense']]; ?>)
	</h3>

	<div class="progress_bar_container" style="width: 250px;">
		<span><?php echo Filters::noXSS($milestone['percent_complete']); ?>%</span>
		<div class="progress_bar" style="width:<?php echo Filters::noXSS($milestone['percent_complete']); ?>%"></div>
	</div>

	<p style="margin-top: 1em;"><?php echo Filters::noXSS($milestone['percent_complete']); ?><?php echo Filters::noXSS(L('of')); ?>
		<a href="<?php echo Filters::noXSS($baseurl); ?>index.php?do=index&amp;tasks=&amp;project=<?php echo Filters::noXSS($proj->id); ?>&amp;due=<?php echo Filters::noXSS($milestone['id']); ?>&amp;status[]=">
			<?php echo Filters::noXSS(count($milestone['all_tasks'])); ?> <?php echo Filters::noXSS(L('tasks')); ?>
		</a> <?php echo Filters::noXSS(L('completed')); ?>

		<?php if(count($milestone['open_tasks'])): ?>
			<a href="<?php echo Filters::noXSS($baseurl); ?>index.php?do=index&amp;tasks=&amp;project=<?php echo Filters::noXSS($proj->id); ?>&amp;due=<?php echo Filters::noXSS($milestone['id']); ?>"><?php echo Filters::noXSS(count($milestone['open_tasks'])); ?> <?php echo Filters::noXSS(L('opentasks')); ?>:</a>
		<?php endif; ?>

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
			}
	?></p>

	<?php if ($user->perms('view_estimated_effort')) { ?>
	<p><?php
		echo Filters::noXSS(L('opentasks')); ?> - <?php echo Filters::noXSS(L('totalestimatedeffort')); ?>: <?php echo effort::SecondsToString($total_estimated, $proj->prefs['hours_per_manday'], $proj->prefs['estimated_effort_format']);
	?></p>
	<?php } ?>

	<?php if ($user->perms('view_current_effort_done')) { ?>
	<p><?php
		echo Filters::noXSS(L('opentasks')); ?> - <?php echo Filters::noXSS(L('currenteffortdone')); ?>: <?php echo effort::SecondsToString($actual_effort, $proj->prefs['hours_per_manday'], $proj->prefs['current_effort_done_format']);
	?></p>
	<?php } ?>

	<?php if (count($milestone['open_tasks'])): ?>
	<div>
		<a href="javascript:showAll(allTasks<?php echo Filters::noXSS($milestone['id']); ?>)" class="button"><?php echo Filters::noXSS(L('expandall')); ?></a>
		<a href="javascript:hideAll(allTasks<?php echo Filters::noXSS($milestone['id']); ?>)" class="button"><?php echo Filters::noXSS(L('collapseall')); ?></a>
	</div>
	<?php endif; ?>


<?php if(count($milestone['open_tasks'])): ?>
	<?php foreach($milestone['open_tasks'] as $task): ?>
		<div class="severity<?php echo Filters::noXSS($task['task_severity']); ?>">
			<p>
				<?php echo tpl_tasklink($task['task_id']); ?>

				<span>
					<a id="expand<?php echo Filters::noXSS($task['task_id']); ?>" class="button" href="javascript:showstuff('dd<?php echo Filters::noXSS($task['task_id']); ?>');hidestuff('expand<?php echo Filters::noXSS($task['task_id']); ?>');showstuff('hide<?php echo Filters::noXSS($task['task_id']); ?>', 'inline-block')" title="<?php echo Filters::noXSS(L('expand')); ?>"><span class="fas fa-caret-down"></span></a>
					<a class="button hide" id="hide<?php echo Filters::noXSS($task['task_id']); ?>" href="javascript:hidestuff('dd<?php echo Filters::noXSS($task['task_id']); ?>');hidestuff('hide<?php echo Filters::noXSS($task['task_id']); ?>');showstuff('expand<?php echo Filters::noXSS($task['task_id']); ?>', 'inline-block')" title="<?php echo Filters::noXSS(L('collapse')); ?>"><span class="fas fa-caret-up"></span></a>
				</span>
			</p>

			<div id="dd<?php echo Filters::noXSS($task['task_id']); ?>" style="display: none;">
			<?php echo TextFormatter::render($task['detailed_desc'], 'rota', $task['task_id'], $task['content']); ?>
			</div>
		</div>
	<?php endforeach; ?>
<?php endif; ?>
</div>
<?php endforeach; ?>

	</div>
<?php endif; ?>

	<p><a href="<?php echo Filters::noXSS(CreateURL('roadmap', $proj->id, null, array('txt' => 'true'))); ?>">
<?php
/*
	<img src="<?php echo Filters::noXSS($this->get_image('mime/text')); ?>" alt="" />
*/
?><?php echo Filters::noXSS(L('textversion')); ?></a></p>

</div>

