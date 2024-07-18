<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?= eL('versionlist') ?></h2>

<?php
	$this->assign('list_type', 'version');
	$this->assign('rows', $proj->listVersions(true));
	$this->display('common.list.tpl');
?>
</div>
