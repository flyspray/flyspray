<div id="toolbox" class="toolbox_<?php echo $area; ?>">
<h3><?= eL('tasktypes') ?></h3>
<?php
	$this->assign('list_type', 'tasktype');
	$this->assign('rows', $proj->listTaskTypes(true));
	$this->display('common.list.tpl');
?>
</div>
