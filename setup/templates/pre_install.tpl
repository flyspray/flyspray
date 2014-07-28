			<div id="right">
			<?php echo Filters::noXSS($message); ?>

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
					<td>PHP >= <?php echo Filters::noXSS($required_php); ?></td>
					<td align="left"><b><?php echo $php_output; ?></b></td>
					<td>&nbsp;</td>
				</tr>
                <tr>
					<td>XML Extension</td>
					<td align="left"><b><?php echo Setup::ReturnStatus($xmlStatus); ?></b></td>
					<td>&nbsp;</td>
				</tr>
                <tr>
					<td>SAPI (<?php echo Filters::noXSS(php_sapi_name()); ?>)</td>
					<td align="left"><b><?php echo Setup::ReturnStatus($sapiStatus, 'support'); ?></b></td>
					<td>&nbsp;</td>
				</tr>

				<tr>
					<td class="heading">Database</td>
					<td class="heading">in PHP</td>
					<td class="heading" style="text-align:center"><?php echo Filters::noXSS($product_name); ?></td>
				</tr>
				<?php echo $database_output; ?>

				</table>
				<p>
				To make setup possible, you must have a correct PHP version installed and
                <strong>at least one</strong> supported database.
                </p>
                <?php if (!$sapiStatus): ?>
                <p><strong>CGI server API is not supported</strong>. Consider upgrading to FastCGI, otherwise you have to add
                <code>force_baseurl = "http://yourflyspray/"</code> manually to flyspray.conf.php after setup.
                </p>
                <?php endif; ?>
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
				<?php echo $php_settings; ?>

				</table>
				<p>
				These settings are recommended for PHP in order to ensure full
				compatibility with <?php echo Filters::noXSS($product_name); ?>.
				</p>
				<p>
				However, <?php echo Filters::noXSS($product_name); ?> will still operate if your
				settings do not quite match the recommended shown here.
				</p>
			</div>
			<div class="clr"></div>

			<h2>Directory and File Permissions:</h2>
			<div class="installBlock">
				<table class="formBlock">
				<tr>
					<td valign="top">../flyspray.conf.php</td>
					<td align="left"><b><?php echo $config_output; ?></b></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td valign="top">../cache</td>
					<td align="left"><b><?php echo $cache_output; ?></b></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td valign="top">../attachments</td>
					<td align="left"><b><?php echo $att_output; ?></b></td>
					<td>&nbsp;</td>
				</tr>
				</table>
				<p>
				In order for <?php echo Filters::noXSS($product_name); ?> to function
				correctly it needs to be able to access or write to certain files
				or directories. If you see "Unwriteable" you need to change the
				permissions on the file or directory to allow <?php echo Filters::noXSS($product_name); ?>

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

			<h2>Proceed to Database Setup:</h2>
			<div class="installBlock">
				<form class="formBlock farRight" action="index.php" method="post" name="adminForm" style="display:inline;">
				<input type="hidden" name="action" value="database" />
				<input name="next" type="submit" class="button" value="Next >>" <?php echo Filters::noXSS(tpl_disableif(!$status)); ?> />
				</form>
				<?php if (!$status) { ?>
				<p>
				You seem to have problems with the Pre-install configuration. Once you have fixed the
				problem, please refresh the page to be able to proceed to the next stage of
				<?php echo Filters::noXSS($product_name); ?> setup.
				</p>
				<?php }else { ?>
				<p>
				All configurations seems to be in place. You may proceed to the Database Setup page.
				</p>
				<?php } ?>
			</div>
			<div class="clr"></div>
			</div><!-- end of right -->
			<div class="clr"></div>
