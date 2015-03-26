<?php
@set_time_limit(0);
ini_set('memory_limit', '64M');

$debug = false;

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
		<title>Flyspray Install - Third Party Packages needed</title>
		<link media="screen" href="../themes/CleanFS/theme.css" rel="stylesheet" type="text/css" />
	</head>
	<body style="padding:2em;"><img src="../flyspray.png" style="display:block;margin:auto;">
		<h3>Step 1: Trying to download Composer:</h3>
		<?php
			#chdir('..');
			# This works also in php5.3.* with SAFE_MODE enabled
			if (ini_get('safe_mode') == 1) {
				$composerfile = file_get_contents('https://getcomposer.org/installer');
				file_put_contents('composer.phar', $composerfile);
			}
			else {
				shell_exec('php -r "readfile(\'https://getcomposer.org/installer\');" | php');
			}

			if (!is_readable('composer.phar')) {
				echo 'Composer installer download failed! Please consider downloading vendors directly from Flyspray support website';
				exit;
			}
			else {
				echo 'Download done'.'<br><br>';
			}

			echo '<h3>Step 2: Trying to load composer.phar into the running php script</h3>';

			# Now lets execute it directly by loading the file composerinstaller. :-)
			# XXX lol PHP5.3.* with SAFE_MODE enabled forces us to be 'unsafe' ...
			if (ini_get('safe_mode') == 1) {
				#$argv=array(); # just for avoiding warnings
				$argv = array('--disable-tls'); # just for avoiding warnings
			} else {
				$argv = array();
				putenv('COMPOSER_HOME=.'); # fake env var; aww not working in SAFE_MODE ! Do we need to automatic patch composerinstaller for running without HOME or COMPOSER_HOME?
			}

			echo 'Done'.'<br><br>';

			if ($debug) {
				echo '*DEBUG MODE*<br><br>';
				echo '<p>Wait a few seconds until composerinstaller put his output under the button. If looking good go to step 3.</p>';
				echo '<a href="composerit2.php" class="button" style="padding:1em;font-size:1em">Install dependencies</a>';
				echo '<pre>';
				require 'composer.phar';
				# Ok, composerinstaller exits itself, so no more code needed here, but it looks more complete :-)
				echo '</pre>';
			}
			else {
				echo '<a href="composerit2.php" class="button" style="padding:1em;font-size:1em">Install dependencies</a>';
			}
		?>
	</body>
</html>
