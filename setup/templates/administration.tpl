<div>
<form action="index.php" method="post" name="database_form">
	<?php echo $message; ?>
	<h1><?php echo Filters::noXSS(L('administrationsetup')); ?></h1>
	<h2><?php echo Filters::noXSS(L('setupapplicationvalue')); ?></h2>
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

	<p><?php echo L('adminsetuptip1'); ?></p>
	<p><?php echo L('adminsetuptip2'); ?></p>
	<p><?php echo L('adminsetuptip3'); ?></p>

	<table class="formBlock">
	<tr>
		<td align="right"><?php echo Filters::noXSS(L('adminemail')); ?></td>
		<td align="center"><input class="inputbox" type="text" name="admin_email" value="<?php echo $admin_email; ?>" size="30" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo Filters::noXSS(L('adminusername')); ?></td>
		<td align="center"><input class="inputbox" type="text" name="admin_username" value="<?php echo $admin_username; ?>" size="30" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo Filters::noXSS(L('adminpassword')); ?></td>
		<td align="center"><input class="inputbox" type="password" name="admin_password" id="admin_password" value="<?php echo $admin_password; ?>" size="30" /></td>
	</tr>
	<tr>
		<td align="right"><label for="showpassword"><?php echo Filters::noXSS(L('showpassword')); ?></label></td>
		<td align="center"><input type="checkbox" onclick="ShowHidePassword('admin_password')" id="showpassword"></td>
	</tr>
	<tr>
		<td><?php echo L('syntaxtext'); ?></td>
		<td>
			<select name="syntax_plugin">
			<option value="dokuwiki">Text/Dokuwiki</option>
			<option value="">HTML/CKEditor</option>
			</select>
		</td>
	</tr>
	<?php if ($daemonise): ?>
	<tr>
		<td align="right" title="<?php echo Filters::noXSS(L('scheduletitle')); ?>"><?php echo Filters::noXSS(L('enablescheduling')); ?></td>
		<td align="center"><?php echo $daemonise; ?></td>
	</tr>
	<?php endif; ?>
	</table>

	<input type="hidden" name="db_type" value="<?php echo Filters::noXSS($db_type); ?>" />
	<input type="hidden" name="db_hostname" value="<?php echo Filters::noXSS($db_hostname); ?>" />
	<input type="hidden" name="db_username" value="<?php echo Filters::noXSS($db_username); ?>" />
	<input type="hidden" name="db_password" value="<?php echo Filters::noXSS($db_password); ?>" />
	<input type="hidden" name="db_name" value="<?php echo Filters::noXSS($db_name); ?>" />
	<input type="hidden" name="db_prefix" value="<?php echo Filters::noXSS($db_prefix); ?>" />

	<p><?php echo Filters::noXSS(L('proceedtofinalsetuptext')); ?></p>
	<input type="hidden" name="action" value="complete" />
	<button class="button" type="submit" name="next" value="<?php echo Filters::noXSS(L('next')); ?> >>" ><?php echo Filters::noXSS(L('proceedtofinalsetup')); ?></button>
		
	</div>
</form>
</div>
