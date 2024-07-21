<div class="box">
	<h2><?= $user->infos['user_name'] ?> (<?php echo $user->infos['real_name']; ?>)</h2>

	<p>Last login: <?php echo formatDate($user->infos['last_login'], $user->infos['dateformat_extended']); ?></p>
</div>

<ul id="submenu">
	<li id="dashboardtab"><a href="#dashboard"><span class="fas fa-gauge"></span><span>Dashboard</span></a></li>
	<li id="editprofiletab"><a href="#editprofile"><span class="fas fa-user-pen"></span><span><?= eL('editmydetails') ?></span></a></li>
	<li id="permissionstab"><a href="#permissions"><span class="fas fa-key"></span><span><?php echo eL('permissionsforproject').' '.$proj->prefs['project_title']; ?></span></a></li>
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
				<th><?= eL('project') ?></th>
				<th><?= eL('task') ?></th>
				<th><?= eL('removevote') ?></th>
			</tr>
			</thead>
			<tbody>
	<?php foreach($votes as $vote): ?>
			<tr<?php echo $vote['is_closed'] ? ' class="closed"':''; ?>>
				<td><?= Filters::noXSS($vote['project_title']) ?></td>
				<td class="task_summary"><?= tpl_tasklink($vote) ?></td>
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
				<th><?= eL('project') ?></th>
				<th><?= eL('task') ?></th>
				<th><?= eL('remindinterval') ?></th>
				<th><?= eL('last_sent') ?></th>
			</tr>
			</thead>
			<tbody>
<?php foreach($myreminders as $reminder): ?>
			<tr<?php echo $reminder['is_closed'] ? ' class="closed"':''; ?>>
				<td><?= Filters::noXSS($reminder['project_title']) ?></td>
				<td class="task_summary"><?= tpl_tasklink($reminder) ?></td>
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
	<div class="permissions"><?php echo tpl_draw_perms($user->perms); ?></div>
</div>

<div id="history" class="tab">
	<div class="history">activity history</div>
</div>
