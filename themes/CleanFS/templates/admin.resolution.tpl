<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?= eL('resolutionlist') ?></h2>

<?php
	$this->assign('list_type', 'resolution');
	$this->assign('rows', $proj->listResolutions(true));
	$this->display('common.list.tpl');
?>
</div>
