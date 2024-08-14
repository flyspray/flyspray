<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<ul id="submenu">
		<li><a href="#users_tab"><?= eL('users') ?></a></li>
		<li><a href="#groups_tab"><?= eL('globalgroups') ?></a></li>
	</ul>

	<div id="users_tab" class="tab">
		<div class="buttons">
			<a class="button" href="<?php echo Filters::noXSS(createURL('admin', 'newuser', $proj->id)); ?>"><span class="good fas fa-user-plus fa-lg fa-fw"></span><?= eL('newuser') ?></a>
			<a class="button" href="<?php echo Filters::noXSS(createURL('admin', 'newuserbulk', $proj->id)); ?>"><span class="good fas fa-user-xmark fa-lg fa-fw"></span><?= eL('newuserbulk') ?></a>
			<a class="button" href="<?php echo Filters::noXSS(createURL('admin', 'editallusers', $proj->id)); ?>"><span class="fas fa-user-group fa-lg fa-fw"></span><?= eL('editallusers') ?></a>
		</div>

		<div class="groupedit">
			<form action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
				<ul class="form_elements">
					<li>
						<label for="edit_user"><?= eL('edituser') ?></label>
						<div class="valuewrap">
							<div class="valuemulti">
								<?php echo tpl_userselect('user_name', '', 'edit_user'); ?>
								<button type="submit"><?= eL('edit') ?></button>
								<input type="hidden" name="do" value="admin" />
								<input type="hidden" name="area" value="users" />
								<input type="hidden" name="project" value="<?php echo $proj->id; ?>" />
							</div>
						</div>
					</li>
				</ul>
			</form>
		</div>
	</div>
	<div id="groups_tab" class="tab">
		<div class="buttons">
			<a class="button" href="<?php echo Filters::noXSS(createURL('admin', 'newgroup', $proj->id)); ?>"><span class="fas fa-user-group fa-lg fa-fw"></span><?= eL('newgroup') ?></a>
		</div>

<?php
$perm_fields = array(
	'group_open',
	'is_admin',
	'manage_project',
	'view_tasks',
	'view_groups_tasks', // TODO: What is the definition of "group's task" and how does it effect project views?
	'view_own_tasks',    // TODO: What is the definition of "own task" and how does it effect project views?
	'open_new_tasks',
	'add_multiple_tasks',
	'modify_own_tasks',
	'modify_all_tasks',
	'create_attachments',
	'delete_attachments',
	'assign_to_self',
	'assign_others_to_self',
	'edit_assignments',
	'close_own_tasks',
	'close_other_tasks',
	'view_roadmap',
	'view_history',
	'view_reports',
	'add_votes',
	'view_comments',
	'add_comments',
	'edit_comments',
	'edit_own_comments',
	'delete_comments',
	'view_estimated_effort',
	'view_current_effort_done',
	'track_effort'
);

require_once 'permicons.tpl';

foreach ($groups as $group) {
?>
		<div class="box groupinfo g<?= $group['group_id'] . ($group['group_open'] == 0 ? ' inactive' : '') ?>">
			<h3><?= Filters::noXSS($group['group_name']) ?> (<?= $group['users'] ?></strong> <?= eL('members') ?>) <span class="fas fa-toggle-<?= ($group['group_open'] == 0 ? 'off' : 'on') ?>" title="<?= eL($group['group_open'] == 0 ? 'inactive' : 'active') ?>"></span></h3>

			<div class="buttons">
				<a class="button" href="<?= Filters::noXSS(createURL('editgroup', $group['group_id'], 'admin')) ?>"><?= eL('editgroup') ?><span class="fas fa-pencil fa-lg fa-fw"></span></a>
			</div>

			<p><strong><?= eL('description') ?>:</strong> <?= Filters::noXSS($group['group_desc']) ?></p>

			<h4><?= eL('permissions') ?></h4>

			<div class="perms">
<?php
	foreach ($perm_fields as $p):
		$direct_grant = ($group[$p] == 1);
		$admin_grant = false;
		$pm_grant = false;

		$admin_grant = ($p != 'is_admin' && $group['is_admin'] == 1 && $group[$p] == 0);
		$pm_grant = (($p != 'is_admin' && $p != 'manage_project') && $group[$p] == 0 && $group['manage_project'] == 1);

		$granted = ($direct_grant || $admin_grant || $pm_grant);
?>
				<div class="perm_item perm-<?= ($granted ? 'yes' : 'no') ?>" data-perm-name="<?= $p ?>">
<?php
// TODO: make it visible that a granted 'view_tasks' overrules 'view_groups_tasks' and 'own_tasks'. (like is_admin)
?>
				<div class="perm_icon">
					<?= $permicons[$p] ?>
				</div>
				<div class="perm_info">
					<p><?php echo eL(str_replace('_', '', $p)); ?></p>
<?php if ($admin_grant): ?>
					<p class="perm_note"><?= eL('grantedvia') ?><span><?= eL('isadmin') ?></span></p>
<?php elseif ($pm_grant): ?>
					<p class="perm_note"><?= eL('grantedvia') ?> <span><?= eL('manageproject') ?></span></p>
<?php endif; ?>
				</div>
			</div>
<?php endforeach; ?>
		</div>
	</div>
<?php
}
?>
	</div>
</div>
