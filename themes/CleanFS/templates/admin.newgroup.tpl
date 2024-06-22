<div id="toolbox" class="toolbox_<?php echo $area; ?>">
<h3><?= eL('admintoolbox') ?> :: <?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?= eL('createnewgroup') ?></h3>
<?php $this->display('common.newgroup.tpl'); ?>
</div>
