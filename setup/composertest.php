<?php

define('IN_FS', 1);
define('BASEDIR', dirname(__FILE__));
define('APPLICATION_PATH', dirname(BASEDIR));
define('OBJECTS_PATH', APPLICATION_PATH . '/includes');
define('TEMPLATE_FOLDER', BASEDIR . '/templates/');

require_once OBJECTS_PATH.'/i18n.inc.php';

/** dummy class */
class user
{
	public $infos=array();
}

/** dummy class */
class project
{
	public $id=0;
}

$user=new user;
$proj=new project;
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
	<link rel="stylesheet" href="styles/setup.css" type="text/css" media="screen">	
</head>
<body>
<div id="header">
  <div id="logo">
    <h1><a href="./" title="Flyspray - The Bug Killer!">The Bug Killer!</a></h1>
  </div>
</div>
<div id="content">
<h2>It seems you try to install a development version of Flyspray.</h2>
<h2><?php echo L('needcomposer'); ?></h2>
<p style="margin-top:50px;">Use ssh to login to your server, move to the root directory of your unpacked flyspray sources and execute this:</p>
<pre>
curl -sS https://getcomposer.org/installer | php
php composer.phar install
</pre>

<div class="error">
	<h4>Shared Hostings</h4>
	<p>If you are on a shared hosting, there are probably different php versions available. The hosting companies name them often like <b>php5.4</b>, <b>php5.5-cli</b> or <b>php-cgi-7.0</b>. Choose the best matching php-version for your Hosting (should ideally match that of what the webserver uses). To see available php versions on the commandline type</p>
	<pre><strong>php</strong> <kbd class="key">tab</kbd> <kbd class="key">tab</kbd></pre>
	<p><kbd>tab</kbd> <kbd>tab</kbd> is autocompletion on bash, so it shows all executable that start with <strong>php</strong>.</p>
	<p>Lets say the webserver uses PHP 5.6 by default, than a <b>php5.6</b> you found on the commandline is a good choice:</p>
	<pre>curl -sS https://getcomposer.org/installer | php5.6
	php5.6 composer.phar install
	</pre>
</div>

<p>Or take an official release, which contains all needed external packages bundled.</p>

<a href="./" class="button positive">Retry</a>

<h2>README.md</h2>
<pre>
<?php echo file_get_contents('../README.md'); ?>
</pre>
</div>
</body>
</html>
