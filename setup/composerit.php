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
		<title>Flyspray Install - Third Party Packages needed</title>
		<link media="screen" href="../themes/CleanFS/theme.css" rel="stylesheet" type="text/css" />
	</head>
	<body style="padding:2em;"><img src="../flyspray.png" style="display:block;margin:auto;">
		<h3>Step 1: Trying to download Composer</h3>
<?php
		if (ini_get('safe_mode') == 1) {
			$composerfile = file_get_contents('https://getcomposer.org/installer');
			file_put_contents('composerinstaller', $composerfile);
			echo 'Download done'.'<br>';
			$argv = array('--disable-tls'); # just for avoiding warnings
			?>
			<h3>Step 2: Trying to load composerinstaller into the running php script</h3>
			<p>Wait a few seconds until composerinstaller put his output under the button. Once the output looks good, try installing the dependencies using the button.</p>
			<a href="composerit2.php" class="button" style="padding:1em;font-size:1em">Install dependencies</a>
			<pre>
			<?php
			require 'composerinstaller';
			# Ok, composerinstaller exits itself, so no more code needed here, but it looks more complete :-)
			echo '</pre>';
		} else {
			$phpexe='php';
			# TODO: autodetect the matching commandline php on the host matching the php version of the webserver
			# Any idea? Using $_SERVER['PHP_PEAR_SYSCONF_DIR'] or $_SERVER['PHPRC'] for detecting can help a bit, but weak hints..
			# This is just a temp hack for installing flyspray on xampp on Windows
			if (getenv('OS') == 'Windows_NT' && isset($_SERVER['PHPRC']) && strstr($_SERVER['PHPRC'], 'xampp')) {
				$phpexe=$_SERVER['PHPRC'].'\php.exe';
			}
			shell_exec($phpexe.' -r "readfile(\'https://getcomposer.org/installer\');" | '.$phpexe);
			if (!is_readable('composer.phar')) {
				die('Composer installer download failed! Please consider downloading vendors directly from Flyspray support website');
			}
			echo 'Successfully downloaded Composer.<br /><br />';
			echo '<a href="composerit2.php" class="button" style="padding:1em;font-size:1em">Try to install dependencies</a>';
		}
?>
</body>
</html>
