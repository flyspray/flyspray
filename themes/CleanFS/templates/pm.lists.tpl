<!-- add DC 19/02/2015 -->
<div id="toolbox">
  <h3><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?php echo Filters::noXSS(L('customsfielded')); ?></h3>
  <?php
  $this->assign ('list_type', 'lists');//var asset
  $this->assign ('rows', $proj->listLists(true));//PUT RESULT IN rows type as array
  $this->display('common.lists.tpl');//template
  ?>
</div>