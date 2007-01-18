<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{$title . '  ' . $product_name}</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="styles/setup.css" type="text/css" media="screen" />
</head>
<body>
<div id="center">
  <div id="container">
    <div id="header">
      <div id="logo">
        <h1><a href="{$index}" title="Flyspray - The bug Killer!">Upgrade</a></h1>
      </div><!-- End of logo -->
    </div><!-- End of header -->
    <div id="content">
      <div id="bodyContent">
      <form action="upgrade.php" method="post">
      <input type="hidden" name="upgrade" value="1" />
        <div class="install">	
            <h2>Precondition checks</h2>
            <p>
            Your current version is <strong>{$installed_version}</strong> and the version we can upgrade to is <strong>{$short_version}</strong>.
            <div class="installBlock">
				<table class="formBlock">
				<tr>
					<td valign="top">../flyspray.conf.php</td>
					<td align="left"><b><?php if ($checks['config_writable']): ?><span class="green">writeable</span><?php else: ?><span class="red">writeable</span><?php endif; ?></b></td>
					<td>&nbsp;</td>
				</tr>
				</table>
				<p>
				In order to upgrade {$product_name} 
				correctly it needs to be able to access and write flyspray.conf.php.
				</p>
			</div>
            <?php if (!$upgrade_possible): ?>
            Thus, an upgrade is not possible. {$todo}
            <?php else: ?>
            Thus, an upgrade is possible.
            </p>
            
            <h2>Precautions</h2>
            <p>Create a backup of your <strong>database</strong> <em>and</em> all Flyspray related <strong>files</strong> before performing the upgrade.</p>
            
            <?php if ($upgrade_options): ?>
            <h2>Upgrade options</h2>
            <p>{!$upgrade_options}</p>
            <?php endif; ?>
            
            <h2>Perform Upgrade</h2>
            <p><input name="upgrade" class="button" value="Perform Upgrade > >" type="submit" /></p>
            <?php endif; ?> 
        </div><!-- End of install -->
        </form>
        <div class="clr"></div>
      </div><!-- End of bodyContent -->
      <div class="clr"></div>
    </div><!-- End of content -->
    <div id="footer">
      <p>
        {$copyright}<br />{$version}
      </p>
    </div><!-- End of footer -->
  </div><!-- End of container -->
</div><!-- End of center -->
</body>
</html>