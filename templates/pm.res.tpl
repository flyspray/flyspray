<div id="toolbox">
  <h3>{L('pmtoolbox')} :: {$proj->prefs['project_title']} : {L('resed')}</h3>

  <fieldset class="box">
    <legend>{L('resolutions')}</legend>
    <?php
    $this->assign('list_type', 'resolution');
    $this->assign('rows', $proj->listResolutions(true));
    $this->display('common.list.tpl');
    ?>
  </fieldset>
</div>
