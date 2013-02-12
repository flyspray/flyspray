<div id="toolbox">
  <h3>{$proj->prefs['project_title']} : {L('taskstatuses')}</h3>

  <?php
  $this->assign('list_type', 'status');
  $this->assign('rows', $proj->listTaskStatuses(true));
  $this->display('common.list.tpl');
  ?>
</div>
