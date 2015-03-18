			<div id="right">
			<form action="<?php echo Filters::noXSS($site_index); ?><?php echo Filters::noXSS($complete_action); ?>" method="post" name="database_form">
				<h1>Install status</h1>
				<h2>Congratulations! <?php echo Filters::noXSS($product_name); ?> is now installed and ready to run.</h2>
				<div class="installBlock">
				<p class="error">
					Please remove the setup directory now.
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
							<?php echo Filters::noXSS($product_name); ?> installation.
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
					This file resides in the base of your <?php echo Filters::noXSS($product_name); ?> installation.
				</p>
				<?php
				}
				
				if ($admin_username && $admin_password): ?>
				<h3>Administration Login Details</h3>
				<p>
					<strong>Username : <?php echo Filters::noXSS($admin_username); ?></strong><br />
					<strong>Password : <?php echo Filters::noXSS($admin_password); ?></strong>
				</p>
				<?php endif; ?>
				</div>
				<div class="clr"></div>
				<h2>View Site</h2>
				<div class="installBlock">
				<div class="formBlock farRight" style="display:inline;">
				<?php if ($admin_username && $admin_password): ?>
					<input type="hidden" name="return_to" value="./" />
                    <input type="hidden" name="do" value="authenticate" />
					<input type="hidden" name="user_name" value="<?php echo Filters::noXSS($admin_username); ?>" />
					<input type="hidden" name="password" value="<?php echo Filters::noXSS($admin_password); ?>" />
				<?php endif; ?>
					<input type="hidden" name="remember_login" value="1" />
					<input class="button" type="submit" name="next" value="Next >>" />
				</div>
				<p>
				Proceed to <?php echo Filters::noXSS($product_name); ?> index
				</p>
				</div>
			</form>
			</div><!-- end of right -->
			<div class="clr"></div>