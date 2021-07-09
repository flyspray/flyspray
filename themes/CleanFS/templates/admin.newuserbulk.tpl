<div id="toolbox">
<h3><?= eL('admintoolbox') ?> :: <?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?= eL('newuserbulk') ?></h3>
<?php $this->display('common.newuserbulk.tpl'); ?>
</div>
