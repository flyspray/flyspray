<div id="toolbox">
  <h3><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('verlisted')); ?></h3>
<?php
$this->assign('list_type', 'version');
$this->assign('rows', $proj->listVersions(true));
  
$systemwide = new Project(0);
$this->assign('sysrows', $systemwide->listVersions(true));
  
$this->display('common.list.tpl');
?>
</div>
