<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo Filters::noXSS($title . '  ' . $product_name); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="styles/setup.css" type="text/css" media="screen" />
<?php echo $headers; ?>
</head>
<body>
<div id="header">
  <div id="logo">
    <h1><a href="<?php echo Filters::noXSS($index); ?>" title="Flyspray - <?php echo Filters::noXSS(L('slogan')); ?>"><?php echo Filters::noXSS(L('slogan')); ?></a></h1>
  </div><!-- End of logo -->
</div><!-- End of header -->
<div id="content">
  <div id="stepbar" title="<?php echo Filters::noXSS(L('progress')); ?>">
    <!-- <div><?php echo Filters::noXSS(L('progress')); ?></div> -->
    <div class="done">3rd party libs</div>
    <div<?php
    if(!isset($_POST['action'])){
      echo ' class="step-on"';
    } elseif( $_POST['action'] == 'database' || $_POST['action'] == 'administration' || $_POST['action'] == 'complete' ){
      echo ' class="done"';
    } ?>><?php echo Filters::noXSS(L('preinstallcheck')); ?></div>
    <div<?php
    if(isset($_POST['action'])){
      if( $_POST['action'] == 'database' ){
        echo ' class="step-on"';
      } elseif( $_POST['action'] == 'administration' || $_POST['action'] == 'complete' ){
        echo ' class="done"';
      }
    }
    ?>><?php echo Filters::noXSS(L('databasesetup')); ?></div>
    <div<?php
    if(isset($_POST['action'])){
      if($_POST['action'] == 'administration'){
        echo ' class="step-on"';
      } elseif($_POST['action'] == 'complete'){
        echo ' class="done"';
      }
    } ?>><?php echo Filters::noXSS(L('administration')); ?></div>
    <div<?php echo (isset($_POST['action']) && ($_POST['action'] == 'complete')) ? ' class="step-on"' : ''; ?>><?php echo Filters::noXSS(L('installflyspray')); ?></div>
  </div>
  <?php echo $body; ?>
</div><!-- End of content -->
<div id="footer">
  <ul id="docs">
    <li><a href="http://www.gnu.org/licenses/old-licenses/lgpl-2.1.html" title="<?php echo Filters::noXSS(L('lgpllicense')); ?>" target="_blank"><?php echo Filters::noXSS(L('lgpllicense')); ?></a></li>
    <li><a href="http://flyspray.org/manual" title="<?php echo Filters::noXSS(L('installationguide')); ?>" target="_blank"><?php echo Filters::noXSS(L('installationguide')); ?></a></li>
  </ul>
  <p>Flyspray <?php echo Filters::noXSS($version); ?><br />
  Copyright 2004-<?php echo Filters::noXSS(date('Y')); ?> &copy; The Flyspray team.  All rights reserved.
  </p>
</div><!-- End of footer -->
</body>
</html>
