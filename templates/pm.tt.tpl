<div id="toolbox">
  <h3>{L('pmtoolbox')} :: {$proj->prefs['project_title']} : {L('tasktypeed')}</h3>

  <fieldset class="box">
    <legend>{L('tasktypes')}</legend>
    <?php
    $this->assign('list_type', 'tasktype');
    $this->assign('rows', $proj->listTaskTypes(true));
    $this->display('common.list.tpl');
    ?>
  </fieldset>
</div>
