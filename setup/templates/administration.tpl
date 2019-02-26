<div>
<form action="index.php" method="post" name="database_form">
	<?php echo $message; ?>
	<h1><?= eL('administrationsetup') ?></h1>
	<h2><?= eL('setupapplicationvalue') ?></h2>
	<div class="installBlock">
<script type="text/javascript">
function ShowHidePassword(id) {
	if(document.getElementById(id).type=="text") {
		document.getElementById(id).type="password";
	} else {
		document.getElementById(id).type="text";
	}
}
</script>

	<p><?= L('adminsetuptip1') ?></p>
	<p><?= L('adminsetuptip2') ?></p>
	<p><?= L('adminsetuptip3') ?></p>

	<table class="formBlock">
	<tr>
		<td style="text-align:right"><?= eL('adminusername') ?></td>
		<td style="text-align:left"><input class="inputbox" type="text" name="admin_username" value="<?php echo $admin_username; ?>" required="required" size="30" /></td>
		<td></td>
	</tr>
	<tr>
		<td style="text-align:right"><?= eL('adminrealname') ?></td>
		<td style="text-align:left"><input class="inputbox" type="text" name="admin_realname" value="<?php echo $admin_realname; ?>" size="30" /></td>
		<td></td>
	</tr>
	<tr>
		<td style="text-align:right"><?= eL('adminemail') ?></td>
		<td style="text-align:left"><input class="inputbox" type="text" name="admin_email" value="<?php echo $admin_email; ?>" required="required" size="30" /></td>
		<td></td>
	</tr>
	<tr>
		<td style="text-align:right"><?= eL('adminxmpp') ?></td>
		<td style="text-align:left"><input class="inputbox" type="text" name="admin_xmpp" value="<?php echo $admin_xmpp; ?>" size="30" /></td>
		<td></td>
	</tr>
	<tr>
		<td style="text-align:right"><?= eL('adminpassword') ?></td>
		<td style="text-align:left"><input class="inputbox" type="password" name="admin_password" id="admin_password" value="<?php echo $admin_password; ?>" required="required" size="30" /></td>
		<td style="text-align:left"><label for="showpassword"><?= eL('showpassword') ?></label><input type="checkbox" onclick="ShowHidePassword('admin_password')" id="showpassword"></td>
	</tr>
	<tr>
		<td style="text-align:right"><?= eL('syntax') ?></td>
		<td style="text-align:left">
			<select name="syntax_plugin">
			<option value="dokuwiki">Text/Dokuwiki</option>
			<option value="none">HTML/none</option>
			<option value="html">HTML/CKEditor</option>
			</select>
		</td>
		<td style="text-align:left"><?= L('syntaxtext') ?></td>
	</tr>
	<?php if ($daemonise): ?>
	<tr>
		<td style="text-align:right" title="<?= eL('scheduletitle') ?>"><?= eL('enablescheduling') ?></td>
		<td style="text-align:center"><?php echo $daemonise; ?></td>
	</tr>
	<?php endif; ?>
	</table>

	<input type="hidden" name="db_type" value="<?php echo Filters::noXSS($db_type); ?>" />
	<input type="hidden" name="db_hostname" value="<?php echo Filters::noXSS($db_hostname); ?>" />
	<input type="hidden" name="db_username" value="<?php echo Filters::noXSS($db_username); ?>" />
	<input type="hidden" name="db_password" value="<?php echo Filters::noXSS($db_password); ?>" />
	<input type="hidden" name="db_name" value="<?php echo Filters::noXSS($db_name); ?>" />
	<input type="hidden" name="db_prefix" value="<?php echo Filters::noXSS($db_prefix); ?>" />

	<p><?= eL('proceedtofinalsetuptext') ?></p>
	<input type="hidden" name="action" value="complete" />
	<button class="button" type="submit" name="next" value="<?= eL('next') ?> >>" ><?= eL('proceedtofinalsetup') ?></button>
		
	</div>
</form>
</div>
