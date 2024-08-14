<script>
function toggleCheckbox(id)
{
	var el = document.getElementById(id);
	if (el != null) {
		if (el.checked) {
			el.checked = false;
		} else {
			el.checked = true;
		}
	}
}
</script>
<form action="<?php echo Filters::noXSS(createURL($do, 'editallusers'));?>" method="get">
	<fieldset>
		<legend><?= eL('accountfilter') ?></legend>

		<ul class="form_elements">
			<li>
				<label><?= eL('columns'); ?></label>
				<div class="valuewrap">
					<div class="checksetwrap">
<?php
$fields = [
	[
		'stats',
		'stats',
		eL('statistics'),
		($showstats == 1)
	],
	[
		'ltf',
		'ltf',
		el('langtzdateformat'),
		($showltf == 1)
	],
];

foreach ($fields as $f) {
?>
						<div class="checkwrap">
 							<?php echo tpl_checkbox('showfields[]', ($f[3] ? ' checked="checked"' : ''), 'showfields_' . $f[0], $f[1]); ?>
							<label for="<?php echo 'showfields_' . $f[0]; ?>"><?php echo $f[2]; ?></lable>
						</div>
<?php
}
?>
					</div>
					<div class="note"><?= el('noteuserstatistics') ?></div>
				</div>
			</li>
			<li>
				<label for="usersearchtext" style=""><?= eL('name') ?>:</label>
				<div class="valuewrap">
					<input type="text" name="namesearch" class="fi-large" value="<?php echo empty($namesearch) ? '' : Filters::noXSS($namesearch); ?>" id="usersearchtext" placeholder="search user">
				</div>
			</li>
			<li>
				<label for="mailsearchtext" style=""><?= eL('email') ?>:</label>
				<div class="valuewrap">
					<input type="text" name="mailsearch" class="fi-large" value="<?php echo empty($mailsearch) ? '' : Filters::noXSS($mailsearch); ?>" id="mailsearchtext" placeholder="search mail address">
				</div>
			</li>
			<li>
				<label><?= eL('accountstatus') ?></label>
				<div class="valuewrap">
					<div class="btn-group">
						<input type="radio" id="status_all" name="status" value=""<?= Get::val('status')=='' ? ' checked="checked"':'' ?>>
						<label class="userstatus" id="s_all" for="status_all"><?= eL('showaccountsall') ?></label>

						<input type="radio" id="status_enabled" name="status" value="1"<?= Get::val('status')==='1' ? ' checked="checked"':'' ?>>
						<label class="userstatus" id="s_enabled" for="status_enabled"><?= eL('showaccountsenabled') ?></label>

						<input type="radio" id="status_disabled" name="status" value="0"<?= Get::val('status')==='0' ? ' checked="checked"':'' ?>>
						<label class="userstatus" id="s_disabled" for="status_disabled"><?= eL('showaccountsdisabled') ?></label>
					</div>
				</div>
			</li>
		</ul>

		<div class="buttons">
			<input type="hidden" name="do" value="admin" />
			<input type="hidden" name="area" value="editallusers" />
			<button type="submit"><?= eL('search') ?></button>
		</div>
	</fieldset>
</form>

<?php if ($usercount): ?>
<div class="pagination">
	<p><?php echo sprintf('Showing Users %d - %d of %d', $offset + 1, ($offset + $perpage > $usercount ? $usercount : $offset + $perpage), $usercount); ?></p>

	<?php echo pagenums($pagenum, $perpage, $usercount, 'admin', 'editallusers'); ?>
</div>
<?php
if ($do == 'admin'): echo tpl_form(Filters::noXSS(createURL($do, 'editallusers')), null, null, null, 'id="editallusers"');
			   else: echo tpl_form(Filters::noXSS($_SERVER['SCRIPT_NAME']), null, null, null, 'id="editallusers"');
endif;
if ($do == 'admin'): ?>
	<input type="hidden" name="action" value="admin.editallusers" />
	<input type="hidden" name="do" value="admin" />
	<input type="hidden" name="area" value="editallusers" />
<?php endif; ?>
<table class="bulkedituser userlist">
	<thead>
	<tr class="account_header">
		<th class="ttcolumn"></th>
		<th class="actions"><span class="fas fa-pencil fa-lg"></span></th>
<!-- 		<th class="accountenabled"><?= eL('accountenabled') ?></span></th> -->
		<th class="realname"><?= tpl_userlistheading('realname') ?></th>
		<th class="username"><?= tpl_userlistheading('username') ?></th>
		<th class="emailaddress"><?= tpl_userlistheading('emailaddress') ?></th>
		<th class="jabberid"><?= tpl_userlistheading('jabberid') ?></th>
		<th class="regdate"><?= tpl_userlistheading('regdate') ?></th>
		<th class="lastlogin<?= (($showstats || $showltf) ? ' divider' : '') ?>"><?= tpl_userlistheading('lastlogin') ?></th>
<?php if($showstats): ?>
		<th class="tasksopenedby"><?= eL('openedby') ?></th>
		<th class="tasksclosedby"><?= eL('closedby') ?></th>
		<th class="taskseditedby"><?= eL('editedby') ?></th>
		<th class="tasksassignedto"><?= eL('assignedto') ?></th>
		<th class="commentscount"><?= eL('comments') ?></th>
		<th class="votescount<?= ($showltf ? ' divider' : '') ?>"><?= eL('votes') ?></th>
<?php endif; ?>
<?php if($showltf): ?>
		<th class="language"><?= eL('language') ?></th>
		<th class="timezone"><?= eL('timezone') ?></th>
		<th class="dateformat"><?= eL('dateformat') ?></th>
		<th class="dateformatext"><?= eL('dateformat_extended') ?></th>
<?php endif; ?>
	</tr>
	</thead>
	<tbody>
<?php foreach ($users as $usr): ?>
<tr class="<?php echo ($usr['account_enabled']) ? 'account_enabled':'account_disabled'; ?>" onclick="toggleCheckbox('<?php echo $usr['user_id']; ?>')">
	<td class="ttcolumn">
		<input id="<?php echo $usr['user_id'] ?>" onclick="event.stopPropagation()" type="checkbox" name="checkedUsers[]" value="<?php echo $usr['user_id']; ?>">
	</td>
	<td class="user_actions">
		<a href="<?= createURL('edituser', $usr['user_id']) ?>" class="button" title="Edit user <?= Filters::noXSS($usr['real_name']) ?>"><span class="fas fa-pencil"></span></a>
	</td>
<!--	<td class="user_accountenabled">
		<span class="fas fa-<?php echo ($usr['account_enabled']) ? 'check':'ban'; ?>"></span>
	</td> -->
	<td class="user_realname">
		<a href="<?= createURL('user', $usr['user_id']) ?>" title="View user <?= Filters::noXSS($usr['real_name']).' ('.$usr['user_name'].')' ?>"><?= Filters::noXSS($usr['real_name']) ?></a>
	</td>
	<td class="user_username">
		<a href="<?= createURL('user', $usr['user_id']) ?>" title="View user <?= Filters::noXSS($usr['real_name']).' ('.$usr['user_name'].')' ?>"><?= $usr['user_name'] ?></a>
	</td>
	<td class="user_emailaddress<?= ($usr['notify_type']==0 || $usr['notify_type']==2) ? ' inactive':''; ?>">
		<?php echo Filters::noXSS($usr['email_address']); ?>
	</td>
	<td class="user_jabberid<?= ($usr['notify_type']==0 || $usr['notify_type']==1) ? ' inactive':''; ?>">
		<?php echo Filters::noXSS($usr['jabber_id']); ?>
	</td>
	<td class="user_regdate">
		<?php echo formatDate($usr['register_date']); ?>
	</td>
	<td class="user_lastlogin<?= (($showstats || $showltf) ? ' divider' : '') ?>">
		<?php echo formatDate($usr['last_login']); ?>
	</td>
<?php if($showstats): ?>
	<td class="user_tasksopenedby">
		<?php echo $usr['countopen']>0 ? $usr['countopen']:''; ?>
	</td>
	<td class="user_tasksclosedby">
		<?php echo $usr['countclose']>0 ? $usr['countclose']:''; ?>
	</td>
	<td class="user_taskseditedby">
		<?php echo $usr['countlastedit']>0 ? $usr['countlastedit']:''; ?>
	</td>
	<td class="user_tasksassignedto">
		<?php echo $usr['countassign']>0 ? $usr['countassign']:''; ?>
	</td>
	<td class="user_commentscount">
		<?php echo $usr['countcomments']>0 ? $usr['countcomments']:''; ?>
	</td>
	<td class="user_votescount<?= ($showltf ? ' divider' : '') ?>">
		<?php echo $usr['countvotes']>0 ? $usr['countvotes']:''; ?>
	</td>
<?php endif; ?>
<?php if($showltf): ?>
	<td class="user_language">
		<?php echo Filters::noXSS($usr['lang_code']); ?>
	</td>
	<td class="user_timezone">
		<?php echo Filters::noXSS($usr['time_zone']); ?>
	</td>
	<td class="user_dateformat">
		<?php echo Filters::noXSS($usr['dateformat']); ?>
	</td>
	<td class="user_dateformatext">
		<?php echo Filters::noXSS($usr['dateformat_extended']); ?>
	</td>
<?php endif; ?>
	</tr>
<?php endforeach; ?>
	</tbody>
</table>

<button type="submit" id="buSubmit" name="enable"><?= eL('enableaccounts') ?></button>
<button type="submit" id="buSubmit" name="disable"><?= eL('disableaccounts') ?></button>
<button type="submit" id="buSubmit" name="delete"><?= eL('deleteaccounts') ?></button>

<!-- TODO Should still add these to bulk edit, but hasn't been done yet
<ul class="form_elements">
	<li class="required">
		<label for="notify_type"><?= eL('notifications') ?></label>
		<select id="notify_type" name="notify_type">
		<?php echo tpl_options($fs->getNotificationOptions(), Req::val('notify_type')); ?>
		</select>
	</li>
	<li>
		<label for="time_zone"><?= eL('timezone') ?></label>
		<select id="time_zone" name="time_zone">
		<?php
			$times = array();
			for ($i = -12; $i <= 13; $i++) {
				$times[$i] = L('GMT') . (($i == 0) ? ' ' : (($i > 0) ? '+' . $i : $i));
			}
			?>
		<?php echo tpl_options($times, Req::val('time_zone', 0)); ?>
		</select>
	</li>

	<?php if (isset($groups)): ?>
	<li>
		<label for="groupin"><?= eL('globalgroup') ?></label>
		<select id="groupin" class="adminlist" name="group_in">
		<?php echo tpl_options($groups, Req::val('group_in')); ?>
		</select>
	</li>
	<?php endif; ?>
</ul>
<p><button type="submit" id="buSubmit"><?= eL('updateaccounts') ?></button></p>
-->
</form>
<div class="pagination">
	<p><?php echo sprintf('Showing Users %d - %d of %d', $offset + 1, ($offset + $perpage > $usercount ? $usercount : $offset + $perpage), $usercount); ?></p>
	<?php echo pagenums($pagenum, $perpage, $usercount, 'admin', 'editallusers'); ?>
</div>
<?php else: ?>
	<div class="noresult"><strong><?= eL('noresults') ?></strong></div>
<?php endif; ?>
