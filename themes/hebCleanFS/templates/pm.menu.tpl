<?php $activeclass = ' class="active" '; ?>

<div id="toolboxmenu">
  <a id="projprefslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'prefs') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'prefs',      $proj->id)); ?>"><?php echo Filters::noXSS(L('preferences')); ?></a>
  <a id="projuglink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'groups') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'groups',     $proj->id)); ?>"><?php echo Filters::noXSS(L('usergroups')); ?></a>
  <a id="projttlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'tasktype') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'tasktype',         $proj->id)); ?>"><?php echo Filters::noXSS(L('tasktypes')); ?></a>
  <a id="projstatuslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'status') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'status',     $proj->id)); ?>"><?php echo Filters::noXSS(L('taskstatuses')); ?></a>
  <a id="projreslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'resolution') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'resolution',        $proj->id)); ?>"><?php echo Filters::noXSS(L('resolutions')); ?></a>
  <a id="projcatlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'cat') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'cat',        $proj->id)); ?>"><?php echo Filters::noXSS(L('categories')); ?></a>
  <a id="projoslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'os') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'os',         $proj->id)); ?>"><?php echo Filters::noXSS(L('operatingsystems')); ?></a>
  <a id="projverlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'version') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'version',        $proj->id)); ?>"><?php echo Filters::noXSS(L('versions')); ?></a>
  <a id="projreqlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'pendingreq') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'pendingreq', $proj->id)); ?>"><?php echo Filters::noXSS(L('pendingrequests')); ?></a>
</div>
