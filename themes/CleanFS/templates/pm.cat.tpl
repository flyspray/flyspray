<?php $this->assign('sysrows', $proj->listCategories(0, false, true, false)); ?>
<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('catlisted')); ?></h2>

	<?php $this->display('common.cat.tpl'); ?>
</div>
