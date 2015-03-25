<?php

@set_time_limit(0);
ini_set('memory_limit', '64M');

define('IN_FS', 1);
define('BASEDIR', dirname(__FILE__));
define('APPLICATION_PATH', dirname(BASEDIR));
define('OBJECTS_PATH', APPLICATION_PATH . '/includes');
define('TEMPLATE_FOLDER', BASEDIR . '/templates/');

require_once OBJECTS_PATH.'/i18n.inc.php';
class user{var $infos=array();}; class project{var $id=0;};
$user=new user; $proj=new project;
load_translations();

# no caching to prevent old pages if user goes back and forth during install
header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

# Step 1 and 2 of composer install now working also with SAFE_MODE enabled in php5.3.*
#if(ini_get('safe_mode') == 1){
#	$composerit = 'composerit.pl'; // try it with perl scripts
#}else{
	$composerit = 'composerit.php'; // try it with php
#}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset='utf-8'>
		<title>Flyspray Install - Third Party Packages needed</title>
		<link media="screen" href="../themes/CleanFS/theme.css" rel="stylesheet" type="text/css" />
	</head>
	<body style="padding:2em;"><img src="../flyspray.png" style="display:block;margin:auto;">
		<h2>It seems you try to install a development version of Flyspray.</h2>
		<h2><?php echo L('needcomposer'); ?></h2>
		<a href="<?php echo $composerit; ?>" class="button" style="margin:auto;max-width:300px;text-align:center;display:block;font-size:2em;"><?php echo L('installcomposer'); ?></a>
		<p style="margin-top:50px;">
			In case the above solution doesn't work for you, use ssh to login to your server, move to the root directory of your unpacked flyspray sources and execute this:
		</p>
		<p>
			<pre>
  				curl -sS https://getcomposer.org/installer | php
  				php composer.phar install
			</pre>
		</p>
		<p>
			Or take an official release, which contains all needed external packages bundled.
		</p>
		<h2>README.md</h2>
		<div id="content">
			<pre>
				<?php echo file_get_contents('../README.md'); ?>
			</pre>
		</div>
	</body>
</html>
