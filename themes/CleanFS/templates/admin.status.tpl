<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?= eL('taskstatuses') ?></h2>

<?php
	$this->assign('list_type', 'status');
	$this->assign('rows', $proj->listTaskStatuses(true));
	$this->display('common.list.tpl');
?>
</div>
