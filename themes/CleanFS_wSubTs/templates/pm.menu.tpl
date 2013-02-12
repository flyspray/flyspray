<?php $activeclass = ' class="active" '; ?>

<div id="toolboxmenu">
  <a id="projprefslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'prefs') echo $activeclass; ?>
     href="{CreateURL('pm', 'prefs',      $proj->id)}">{L('preferences')}</a>
  <a id="projuglink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'groups') echo $activeclass; ?>
     href="{CreateURL('pm', 'groups',     $proj->id)}">{L('usergroups')}</a>
  <a id="projttlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'tasktype') echo $activeclass; ?>
     href="{CreateURL('pm', 'tasktype',         $proj->id)}">{L('tasktypes')}</a>
  <a id="projstatuslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'status') echo $activeclass; ?>
     href="{CreateURL('pm', 'status',     $proj->id)}">{L('taskstatuses')}</a>
  <a id="projreslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'resolution') echo $activeclass; ?>
     href="{CreateURL('pm', 'resolution',        $proj->id)}">{L('resolutions')}</a>
  <a id="projcatlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'cat') echo $activeclass; ?>
     href="{CreateURL('pm', 'cat',        $proj->id)}">{L('categories')}</a>
  <a id="projoslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'os') echo $activeclass; ?>
     href="{CreateURL('pm', 'os',         $proj->id)}">{L('operatingsystems')}</a>
  <a id="projverlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'version') echo $activeclass; ?>
     href="{CreateURL('pm', 'version',        $proj->id)}">{L('versions')}</a>
  <a id="projreqlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'pendingreq') echo $activeclass; ?>
     href="{CreateURL('pm', 'pendingreq', $proj->id)}">{L('pendingrequests')}</a>
</div>
