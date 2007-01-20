			<div id="right">
			<form action="index.php" method="post" name="database_form">
				{!$message}
				<h1>Administration setup</h1>
				<h2>Setup all the Application values</h2>
				<div class="installBlock">
				<table class="formBlock" style="width:68%;">
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
				1) Admin <strong>Email, Username, Password</strong> are values for the Administrator of your {$product_name}
				Installation. You can change these values through the administration section of {$product_name}.
				</p>

				<p>
				 2) The <strong>Reminder Daemon</strong>.
				 Starting with the 0.9.8 release, {$product_name} has a background daemon to regularly trigger 
				 the scheduled reminders script and background notifications. 
				</p>

				<input type="hidden" name="db_type" value="{$db_type}" />
				<input type="hidden" name="db_hostname" value="{$db_hostname}" />
				<input type="hidden" name="db_username" value="{$db_username}" />
				<input type="hidden" name="db_password" value="{$db_password}" />
				<input type="hidden" name="db_name" value="{$db_name}" />
				<input type="hidden" name="db_prefix" value="{$db_prefix}" />
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
