<div id="toolbox">
  <h3><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('tasktypeed')); ?></h3>

  <?php
  $this->assign('list_type', 'tasktype');
  $this->assign('rows', $proj->listTaskTypes(true));
  $this->display('common.list.tpl');
  ?>
</div>