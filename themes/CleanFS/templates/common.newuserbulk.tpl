<?php if ($do == 'admin'): echo tpl_form(Filters::noXSS(CreateURL($do, 'newuserbulk')),null,null,null,'id="registernewuser"');
					 else: echo tpl_form(Filters::noXSS($_SERVER['SCRIPT_NAME']),	  null,null,null,'id="registernewuser"');
endif; ?>

<fieldset>
	<legend><?php echo Filters::noXSS(L('bulkuserstoadd')); ?></legend>
	<!-- Header -->
	<table class="bulkuser">
	<thead>
	<tr>
		<th><?php echo Filters::noXSS(L('realname')); ?></th>
		<th><?php echo Filters::noXSS(L('username')); ?></th>
		<th><?php echo Filters::noXSS(L('emailaddress')); ?></th>
	</tr>
	</thead>
	<tbody>
<?php for ($i = 0 ; $i < 10 ; $i++) { ?>
	<!-- User <?php echo Filters::noXSS($i); ?> -->
	<tr>
		<td><input id="realname" name="real_name<?php echo Filters::noXSS($i); ?>" class="text" value="<?php echo Filters::noXSS(Req::val('real_name')); ?>" type="text" size="20" maxlength="100" onblur="this.form.elements['user_name<?php echo Filters::noXSS($i); ?>'].value = this.form.elements['real_name<?php echo Filters::noXSS($i); ?>'].value.replace(/ /g,'');"/></td>
		<td><input id="username" name="user_name<?php echo Filters::noXSS($i); ?>" value="<?php echo Filters::noXSS(Req::val('user_name')); ?>" class="text" type="text" size="20" maxlength="32"  onblur="checkname(this.value); "/></td>
		<td><input id="emailaddress" name="email_address<?php echo Filters::noXSS($i); ?>" class="text" value="<?php echo Filters::noXSS(Req::val('email_address')); ?>" type="text" size="20" maxlength="100" /></td>
	</tr>
<?php } ?>
	</tbody>
	</table>
</fieldset>

<fieldset>
	<legend><?php echo Filters::noXSS(L('optionsforallusers')); ?></legend>

	<ul class="form_elements">
		<li>
			<label for="notify_type"><?php echo Filters::noXSS(L('notifications')); ?></label>
			<div class="valuewrap">
				<select id="notify_type" name="notify_type">
				<?php echo tpl_options($fs->GetNotificationOptions(), Req::val('notify_type')); ?>
				</select>
			</div>
		</li>
		<li>
			<label for="time_zone"><?php echo Filters::noXSS(L('timezone')); ?></label>
			<div class="valuewrap">
				<select id="time_zone" name="time_zone">
				<?php
					$times = array();
					for ($i = -12; $i <= 13; $i++) {
						$times[$i] = L('GMT') . (($i == 0) ? ' ' : (($i > 0) ? '+' . $i : $i));
					}
				?>
				<?php echo tpl_options($times, Req::val('time_zone', 0)); ?>
				</select>
			</div>
		</li>
<?php if (isset($groups)): ?>
		<li>
			<label for="groupin"><?php echo Filters::noXSS(L('globalgroup')); ?></label>
			<div class="valuewrap">
				<select id="groupin" class="adminlist" name="group_in">
				<?php echo tpl_options($groups, Req::val('group_in', $fs->prefs['anon_group'])); ?>
				</select>
			</div>
		</li>
<?php endif; ?>
	</ul>
</fieldset>

<div class="buttons">
<?php if ($do == 'admin'): ?>
	<input type="hidden" name="action" value="admin.newuserbulk" />
	<input type="hidden" name="do" value="admin" />
	<input type="hidden" name="area" value="newuserbulk" />
<?php else: ?>
	<input type="hidden" name="action" value="register.newuserbulk" />
<?php endif; ?>
	<button type="submit" id="buSubmit"><?php echo Filters::noXSS(L('registerbulkaccount')); ?></button>
</div>
</form>
