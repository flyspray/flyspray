<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?= eL('tasktypes') ?></h2>

<?php
	$this->assign('list_type', 'tasktype');
	$this->assign('rows', $proj->listTaskTypes(true));
	$this->display('common.list.tpl');
?>
</div>
