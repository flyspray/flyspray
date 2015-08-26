<div id="toolbox">
  <h3><?php echo Filters::noXSS(L('tags')); ?></h3>
  <p>Tag management is in development.</p>
  <p>Please see <a href="https://bugs.flyspray.org/2012" target="_blank">bugs.flyspray.org/2012</a> for status of <b>Tags</b> feature.</p>
<?php
$this->assign('list_type', 'tag');
$this->assign('rows', $proj->listTags(true));
  
$systemwide = new Project(0);
$this->assign('sysrows', $systemwide->listTags(true));

$this->display('common.list.tpl');
?>
</div>
