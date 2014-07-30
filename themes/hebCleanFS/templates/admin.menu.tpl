<?php $activeclass = ' class="active" '; ?>

<div id="toolboxmenu">
  <a id="globprefslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'prefs') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'prefs')); ?>"><?php echo Filters::noXSS(L('preferences')); ?></a>
  <a id="globuglink"
     <?php if(isset($_GET['area']) and in_array($_GET['area'], array('groups','newuser','newgroup','editgroup', 'users'))) echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'groups')); ?>"><?php echo Filters::noXSS(L('usersandgroups')); ?></a>
  <a id="globttlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'tasktype') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'tasktype')); ?>"><?php echo Filters::noXSS(L('tasktypes') ); ?></a>
  <a id="globstatuslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'status') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'status')); ?>"><?php echo Filters::noXSS(L('taskstatuses') ); ?></a>
  <a id="globreslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'resolution') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'resolution')); ?>"><?php echo Filters::noXSS(L('resolutions') ); ?></a>
  <a id="globcatlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'cat') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'cat')); ?>"><?php echo Filters::noXSS(L('categories') ); ?></a>
  <a id="globoslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'os') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'os')); ?>"><?php echo Filters::noXSS(L('operatingsystems')); ?></a>
  <a id="globverlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'version') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'version')); ?>"><?php echo Filters::noXSS(L('versions') ); ?></a>
  <a id="globnewprojlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'newproject') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'newproject')); ?>"><?php echo Filters::noXSS(L('newproject')); ?></a>
</div>
