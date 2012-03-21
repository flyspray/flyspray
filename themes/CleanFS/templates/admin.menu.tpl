<?php $activeclass = ' class="active" '; ?>

<div id="toolboxmenu">
  <a id="globprefslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'prefs') echo $activeclass; ?>
     href="{CreateURL('admin', 'prefs')}">{L('preferences')}</a>
  <a id="globuglink"
     <?php if(isset($_GET['area']) and in_array($_GET['area'], array('groups','newuser','newgroup','editgroup', 'users'))) echo $activeclass; ?>
     href="{CreateURL('admin', 'groups')}">{L('usersandgroups')}</a>
  <a id="globttlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'tasktype') echo $activeclass; ?>
     href="{CreateURL('admin', 'tasktype')}">{L('tasktypes') }</a>
  <a id="globstatuslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'status') echo $activeclass; ?>
     href="{CreateURL('admin', 'status')}">{L('taskstatuses') }</a>
  <a id="globreslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'resolution') echo $activeclass; ?>
     href="{CreateURL('admin', 'resolution')}">{L('resolutions') }</a>
  <a id="globcatlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'cat') echo $activeclass; ?>
     href="{CreateURL('admin', 'cat')}">{L('categories') }</a>
  <a id="globoslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'os') echo $activeclass; ?>
     href="{CreateURL('admin', 'os')}">{L('operatingsystems')}</a>
  <a id="globverlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'version') echo $activeclass; ?>
     href="{CreateURL('admin', 'version')}">{L('versions') }</a>
  <a id="globnewprojlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'newproject') echo $activeclass; ?>
     href="{CreateURL('admin', 'newproject')}">{L('newproject')}</a>
</div>
