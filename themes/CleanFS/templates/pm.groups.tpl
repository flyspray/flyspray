<div id="toolbox" class="toolbox_<?php echo $area; ?>">
	<h2><?php echo Filters::noXSS($proj->prefs['project_title']); ?> : <?= eL('groupmanage') ?></h2>

	<div>
	<?php if ($user->perms('is_admin')): ?>
		<a class="button" href="<?php echo createURL('admin', 'newuser', $proj->id); ?>"><span class="fas fa-user-plus fa-lg fa-fw"></span> <?= eL('newuser') ?></a>
	<?php endif; ?>
		<a class="button" href="<?php echo Filters::noXSS(createURL('pm', 'newgroup', $proj->id)); ?>"><span class="fas fa-user-group fa-lg fa-fw"></span><?= eL('newgroup') ?></a>
		<form style="display:inline-block" action="<?php echo Filters::noXSS($baseurl); ?>index.php" method="get">
			<label for="edit_user"><?= eL('edituser') ?></label>
			<?php echo tpl_userselect('user_name', '', 'edit_user'); ?>
			<button type="submit"><?= eL('edit') ?></button>
			<input type="hidden" name="do" value="user" />
			<input type="hidden" name="project" value="<?php echo $proj->id; ?>" />
		</form>
	</div>
<?php
// 'group_open 'is not relevant for project groups, so lets not add it here.
$perm_fields = array(
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

$basic_fields_labels = [
	'item_summary' => 'summary',
	'detailed_desc' => 'taskdetails',
	'task_type' => 'tasktype',
	'product_category' => 'category',
	'operating_system' => 'operatingsystem',
	'task_severity' => 'severity',
	'percent_complete' => 'percentcomplete',
	'product_version' => 'productversion',
	'estimated_effort' => 'estimatedeffort'
];

require_once 'permicons.tpl';

$merge = array_merge($groups, $globalgroups);

foreach ($merge as $group) {
?>
	<div class="box groupinfo g<?= $group['group_id'] . ($group['project_id'] == 0 ? ' globalgroup' : '') . ($group['group_open'] == 0 ? ' inactive' : '') ?>">
		<h3>
			<?= Filters::noXSS($group['group_name']) ?> (<?= $group['users'] ?></strong> <?= eL('members') ?>)
			<span class="fas fa-globe" title="<?= eL($group['project_id'] == 0 ? 'globalgroup' : 'projectgroup') ?>"></span>
			<span class="fas fa-toggle-<?= ($group['group_open'] == 0 ? 'off' : 'on') ?>" title="<?= eL($group['group_open'] == 0 ? 'inactive' : 'active') ?>"></span>
		</h3>

<?php if($group['project_id'] != 0): ?>
		<p>
			<a class="button" href="<?= Filters::noXSS(createURL('editgroup', $group['group_id'], 'pm')) ?>">
			<?= eL('editgroup') ?>
			<span class="fas fa-pencil fa-lg fa-fw"></span></a>
		</p>
<?php endif; ?>

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
			<div class="perm_item perm-<?= ($granted ? 'yes' : 'no') ?>">
<?php
// TODO: make it visible that a granted 'view_tasks' overrules 'view_groups_tasks' and 'own_tasks'. (like is_admin)
?>
				<div class="perm_icon">
					<?= $permicons[$p] ?>
				</div>

				<div class="perm_info">
					<p><?php echo eL(str_replace('_', '', $p)); ?></p>
<?php if ($admin_grant): ?>
					<p class="perm_note">Granted via <span><?= eL('isadmin') ?><span></p>
<?php elseif ($pm_grant): ?>
					<p class="perm_note">Granted via <span><?= eL('manageproject') ?></span></p>
<?php elseif ($group['project_id'] != 0 && $p == 'modify_own_tasks' && $granted): ?>
					<p class="perm_note"><a class="modify_own_tasks" onclick="showhidestuff('modify_own_tasks_g<?= $group['group_id'] ?>')"><?= eL('fieldsallowedtochange') ?></a></p>
					<ul id="modify_own_tasks_g<?= $group['group_id'] ?>">
<?php foreach ($proj->prefs['basic_fields'] as $motf): ?>
						<li><?= eL($basic_fields_labels[$motf]) ?></li>
<?php endforeach; ?>
					</ul>
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
