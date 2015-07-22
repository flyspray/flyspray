<div id="toolbox">
  <h3><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('resed')); ?></h3>
<?php
$this->assign('list_type', 'resolution');
$this->assign('rows', $proj->listResolutions(true));
  
$systemwide = new Project(0);
$this->assign('sysrows', $systemwide->listResolutions(true));

$this->display('common.list.tpl');
?>
</div>
