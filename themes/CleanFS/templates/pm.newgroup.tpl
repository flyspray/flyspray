<div id="toolbox">
<h3><?= eL('pmtoolbox') ?> :: <?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?= eL('createnewgroup') ?></h3>
<?php $this->display('common.newgroup.tpl'); ?>
</div>
