<div id="toolbox">
  <h3>{L('resolutionlist')}</h3>
  <?php
  $this->assign('list_type', 'resolution');
  $this->assign('rows', $proj->listResolutions(true));
  $this->display('common.list.tpl');
  ?>
</div>
