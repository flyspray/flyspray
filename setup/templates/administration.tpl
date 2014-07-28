			<div id="right">
			<form action="index.php" method="post" name="database_form">
				<?php echo $message; ?>

				<h1>Administration setup</h1>
				<h2>Setup all the Application values</h2>
				<div class="installBlock">
				<table class="formBlock" style="width:68%;">
					<?php echo $admin_email; ?>

					<?php echo $admin_username; ?>

					<?php echo $admin_password; ?>

					<?php if ($daemonise): ?>
					<tr>
						<td align="right">Reminder daemon</td>
						<td align="center">
							<?php echo $daemonise; ?>

						</td>
					</tr>
					<?php endif; ?>
				</table>
				<p>
				The Database schema has been populated. Please follow the instructions to complete the Admin configuration.
				</p>
				1) Admin <strong>Email, Username, Password</strong> are values for the Administrator of your <?php echo Filters::noXSS($product_name); ?>

				Installation. You can change these values through the administration section of <?php echo Filters::noXSS($product_name); ?>.
				</p>

				<p>
				 2) The <strong>Reminder Daemon</strong>.
				 Starting with the 0.9.8 release, <?php echo Filters::noXSS($product_name); ?> has a background daemon. This is required for Jabber notifications, reminders
                 and other scheduled actions which will added to Flyspray in the future.
				</p>

				<input type="hidden" name="db_type" value="<?php echo Filters::noXSS($db_type); ?>" />
				<input type="hidden" name="db_hostname" value="<?php echo Filters::noXSS($db_hostname); ?>" />
				<input type="hidden" name="db_username" value="<?php echo Filters::noXSS($db_username); ?>" />
				<input type="hidden" name="db_password" value="<?php echo Filters::noXSS($db_password); ?>" />
				<input type="hidden" name="db_name" value="<?php echo Filters::noXSS($db_name); ?>" />
				<input type="hidden" name="db_prefix" value="<?php echo Filters::noXSS($db_prefix); ?>" />
				</div>
				<div class="clr"></div>
				<h2>Proceed to final Setup</h2>
				<div class="installBlock">
				<div class="formBlock farRight" style="display:inline;">
					<input type="hidden" name="action" value="complete" />
					<input class="button" type="submit" name="next" value="Next >>" />
				</div>
				<p>
				Proceed to complete <?php echo Filters::noXSS($product_name); ?> setup.
				</p>
				</div>
			</form>
			</div><!-- end of right -->
			<div class="clr"></div>
