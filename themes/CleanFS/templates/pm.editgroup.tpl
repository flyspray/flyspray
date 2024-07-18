<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?= eL('pmtoolbox') ?> :: <?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?= eL('editgroup') ?></h2>

	<?php $this->display('common.editgroup.tpl'); ?>
</div>
