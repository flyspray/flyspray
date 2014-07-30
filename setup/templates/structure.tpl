<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo Filters::noXSS($title . '  ' . $product_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="styles/setup.css" type="text/css" media="screen" />
<?php echo $headers; ?>

</head>
<body>
<div id="center">
  <div id="container">
    <div id="header">
      <div id="logo">
        <h1><a href="<?php echo Filters::noXSS($index); ?>" title="Flyspray - The bug Killer!">The bug Killer!</a></h1>
      </div><!-- End of logo -->
    </div><!-- End of header -->
    <div id="content">
      <div id="bodyContent">
        <div class="install">
          <div id="stepbar">
            <h1>Progress</h1>
            <div <?php echo (!isset($_POST['action'])) ? 'class="step-on"' : ''; ?>>Pre-installation check</div>
            <div <?php echo (isset($_POST['action']) && ($_POST['action'] == 'database')) ? 'class="step-on"' : ''; ?>>Database setup</div>
            <div <?php echo (isset($_POST['action']) && ($_POST['action'] == 'administration')) ? 'class="step-on"' : ''; ?>>Administration</div>
            <div <?php echo (isset($_POST['action']) && ($_POST['action'] == 'complete')) ? 'class="step-on"' : ''; ?>>Install <?php echo Filters::noXSS($product_name); ?></div>
            <h1>Docs</h1>
            <div><a href="http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html" title="LGPL License" target="_blank" title="LGPL License">LGPL License</a></div>
            <div><a href="http://flyspray.org/manual:installation" title="Installation guide" target="_blank" title="User Manual">Install Guide</a></div>
            <div><a href="http://flyspray.org/development#installing_the_development_version" target="_blank" title="Developer's Manual">Developer's Manual</a> </div>
          </div><!-- End of stepbar -->		
            <?php echo $body; ?>

        </div><!-- End of install -->
        <div class="clr"></div>
      </div><!-- End of bodyContent -->
      <div class="clr"></div>
    </div><!-- End of content -->
    <div id="footer">
      <p>
        Flyspray <?php echo Filters::noXSS($version); ?> [Fly Flapper]<br />
        Copyright 2004-<?php echo Filters::noXSS(date('Y')); ?> &copy; The Flyspray team.  All rights reserved.
      </p>
    </div><!-- End of footer -->
  </div><!-- End of container -->
</div><!-- End of center -->
</body>
</html>
