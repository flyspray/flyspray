<form action="<?php if ($do == 'admin'): ?><?php echo Filters::noXSS(CreateURL($do, 'newuser')); ?><?php else: ?><?php echo Filters::noXSS($_SERVER['SCRIPT_NAME']); ?><?php endif; ?>" method="post" enctype="multipart/form-data" id="registernewuser">
	<ul class="form_elements">
		<li class="required">
			<?php if ($do == 'admin'): ?>
				<input type="hidden" name="action" value="admin.newuser" />
				<input type="hidden" name="do" value="admin" />
				<input type="hidden" name="area" value="newuser" />
			<?php else: ?>
				<input type="hidden" name="action" value="register.newuser" />
			<?php endif; ?>
			<label for="username"><?php echo Filters::noXSS(L('username')); ?>*</label>
			<input id="username" name="user_name" value="<?php echo Filters::noXSS(Req::val('user_name')); ?>" class="required text" type="text" size="20" maxlength="32" onblur="checkname(this.value);" />
			<br /><span id="errormessage"></span>
		</li>

		<?php if (!$fs->prefs['disable_changepw']): ?>

		<li>
			<label for="userpass"><?php echo Filters::noXSS(L('password')); ?></label>
			<input id="userpass" class="password" name="user_pass" value="<?php echo Filters::noXSS(Req::val('user_pass')); ?>" type="password" size="20" maxlength="100" /> <em><?php echo Filters::noXSS(L('minpwsize')); ?></em>
		</li>

		<li>
			<label for="userpass2"><?php echo Filters::noXSS(L('confirmpass')); ?></label>
			<input id="userpass2" class="password" name="user_pass2" value="<?php echo Filters::noXSS(Req::val('user_pass2')); ?>" type="password" size="20" maxlength="100" /><br />
			<span class="note"><?php echo Filters::noXSS(L('leaveemptyauto')); ?></span>
		</li>

		<?php endif; ?>

		<li class="required">
			<label for="realname"><?php echo Filters::noXSS(L('realname')); ?>*</label>
			<input id="realname" name="real_name" class="required text" value="<?php echo Filters::noXSS(Req::val('real_name')); ?>" type="text" size="20" maxlength="100" />
		</li>

		<li class="required">
			<label for="emailaddress"><?php echo Filters::noXSS(L('emailaddress')); ?>*</label>
			<input id="emailaddress" name="email_address" class="text required" value="<?php echo Filters::noXSS(Req::val('email_address')); ?>" type="text" size="20" maxlength="100" /> <em><?php echo Filters::noXSS(L('validemail')); ?></em>
		</li>

		<li>
			<label for="verifyemailaddress"><?php echo Filters::noXSS(L('verifyemailaddress')); ?></label>
			<input id="verifyemailaddress" value="<?php echo Filters::noXSS(Req::val('verify_email_address')); ?>" name="verify_email_address" class="required text" type="text" size="20" maxlength="100" />
		</li>

		<li>
			<label for="jabberid"><?php echo Filters::noXSS(L('jabberid')); ?></label>
			<input id="jabberid" name="jabber_id" class="text" type="text" value="<?php echo Filters::noXSS(Req::val('jabber_id')); ?>" size="20" maxlength="100" />
		</li>

		<li>
			<label for="profileimage"><?php echo Filters::noXSS(L('profileimage')); ?></label>
			<input id="profileimage" name="profile_image" type="file" value="<?php echo Filters::noXSS(Req::val('profile_image')); ?>"/>
		</li>

		<li>
			<label for="notify_type"><?php echo Filters::noXSS(L('notifications')); ?></label>
			<select id="notify_type" name="notify_type">
			<?php echo tpl_options($fs->GetNotificationOptions(), Req::val('notify_type')); ?>
			</select>
		</li>

		<li>
			<label for="time_zone"><?php echo Filters::noXSS(L('timezone')); ?></label>
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
			<label for="groupin"><?php echo Filters::noXSS(L('globalgroup')); ?></label>
			<select id="groupin" class="adminlist" name="group_in">
			<?php echo tpl_options($groups, Req::val('group_in')); ?>
			</select>
		</li>
		<?php endif; ?>
	</ul>
	<p><button type="submit" id="buSubmit"><?php echo Filters::noXSS(L('registeraccount')); ?></button></p>
</form>
