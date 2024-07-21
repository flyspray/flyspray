<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?php echo Filters::noXSS(L('admintoolboxlong')); ?> :: <?php echo Filters::noXSS(L('edituser')); ?> : <?php echo Filters::noXSS($theuser->infos['user_name']); ?></h2>

	<?php $this->display('common.profile.tpl'); ?>
</div>
