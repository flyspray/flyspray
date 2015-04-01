<?php
@set_time_limit(0);
ini_set('memory_limit', '64M');

define('IN_FS', 1);
define('BASEDIR', dirname(__FILE__));
define('APPLICATION_PATH', dirname(BASEDIR));
define('OBJECTS_PATH', APPLICATION_PATH . '/includes');
define('TEMPLATE_FOLDER', BASEDIR . '/templates/');

require_once OBJECTS_PATH.'/i18n.inc.php';
class user{var $infos=array();};
class project{var $id=0;};
$user = new user;
$proj = new project;
load_translations();

# no caching to prevent old pages if user goes back and forth during install
header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset='utf-8'>
		<title>Flyspray Install - Third Party Packages needed - Step 3</title>
		<link media="screen" href="../themes/CleanFS/theme.css" rel="stylesheet" type="text/css" />
	</head>
	<body style="padding:2em;"><img src="../flyspray.png" style="display:block;margin:auto;">
	<?php
		if (ini_get('safe_mode') == 1) {
			echo '
				<h3>PHP safe_mode is enabled. We currently don\'t know how to run  the "php composer.phar install" from php web frontend under this circumstances.</h3>
				<h3>But lets test if we can workaround it with Perl:</h3>
				<a href="composerit2.pl" class="button">Test using Perl: composerit2.pl</a>';
		} else {
			echo '<h3>Step 3: Trying to install dependencies</h3>';
			# $argv=('install');
			# chdir('..');
			# echo '<pre>';
			# require 'composer.phar';
			# echo '</pre>';

			# without chdir('..');
			$phpexe='php';
			# TODO: autodetect the matching commandline php on the host matching the php version of the webserver
			# Any idea? Using $_SERVER['PHP_PEAR_SYSCONF_DIR'] or $_SERVER['PHPRC'] for detecting can help a bit, but weak hints.. 
			# This is just a temp hack for installing flyspray on xampp on Windows
			if (getenv('OS') == 'Windows_NT' && isset($_SERVER['PHPRC']) && strstr($_SERVER['PHPRC'], 'xampp')) {
				$phpexe=$_SERVER['PHPRC'].'\php.exe';
			}
			$cmd2 = $phpexe.' composer.phar --working-dir=.. install';

			# with chdir('..');
			#$cmd2 = 'php composer.phar install';

			echo $cmd2.'<br/><br/>';
			shell_exec($cmd2);
			echo '<strong>Done</strong>';

			echo '<h3>Step 4: Checking and cleaning:</h3>';
			if (is_readable('../vendor/autoload.php')) {
				echo 'Composer installation ok<br />';
			} else {
				echo 'Composer installation failed<br />';
			}
			if (is_file('composer.phar')) {
				unlink('composer.phar');
			}
			echo 'Cleanup made<br /><br />';
			echo '<a href="./index.php" class="button">Go back</a>';
		}
	?>
	</body>
</html>
