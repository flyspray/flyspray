			<div id="right">
			<form action="index.php" method="post" name="database_form">
				{!$message}
				<h1>Administration setup</h1>
				<h2>Setup all the Application values</h2>
				<div class="installBlock">
				<table class="formBlock" style="width:68%;">
					<tr>
						<td align="right">System Path</td>
						<td align="center">
							<input class="inputbox text" type="text" name="absolute_path" value="{$absolute_path}" size="30" />
						</td>
					</tr>
					{!$admin_email}
					{!$admin_username}
					{!$admin_password}
					<?php if ($daemonise): ?>
					<tr>
						<td align="right">Reminder daemon</td>
						<td align="center">
							{!$daemonise}
						</td>
					</tr>					
					<?php endif; ?>
				</table>
				<p>
				The Database schema has been populated. Please follow the instructions to complete the Admin configuration.
				</p>
				<p>
				1) <strong>System Path</strong> is where your copy of {$product_name}
				is being installed. Again as above, do not change the values or add extra slashes to the path unless you 
				are certain its not going to affect the installation.
				</p>
				<?php
				$counter = 1;
				if ($admin_email && $admin_username & $admin_password)
				{
					$counter++;
				?>
				<p>
				{$counter}) Admin <strong>Email, Username, Password</strong> are values for the Administrator of your {$product_name}
				Installation. You can change these values through the administration section of {$product_name}.
				</p>
				<?php
				}
				if ($daemonise)
				{
					$counter++;
				?>
				<p>
				{$counter}) The <strong>Reminder Daemon</strong>.
				 Starting with the 0.9.8 release, {$product_name} has a background daemon to regularly trigger 
				 the scheduled reminders script. The background reminder requires that you have the Command line 
				 version of PHP installed. {$product_name} installer has found that your system does have the 
				 command line version of PHP running. You can choose to enable/disable the background reminder.
				</p>
				<?php
				}
				else
				{
				?>
				<h3>Additional Configuration</h3>
				<p>
				 The <strong>Reminder Daemon</strong>. 
				 Starting with the 0.9.8 release, {$product_name} has a background daemon to regularly trigger 
				 the scheduled reminders script. The background reminder requires that you have the Command line 
				 version of PHP installed. {$product_name} installer has found that your system does not have the 
				 command line version of PHP running.
				</p>
				<p>
				An alternative solution is to activate schedule.php file regularly through some task 
				scheduler (like "cron") to make a download program (like "wget") to retrieve the file every five or 
				ten minutes. You don't need to save it anywhere, just send it to the bit bucket. Merely retrieving 
				the file causes it to run, and thus, send the reminders out. More details can be obtained at 
				<a href="http://flyspray.rocks.cc/manual/reminders" target="_blank">http://flyspray.rocks.cc/manual/reminders</a>.
				Meanwhile you can proceed to complete the installation process.
				</p>
				<?php
				}
				?>
				<input type="hidden" name="db_type" value="{$db_type}" />
				<input type="hidden" name="db_hostname" value="{$db_hostname}" />
				<input type="hidden" name="db_username" value="{$db_username}" />
				<input type="hidden" name="db_password" value="{$db_password}" />
				<input type="hidden" name="db_name" value="{$db_name}" />
				<input type="hidden" name="db_prefix" value="{$db_prefix}" />
				<input type="hidden" name="db_setup_options" value="{$db_setup_options}" />
				</div>
				<div class="clr"></div>
				<h2>Proceed to final Setup</h2>
				<div class="installBlock">
				<div class="formBlock farRight" style="display:inline;">
					<input type="hidden" name="action" value="complete" />
					<input class="button" type="submit" name="next" value="Next >>" />
				</div>
				<p>
				Proceed to complete {$product_name} setup.
				</p>
				</div>
			</form>
			</div><!-- end of right -->
			<div class="clr"></div>
