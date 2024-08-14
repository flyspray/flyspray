<div class="box">
	<h2><?= $theuser->infos['user_name'] ?> (<?php echo $theuser->infos['real_name']; ?>)</h2>

	<p>Last login: <?php echo formatDate($theuser->infos['last_login'], $theuser->infos['dateformat_extended']); ?></p>
</div>

<ul id="submenu">
	<li id="dashboardtab"><a href="#dashboard"><span class="fas fa-gauge"></span><span>Dashboard</span></a></li>
	<li id="editprofiletab"><a href="#editprofile"><span class="fas fa-user-pen"></span><span><?= eL('editmydetails') ?></span></a></li>
	<li id="permissionstab"><a href="#permissions"><span class="fas fa-key"></span><span><?php echo eL('permissions'); ?></span></a></li>
	<li id="historytab"><a href="#history"><span class="fas fa-timeline"></span><span><?php echo eL('history'); ?></span></a></li>
</ul>

<div id="editprofile" class="tab">
	<?php $this->display('common.profile.tpl'); ?>
</div>

<div id="dashboard" class="tab">
	<div class="dashboardwrapper">
		<div class="box">
			<h3><?= eL('myvotes') ?></h3>

<?php if(count($votes) > 0): ?>
			<table id="myvotes">
			<thead>
			<tr>
				<th><?= eL('task') ?></th>
				<th></th>
			</tr>
			</thead>
			<tbody>
	<?php foreach($votes as $vote): ?>
			<tr<?php echo $vote['is_closed'] ? ' class="closed"':''; ?>>
				<td class="task_summary">
					<div class="otherprojtitle"><?php echo $vote['project_title'] ?></div>
					<?= tpl_tasklink($vote) ?>
				</td>
				<td>
					<?php echo tpl_form(Filters::noXSS(createURL('myprofile', $vote['task_id']))); ?>
					<input type="hidden" name="action" value="removevote" />
					<input type="hidden" name="task_id" value="<?php echo $vote['task_id'] ?>" />
					<button type="submit" title="<?= eL('removevote') ?>"><span class="fas fa-trash-can"></span></button>
					</form>
				</td>
			</tr>
	<?php endforeach; ?>
			</tbody>
			</table>
<?php else: ?>
			<p><?php echo eL('novotes'); ?></p>
<?php endif; ?>
		</div>

		<div class="box">
			<h3><?= eL('myreminders') ?></h3>

<?php if(count($myreminders) > 0): ?>
			<table id="myreminders">
			<thead>
			<tr>
				<th><?= eL('task') ?></th>
				<th><?= eL('remindinterval') ?></th>
				<th><?= eL('last_sent') ?></th>
			</tr>
			</thead>
			<tbody>
<?php foreach($myreminders as $reminder): ?>
			<tr<?php echo $reminder['is_closed'] ? ' class="closed"':''; ?>>
				<td class="task_summary">
					<div class="otherprojtitle"><?= Filters::noXSS($reminder['project_title']) ?></div>
					<?= tpl_tasklink($reminder) ?>
				</td>
				<td><?= ($reminder['how_often']/3600) ?>h</td>
				<td><?= $reminder['last_sent'] ?></td>
			</tr>
<?php endforeach; ?>
			</tbody>
			</table>
<?php else: ?>
			<p><?php echo eL('noreminders'); ?></p>
<?php endif; ?>
		</div>
	</div>
</div>

<div id="permissions" class="tab">
<?php

// TODO: display group memberships

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

foreach ($projects as $project) {
?>
	<div class="box projectperms p<?= $project->id . ($project->id == 0 ? ' globalproject' : '') . ($project->prefs['project_is_active'] == 0 ? ' inactive' : '') ?>">
		<h3>
			<?= ($project->id == 0 ? eL('globalpermissions') : Filters::noXSS($project->prefs['project_title'])) ?> <?php  /*(TODO: contributors)*/ ?>
			<span class="fas fa-globe" title="<?= eL($project->id == 0 ? 'globalproject' : '') ?>"></span>
			<span class="fas fa-toggle-<?= ($project->prefs['project_is_active'] == 0 ? 'off' : 'on') ?>" title="<?= eL($project->prefs['project_is_active'] == 0 ? 'inactive' : 'active') ?>"></span>
		</h3>
<?php
	unset($perm_tmp, $project_perms);

	foreach ($theuser->perms as $perm_tmp) {
		if ($perm_tmp['project_id'] == $project->id) {
			$project_perms = $perm_tmp;

			break;
		}
	}
?>
		<h4><?= eL('permissions') ?></h4>

		<div class="perms">

<?php
	foreach ($perm_fields as $p):
		$direct_grant = ($project_perms[$p] == 1);
		$admin_grant = false;
		$pm_grant = false;

		$admin_grant = ($p != 'is_admin' && $project_perms['is_admin'] == 1 && $project_perms[$p] == 0);
		$pm_grant = (($p != 'is_admin' && $p != 'manage_project') && $project_perms[$p] == 0 && $project_perms['manage_project'] == 1);

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
					<p><?php ($direct_grant ? 'Y' : 'N').($admin_grant ? 'Y' : 'N' ).($pm_grant ? 'Y' : 'N' ) ?> <?php echo eL(str_replace('_', '', $p)); ?></p>
<?php if ($admin_grant): ?>
					<p class="perm_note">Granted via <span><?= eL('isadmin') ?><span></p>
<?php elseif ($pm_grant): ?>
					<p class="perm_note">Granted via <span><?= eL('manageproject') ?></span></p>
<?php elseif ($project->id != 0 && $p == 'modify_own_tasks' && $granted): ?>
					<p class="perm_note"><a class="modify_own_tasks" onclick="showhidestuff('modify_own_tasks_p<?= $project->id ?>')"><?= eL('fieldsallowedtochange') ?></a></p>
					<ul id="modify_own_tasks_p<?= $project->id ?>">
<?php
 foreach ($project->prefs['basic_fields'] as $motf): ?>
						<li><?= eL($basic_fields_labels[$motf]) ?></li>
<?php endforeach;

?>
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

<div id="history" class="tab">
	<div class="history">activity history</div>
</div>
