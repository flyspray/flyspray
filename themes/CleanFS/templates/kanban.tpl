<link rel="stylesheet" href="<?php echo Filters::noXSS($this->themeUrl()); ?>custom_example.css"></link>
<link rel="stylesheet" href="<?php echo Filters::noXSS($this->themeUrl()); ?>kanban.css"></link>
<div class="kanbanboard">
<?php
$c=count($stati);
for ($i=0; $i < $c; $i++): ?>
<div class="col">
<?php
$col = $cols[$i][0]; // tasks
$tcount = $cols[$i][2]; // taskcount
?>
<div class="colname status<?= $stati[$i]['status_id'] ?>"><?= ($tcount>0) ? '<span>'.$tcount.'</span>':'' ?><?= Filters::noXSS($stati[$i]['status_name']) ?></div>
<?php
foreach ($col as $task): ?>
	<div class="task">
	<span class="typ<?= $task['task_type'] ?>" title="<?= Filters::noXSS($task['tasktype_name']) ?>"></span>
	<a href="<?= createUrl('details', $task['task_id']) ?>"><?= Filters::noXSS($task['item_summary']) ?></a>
	<?php if($task['assignedids']): ?>
	<div class="assignedto"><?= tpl_userlinkavatar($task['assignedids'], 20) ?></div>
	<?php endif; ?>
	</div>
<?php endforeach; ?>
</div>
<?php endfor; ?>
</div>
