<div id="toolbox" class="toolbox_<?php echo $area; ?>">
<h3><?= eL('admintoolbox') ?> :: <?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?= eL('newuser') ?></h3>
<?php $this->display('common.newuser.tpl'); ?>
</div>
