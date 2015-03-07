<div class="box"><h3><?php echo Filters::noXSS(L('editmydetails')); ?></h3>
<?php $this->display('common.profile.tpl'); ?>
</div><div class="box"><h3><?php echo eL('permissionsforproject').' '.$proj->prefs['project_title']; ?></h3><?php echo tpl_draw_perms($user->perms); ?></div>
