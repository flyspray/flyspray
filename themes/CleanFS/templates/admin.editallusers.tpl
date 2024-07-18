<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?= eL('admintoolbox') ?> :: <?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?= eL('editallusers') ?></h2>

	<?php $this->display('common.editallusers.tpl'); ?>
</div>
