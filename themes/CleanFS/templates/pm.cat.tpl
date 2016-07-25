<?php $this->assign('sysrows', $proj->listCategories(0, false, true, false)); ?>
<div id="toolbox">
  <h3><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('catlisted')); ?></h3>
  <?php $this->display('common.cat.tpl'); ?>
</div>
