    // add DC 19/02/2015
    //
<div id="toolbox">
  <h3><?php echo Filters::noXSS(L('tasktypes')); ?></h3>
  <?php
  $this->assign('list_type', 'lists');
  $this->assign('rows', $proj->listLists($proj->id));
  $this->display('common.lists.tpl');
  ?>
</div>
