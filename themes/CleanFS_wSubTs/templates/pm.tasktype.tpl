<div id="toolbox">
  <h3>{$proj->prefs['project_title']} : {L('tasktypeed')}</h3>

  <?php
  $this->assign('list_type', 'tasktype');
  $this->assign('rows', $proj->listTaskTypes(true));
  $this->display('common.list.tpl');
  ?>
</div>