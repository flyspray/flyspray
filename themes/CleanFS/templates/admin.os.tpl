<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?= eL('oslist') ?></h2>

<?php
	$this->assign('list_type', 'os');
	$this->assign('rows', $proj->listOs(true));
	$this->display('common.list.tpl');
?>
</div>
