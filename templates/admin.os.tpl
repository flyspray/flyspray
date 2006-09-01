<div id="toolbox">
  <h3>{L('admintoolboxlong')} :: {L('oslist')}</h3>

  <fieldset class="box">
    <legend>{L('operatingsystems')}</legend>
    <?php
    $this->assign('list_type', 'os');
    $this->assign('rows', $proj->listOs(true));
    $this->display('common.list.tpl');
    ?>
  </fieldset>
</div>
