			<div id="right">
			<form action="{$site_index}?{$complete_action}" method="post" name="database_form">
				{!$message}
				<h1>Install status</h1>
				<h2>Congratulations! {$product_name} is installed</h2>
				<div class="installBlock">
				<p class="error">
					PLEASE REMEMBER TO COMPLETELY REMOVE THE SETUP DIRECTORY
				</p>	
				<?php
				if (!$config_writeable)
				{
				?>
				<table class="formBlock">
				<?php 
				}
				if (!$config_writeable)
				{
				?>
					<tr>
						<td>
							The configuration file is not writeable. You will have to upload the following
							code manually. Click in the textarea to highlight all of the code. Copy and
							paste the contents into the flyspray.conf.php file available in the base of
							{$product_name} installation.
						</td>
					</tr>
					<tr>
						<td align="center">
							<textarea class="inputbox" rows="10" cols="38" name="configcode" onclick="javascript:this.form.configcode.focus();this.form.configcode.select();" ><?php echo htmlspecialchars($config_text); ?></textarea>
						</td>
					</tr>
				<?php 
				}
				?>
				</table>
				<?php 
				if (!$config_writeable)
				{
				?>
				<h3>flyspray.conf.php NOT writeable</h3>
				<p>
					To complete setup, copy and paste the contents of the textarea box into flyspray.conf.php
					This file resides in the base of your {$product_name} installation.
				</p>
				<?php
				}
				if (!$daemonise)
				{
				?>
				<h2>Background reminder daemon</h2>
				<p>
				 Starting with the 0.9.8 release, Flyspray has a background daemon to regularly trigger the 
				 scheduled reminders script. The background reminder requires that you have the Command line 
				 version of PHP installed. {$product_name} has found that your system does not 
				 have the command line version of PHP running.
				</p>
				<p>
				An alternative solution is to activate the scripts/schrem.php file regularly through some task 
				scheduler (like "cron") to make a download program (like "wget") to retrieve the file every five or 
				ten minutes. You don't need to save it anywhere, just send it to the bit bucket. Merely retrieving 
				the file causes it to run, and thus, send the reminders out. More details can be obtained at 
				<a href="http://flyspray.rocks.cc/manual/reminders" target="_blank">http://flyspray.rocks.cc/manual/reminders</a>
				</p>
				<?php
				}
				?>
				<?php if ($admin_username && $admin_password): ?>
				<h3>Administration Login Details</h3>
				<p>
					<strong>Username : {$admin_username}</strong><br />
					<strong>Password : {$admin_password}</strong>
				</p>
				<?php endif; ?>
				</div>
				<div class="clr"></div>
				<h2>View Site</h2>
				<div class="installBlock">
				<div class="formBlock farRight" style="display:inline;">
				<?php if ($admin_username && $admin_password): ?>
					<input type="hidden" name="prev_page" value="index.php?do=myprofile" />
					<input type="hidden" name="user_name" value="{$admin_username}" />
					<input type="hidden" name="password" value="{$admin_password}" />
				<?php endif; ?>
					<input type="hidden" name="remember_login" value="1" />
					<input class="button" type="submit" name="next" value="Next >>" />
				</div>
				<p>
				Proceed to {$product_name} index
				</p>
				</div>
			</form>
			</div><!-- end of right -->
			<div class="clr"></div>