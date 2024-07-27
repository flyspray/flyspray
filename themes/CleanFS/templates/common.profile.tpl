<?php echo tpl_form( $do=='myprofile' ? Filters::noXSS(createUrl('myprofile')) : Filters::noXSS(createUrl('edituser', $theuser->id))); ?>
<div id="editprofilewrap">
	<fieldset>
		<legend><?= eL('account') ?></legend>

	<ul class="form_elements">
		<li>
		<label><?= eL('username') ?></label>
		<div class="valuewrap">
			<?= $theuser->infos['user_name'] ?>
		</div>
		</li>

		<li>
		<label for="realname"><?= eL('realname') ?></label>
		<div class="valuewrap">
			<input id="realname" type="text" name="real_name" maxlength="100"
			value="<?php echo Filters::noXSS(Req::val('real_name', $theuser->infos['real_name'])); ?>" />
		</div>
		</li>

		<li>
		<label for="emailaddress"><?= eL('emailaddress') ?></label>
		<div class="valuewrap">
			<input id="emailaddress" type="text" name="email_address" maxlength="100" value="<?php echo Filters::noXSS(Req::val('email_address', $theuser->infos['email_address'])); ?>" />
		</div>
		</li>

		<li>
		<label for="hide_my_email"><?= eL('hidemyemail') ?></label>
		<div class="valuewrap">
			<?php echo tpl_checkbox('hide_my_email', Req::val('hide_my_email', !Post::val('action') && $theuser->infos['hide_my_email']), 'hide_my_email', 1, ($fs->prefs['hide_emails'] ) ? array('checked' => 'true', 'disabled' => 'true') : ''); ?>
		</div>
		</li>

		<li>
		<label for="groupin"><?= eL('globalgroup') ?></label>
		<div class="valuewrap">
			<select id="groupin" class="adminlist" name="group_in" <?php echo tpl_disableif(!$user->perms('is_admin')); ?>>
			<?php echo tpl_options($groups, Req::val('group_in', $theuser->infos['global_group'])); ?>
			</select>
			<input type="hidden" name="old_global_id" value="<?php echo Filters::noXSS($theuser->infos['global_group']); ?>" />
		</div>
		</li>

<?php if ($proj->id): ?>
		<li>
		<label for="projectgroupin"><?= eL('projectgroup') ?></label>
		<div class="valuewrap">
			<select id="projectgroupin" class="adminlist" name="project_group_in" <?php echo tpl_disableif(!$user->perms('manage_project')); ?>>
			<?php echo tpl_options(array_merge($project_groups, array(0 => array('group_name' => L('none'), 0 => 0, 'group_id' => 0, 1 => L('none')))), Req::val('project_group_in', $theuser->perms('project_group'))); ?>
			</select>
			<input type="hidden" name="old_group_id" value="<?php echo Filters::noXSS($theuser->perms('project_group')); ?>" />
			<input type="hidden" name="project_id" value="<?php echo $proj->id; ?>" />
		</div>
		</li>
<?php endif; ?>

<?php if ($user->perms('is_admin')): ?>
		<li>
		<label for="accountenabled"><?= eL('accountenabled') ?></label>
		<div class="valuewrap">
			<?php echo tpl_checkbox('account_enabled', Req::val('account_enabled', !Post::val('action') && $theuser->infos['account_enabled']), 'accountenabled'); ?>
		</div>
		</li>

		<li>
		<label for="delete_user"><?= eL('deleteuser') ?></label>
		<div class="valuewrap">
			<?php echo tpl_checkbox('delete_user', false, 'delete_user'); ?>
		</div>
		</li>
<?php endif; ?>
	</ul>
	</fieldset>

	<fieldset>
		<legend><?= eL('preferences'); ?></legend>

	<ul class="form_elements">
		<li>
			<label for="langcode"><?= eL('language') ?></label>
			<div class="valuewrap">
				<select id="langcode" name="lang_code">
				<?= tpl_options(array_merge(array('project'), Flyspray::listLangs()), Post::val('lang_code', $theuser->infos['lang_code']), true); ?>
				</select>
			</div>
		</li>

		<li>
		<label for="time_zone"><?= eL('timezone') ?></label>
		<div class="valuewrap">
			<select id="time_zone" name="time_zone">
			<?php
			$times = array();
			for ($i = -12; $i <= 13; $i++) {
				$times[$i] = L('GMT') . (($i == 0) ? ' ' : (($i > 0) ? '+' . $i : $i));
			}
			?>
			<?php echo tpl_options($times, Req::val('time_zone', $theuser->infos['time_zone'])); ?>
			</select>
		</div>
		</li>

		<li>
		<label for="dateformat"><?= eL('dateformat') ?></label>
		<div class="valuewrap">
			<select id="dateformat" name="dateformat">
			<?php echo tpl_date_formats($theuser->infos['dateformat']); ?>
			</select>
		</div>
		</li>

		<li>
		<label for="dateformat_extended"><?= eL('dateformat_extended') ?></label>
		<div class="valuewrap">
			<select id="dateformat_extended" name="dateformat_extended">
			<?php echo tpl_date_formats($theuser->infos['dateformat_extended'], true); ?>
			</select>
		</div>
		</li>

		<li>
		<label for="tasks_perpage"><?= eL('tasksperpage') ?></label>
		<div class="valuewrap">
			<select name="tasks_perpage" id="tasks_perpage">
			<?php echo tpl_options(array(10, 25, 50, 100, 250), Req::val('tasks_perpage', $theuser->infos['tasks_perpage']), true); ?>
			</select>
		</div>
		</li>

		<li>
		<label for="notifytype"><?= eL('notifytype') ?></label>
		<div class="valuewrap">
			<select id="notifytype" name="notify_type">
			<?php echo tpl_options($fs->getNotificationOptions(), Req::val('notify_type', $theuser->infos['notify_type'])); ?>
			</select>
		</div>
		</li>

		<li>
		<label for="notify_own"><?= eL('notifyown') ?></label>
		<div class="valuewrap">
			<?php echo tpl_checkbox('notify_own', Req::val('notify_own', !Post::val('action') && $theuser->infos['notify_own']), 'notify_own'); ?>
		</div>
		</li>

<?php /*
		<li>
		<label for="notify_online"><?= eL('notifyonline') ?></label>
		<div class="valuewrap">
			<?php echo tpl_checkbox('notify_online', Req::val('notify_online', !Post::val('action')	&& $theuser->infos['notify_online']), 'notify_online'); ?>
		</div>
		</li>
*/ ?>
	</ul>
	</fieldset>

	<fieldset>
		<legend><?= eL('credentials') ?></legend>
	<ul class="form_elements">

<?php if (!$theuser->infos['oauth_uid']): ?>
	<?php if ($user->perms('is_admin') || $user->id == $theuser->id): ?>
		<?php if (!$fs->prefs['disable_changepw']): ?>
			<?php if (!$user->perms('is_admin')): ?>
				<li>
					<label for="oldpass"><?= eL('oldpass') ?></label>
					<div class="valuewrap">
						<input id="oldpass" type="password" name="oldpass" value="" maxlength="100" />
					</div>
				</li>
			<?php endif; ?>

			<li>
				<label for="changepass"><?= eL('changepass') ?></label>
				<div class="valuewrap">
					<input id="changepass" type="password" name="changepass" value="" maxlength="100" />
				</div>
			</li>

			<?php if ($fs->prefs['repeat_password']): ?>
				<li>
					<label for="confirmpass"><?= eL('confirmpass') ?></label>
					<div class="valuewrap">
						<input id="confirmpass" type="password" name="confirmpass" value="" maxlength="100" />
				</div>
				</li>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>

<?php if (!empty($fs->prefs['jabber_server'])): ?>
		<li>
		<label for="jabberid"><?= eL('jabberid') ?></label>
		<div class="valuewrap">
			<input id="jabberid" type="text" name="jabber_id" maxlength="100"
			value="<?php echo Filters::noXSS(Req::val('jabber_id', $theuser->infos['jabber_id'])); ?>" />
			<input type="hidden" name="old_jabber_id" value="<?php echo Filters::noXSS($theuser->infos['jabber_id']); ?>" />
		</div>
		</li>
<?php endif ?>

	</ul>
	</fieldset>

<?php if ($fs->prefs['enable_avatars']): ?>
	<fieldset>
		<legend><?= eL('profileimage') ?></legend>

<?php if($theuser->infos['profile_image'] == '' || !is_file(BASEDIR.'/avatars/'.$theuser->infos['profile_image'])): ?>
	<p><?= eL('noprofileimageselected') ?></p>
<?php endif; ?>

		<div id="profileimagewrap">
			<div id="profileimagedisplay">
				<?php echo tpl_userlinkavatar($theuser->id, 160, 'av_comment'); ?>
			</div>
		</div>

	<ul class="form_elements">
		<li>
		<label for="profileimage_input"><?= eL('profileimage') ?></label>
		<div class="valuewrap">
			<input id="profileimage_input" name="profile_image" type="file" value="<?php echo Filters::noXSS(Req::val('profile_image')); ?>"/>
		</div>
		</li>
	</ul>
	</fieldset>
<?php endif ?>
</div>

<div class="buttons">
	<input type="hidden" name="action" value="<?php echo Filters::noXSS(Req::val('action', $do . '.edituser')); ?>" />
	<?php if (Req::val('area') || $do == 'admin'): ?><input type="hidden" name="area" value="users" /><?php endif; ?>
	<input type="hidden" name="user_id" value="<?php echo Filters::noXSS($theuser->id); ?>" />
	<button class="positive" type="submit"><?= eL('updatedetails') ?></button>
</div>
</form>
