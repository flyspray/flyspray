<h3><?php echo Filters::noXSS(L('editmydetails')); ?></h3>
<div class="box">
<?php $this->display('common.profile.tpl'); ?>
</div><div class="box"><?php echo tpl_draw_perms($user->perms); ?></div>
