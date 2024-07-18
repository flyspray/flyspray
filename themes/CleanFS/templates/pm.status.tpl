<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('taskstatuses')); ?></h2>

<?php
$this->assign('list_type', 'status');
$this->assign('rows', $proj->listTaskStatuses(true));

$systemwide = new Project(0);
$this->assign('sysrows', $systemwide->listTaskStatuses(true));

$this->display('common.list.tpl');
?>
</div>
