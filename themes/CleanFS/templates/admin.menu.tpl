<?php $activeclass = ' class="active" '; ?>

<div id="toolboxmenu">
  <a id="globprefslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'prefs') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'prefs')); ?>"><span class="fas fa-sliders"></span><span><?php echo Filters::noXSS(L('preferences')); ?></span></a>
  <a id="globuglink"
     <?php if(isset($_GET['area']) and in_array($_GET['area'], array('groups','newuser', 'newuserbulk', 'newgroup','editgroup', 'users', 'editallusers'))) echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'groups')); ?>"><span class="fas fa-users"></span><span><?php echo Filters::noXSS(L('usersandgroups')); ?></span></a>
  <a id="globttlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'tasktype') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'tasktype')); ?>"><span class="fas fa-bug"></span><span><?php echo Filters::noXSS(L('tasktypes') ); ?></span></a>
  <a id="globcatlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'cat') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'cat')); ?>"><span class="fas fa-folder-tree"></span><span><?php echo Filters::noXSS(L('categories') ); ?></span></a>
  <a id="globtglink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'tag') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'tag')); ?>"><span class="fas fa-tag"></span><span><?php echo Filters::noXSS(L('tags') ); ?></span></a>
  <a id="globstatuslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'status') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'status')); ?>"><span class="fas fa-crosshairs"></span><span><?php echo Filters::noXSS(L('taskstatuses') ); ?></span></a>
  <a id="globreslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'resolution') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'resolution')); ?>"><span class="fas fa-check-to-slot"></span><span><?php echo Filters::noXSS(L('resolutions') ); ?></span></a>
  <a id="globverlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'version') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'version')); ?>"><span class="fas fa-code-branch"></span><span><?php echo Filters::noXSS(L('versions') ); ?></span></a>
  <a id="globoslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'os') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'os')); ?>"><span class="fas fa-computer"></span><span><?php echo Filters::noXSS(L('operatingsystems')); ?></span></a>
  <a id="globnewprojlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'newproject') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'newproject')); ?>"><span class="fas fa-diagram-project"></span><span><?php echo Filters::noXSS(L('newproject')); ?></span></a>
  <a id="userrequestlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'userrequest') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'userrequest')); ?>"><span class="fas fa-hand-point-up"></span><span><?php echo Filters::noXSS(L('pendingnewuserrequest')); ?></span></a>
  <a id="translationslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'translations') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'translations')); ?>"><span class="fas fa-language"></span><span><?php echo Filters::noXSS(L('translations')); ?></span></a>
  <a id="checkslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'checks') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('admin', 'checks')); ?>"><span class="fas fa-file-waveform"></span><span><?php echo Filters::noXSS(L('adminchecks')); ?></span></a>
</div>
