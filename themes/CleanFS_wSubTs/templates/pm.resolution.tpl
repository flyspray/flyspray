<div id="toolbox">
  <h3>{$proj->prefs['project_title']} : {L('resed')}</h3>

  <?php
  $this->assign('list_type', 'resolution');
  $this->assign('rows', $proj->listResolutions(true));
  $this->display('common.list.tpl');
  ?>
</div>
