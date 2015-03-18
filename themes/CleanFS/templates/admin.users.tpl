<div id="toolbox">
  <h3><?php echo Filters::noXSS(L('admintoolboxlong')); ?> :: <?php echo Filters::noXSS(L('edituser')); ?> : <?php echo Filters::noXSS($theuser->infos['user_name']); ?></h3>
  <fieldset><legend><?php echo Filters::noXSS(L('edituser')); ?></legend>
  <?php $this->display('common.profile.tpl'); ?>
</div>
