<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('verlisted')); ?></h2>

<?php
$this->assign('list_type', 'version');
$this->assign('rows', $proj->listVersions(true));

$systemwide = new Project(0);
$this->assign('sysrows', $systemwide->listVersions(true));

$this->display('common.list.tpl');
?>
</div>
