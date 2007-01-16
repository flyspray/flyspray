<div id="toolbox">
  <h3>{L('pmtoolbox')} :: {$proj->prefs['project_title']} : {L('verlisted')}</h3>

  <fieldset class="box">
    <legend>{L('versions')}</legend>
    <?php
    $this->assign('list_type', 'version');
    $this->assign('rows', $proj->listVersions(true));
    $this->display('common.list.tpl');
    ?>
  </fieldset>
</div>
