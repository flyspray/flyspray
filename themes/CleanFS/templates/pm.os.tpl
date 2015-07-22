<div id="toolbox">
  <h3><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('oslisted')); ?></h3>
<?php
$this->assign('list_type', 'os');
$this->assign('rows', $proj->listOs(true));
  
$systemwide = new Project(0);
$this->assign('sysrows', $systemwide->listOs(true));

$this->display('common.list.tpl');
?>
</div>
