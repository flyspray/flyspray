	<div id="right">
		<?php echo Filters::noXSS($message); ?>

		<h1><?php echo Filters::noXSS(L('preinstallcheck')); ?></h1>
		<h2><?php echo Filters::noXSS(L('libcheck')); ?></h2>
		<div class="installBlock">
		<table class="formBlock">
		<tr>
			<td class="heading"><?php echo Filters::noXSS(L('library')); ?></td>
			<td class="heading"><?php echo Filters::noXSS(L('status')); ?></td>
			<td class="heading">&nbsp;</td>
		</tr>
		<tr>
			<td>PHP <?php echo PHP_VERSION; ?> >= <?php echo Filters::noXSS($required_php); ?></td>
			<td align="left"><b><?php echo $php_output; ?></b></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>XML Extension</td>
			<td align="left"><b><?php echo Setup::ReturnStatus($xmlStatus); ?></b></td>
			<td>&nbsp;</td>
			</tr>
		<tr>
			<td>GD Library</td>
			<td align="left"><b><?php echo Setup::ReturnStatus(extension_loaded('gd'), 'yes'); ?></b></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>Exif Library</td>
			<td align="left"><b><?php echo Setup::ReturnStatus(extension_loaded('exif'), 'yes'); ?></b></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td>SAPI (<?php echo Filters::noXSS(php_sapi_name()); ?>)</td>
			<td align="left"><b><?php echo Setup::ReturnStatus($sapiStatus, 'support'); ?></b></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="heading"><?php echo Filters::noXSS(L('database')); ?></td>
			<td class="heading"><?php echo Filters::noXSS(L('inphp')); ?></td>
			<td class="heading" style="text-align:center"><?php echo Filters::noXSS($product_name); ?></td>
		</tr>
		<?php echo $database_output; ?>
		</table>

		<p><?php echo L('libchecktext'); ?></p>
                <?php if (!$sapiStatus): ?>
                <p><strong>CGI server API is not supported</strong>. Consider upgrading to FastCGI, otherwise you have to add
                <code>force_baseurl = "http://yourflyspray/"</code> manually to flyspray.conf.php after setup.
                </p>
                <?php endif; ?>
		</div>
		<div class="clr"></div>

		<h2><?php echo Filters::noXSS(L('recsettings')); ?></h2>
		<div class="installBlock">
			<table class="formBlock">
				<tr>
					<td class="heading"><?php echo Filters::noXSS(L('directive')); ?></td>
					<td class="heading"><?php echo Filters::noXSS(L('recommended')); ?></td>
					<td class="heading"><?php echo Filters::noXSS(L('actual')); ?></td>
				</tr>
				<?php echo $php_settings; ?>
			</table>
			<p><?php echo Filters::noXSS(L('recsettingstext1')); ?></p>
			<p><?php echo Filters::noXSS(L('recsettingstext2')); ?></p>
		</div>
		<div class="clr"></div>

		<h2><?php echo Filters::noXSS(L('dirandfileperms')); ?></h2>
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
				<p><?php echo Filters::noXSS(L('dirandfilepermstext')); ?></p>
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

		<h2><?php echo Filters::noXSS(L('proceedtodbsetup')); ?></h2>
		<div class="installBlock">
				<form class="formBlock farRight" action="index.php" method="post" name="adminForm" style="display:inline;">
				<input type="hidden" name="action" value="database" />
				<input name="next" type="submit" class="button" value="<?php echo Filters::noXSS(L('next')); ?> >>" <?php echo Filters::noXSS(tpl_disableif(!$status)); ?> />
				</form>
				<?php if (!$status) { ?>
				<p>
				You seem to have problems with the Pre-install configuration. Once you have fixed the
				problem, please refresh the page to be able to proceed to the next stage of
				<?php echo Filters::noXSS($product_name); ?> setup.
				</p>
				<?php }else { ?>
				<p><?php echo Filters::noXSS(L('proceedtodbsetuptext')); ?></p>
				<?php } ?>
		</div>
		<div class="clr"></div>
	</div><!-- end of right -->
	<div class="clr"></div>
