<?php $activeclass = ' class="active" '; ?>

<div id="toolboxmenu">
  <a id="projprefslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'prefs') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'prefs',      $proj->id)); ?>"><span class="fas fa-sliders"></span><span><?php echo Filters::noXSS(L('preferences')); ?></span></a>
  <a id="projuglink"
     <?php if(isset($_GET['area']) and ($_GET['area'] == 'groups' || $_GET['area'] == 'newgroup' || $_GET['area'] == 'editgroup') ) echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'groups',     $proj->id)); ?>"><span class="fas fa-users"></span><span><?php echo Filters::noXSS(L('usergroups')); ?></span></a>
  <a id="projcatlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'cat') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'cat',        $proj->id)); ?>"><span class="fas fa-folder-tree"></span><span><?php echo Filters::noXSS(L('categories')); ?></span></a>
  <a id="projttlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'tasktype') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'tasktype', $proj->id)); ?>"><span class="fas fa-bug"></span><span><?php echo Filters::noXSS(L('tasktypes')); ?></span></a>
  <a id="projtglink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'tag') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'tag',  $proj->id)); ?>"><span class="fas fa-tag"></span><span><?php echo Filters::noXSS(L('tags')); ?></span></a>
  <a id="projstatuslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'status') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'status',     $proj->id)); ?>"><span class="fas fa-crosshairs"></span><span><?php echo Filters::noXSS(L('taskstatuses')); ?></span></a>
  <a id="projreslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'resolution') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'resolution',        $proj->id)); ?>"><span class="fas fa-check-to-slot"></span><span><?php echo Filters::noXSS(L('resolutions')); ?></span></a>
   <a id="projverlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'version') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'version',        $proj->id)); ?>"><span class="fas fa-code-branch"></span><span><?php echo Filters::noXSS(L('versions')); ?></span></a>
 <a id="projoslink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'os') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'os',         $proj->id)); ?>"><span class="fas fa-computer"></span><span><?php echo Filters::noXSS(L('operatingsystems')); ?></span></a>
  <a id="projreqlink"
     <?php if(isset($_GET['area']) and $_GET['area'] == 'pendingreq') echo $activeclass; ?>
     href="<?php echo Filters::noXSS(CreateURL('pm', 'pendingreq', $proj->id)); ?>"><span class="fas fa-hand-point-up"></span><span><?php echo Filters::noXSS(L('pendingrequests')); ?></span></a>
</div>
