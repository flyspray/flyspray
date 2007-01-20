			<div id="right">
			{$message}
			<h1>Pre-installation check</h1>
			<h2>PHP and supported libraries</h2>
			<div class="installBlock">
				<table class="formBlock">
				<tr>
					<td class="heading">Library</td>
					<td class="heading">Status</td>
					<td class="heading">&nbsp;</td>
				</tr>
				<tr>
					<td>PHP >= {$required_php}</td>
					<td align="left"><b>{!$php_output}</b></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td class="heading">Database</td>
					<td class="heading">in PHP</td>
					<td class="heading">{$product_name}</td>
				</tr>
				{!$database_output}
				</table>
				<p>
				If any of these items are highlighted
				in red then please take actions to correct them. Failure to do so
				could lead to your {$product_name} installation not functioning
				correctly.
				</p>
			</div>
			<div class="clr"></div>
	
			<h2>Recommended settings:</h2>
			<div class="installBlock">
				<table class="formBlock">
				<tr>
					<td class="heading">Directive</td>
					<td class="heading">Recommended</td>
					<td class="heading">Actual</td>
				</tr>
				{!$php_settings}
				</table>
				<p>
				These settings are recommended for PHP in order to ensure full
				compatibility with {$product_name}.
				</p>
				<p>
				However, {$product_name} will still operate if your
				settings do not quite match the recommended shown here.
				</p>
			</div>
			<div class="clr"></div>
	
			<h2>Directory and File Permissions:</h2>
			<div class="installBlock">
				<table class="formBlock">
				<tr>
					<td valign="top">../flyspray.conf.php</td>
					<td align="left"><b>{!$config_output}</b></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td valign="top">../cache</td>
					<td align="left"><b>{!$cache_output}</b></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td valign="top">../attachments</td>
					<td align="left"><b>{!$att_output}</b></td>
					<td>&nbsp;</td>
				</tr>
				</table>
				<p>
				In order for {$product_name} to function
				correctly it needs to be able to access or write to certain files
				or directories. If you see "Unwriteable" you need to change the
				permissions on the file or directory to allow {$product_name}
				to write to it.
				</p>
				<?php if (!$config_status): ?>
				<p>
				The installer has detected that the <strong>flyspray.conf.php</strong> file is not
				writeable. Please make it writeable by the web-server user or world writeable to
				proceed with the setup. Alternatively if you wish to proceed, the installer will
				make available the contents of the configuration file at the end of the setup. You
				will then have to manually copy and paste the contents into the configuration file
				located at <strong><?php echo APPLICATION_PATH . DIRECTORY_SEPARATOR . 'flyspray.conf.php'; ?></strong>.
				</p>
				<?php endif; ?>
			</div>
			<div class="clr"></div>
	
			<h2>Proceed to Licence Agreement:</h2>
			<div class="installBlock">
				<form class="formBlock farRight" action="index.php" method="post" name="adminForm" style="display:inline;">
				<input type="hidden" name="action" value="licence" />
				<input name="next" type="submit" class="button" value="Next >>" {tpl_disableif(!$status)} />
				</form>
				<?php if (!$status) { ?>
				<p>
				You seem to have problems with the Pre-install configuration. Once you have fixed the
				problem, please refresh the page to be able to proceed to the next stage of
				{$product_name} setup.
				</p>
				<?php }else { ?>
				<p>
				All configurations seems to be in place. You may proceed to the Licence Agreement page.
				</p>
				<?php } ?>
			</div>
			<div class="clr"></div>
			</div><!-- end of right -->
			<div class="clr"></div>