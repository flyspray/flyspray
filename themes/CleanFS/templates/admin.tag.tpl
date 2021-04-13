<div id="toolbox">
<h3><?= eL('tags') ?></h3>
<p>Some tag settings are configured in the preferences area of each project.</p>
<p>There are still some usability improvements on the TODO list.</p>
<p>Please see <a href="https://bugs.flyspray.org/2012" target="_blank">bugs.flyspray.org/2012</a> for status of <b>Tags</b> feature.</p>
<?php
  $this->assign('list_type', 'tag');
  $this->assign('rows', $proj->listTags(true));
  $this->display('common.list.tpl');
?>
</div>
