<div id="toolbox">
<h3><?= eL('pmtoolbox') ?> :: <?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?= eL('editgroup') ?></h3>
<?php $this->display('common.editgroup.tpl'); ?>
</div>
