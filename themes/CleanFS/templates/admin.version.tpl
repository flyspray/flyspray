<div id="toolbox" class="toolbox_<?php echo $area; ?>">
<h3><?= eL('versionlist') ?></h3>
<?php
  $this->assign('list_type', 'version');
  $this->assign('rows', $proj->listVersions(true));
  $this->display('common.list.tpl');
?>
</div>
