<?php

// cant easly for the time being because of globals
require_once dirname(__FILE__) . '/includes/fix.inc.php';
require_once dirname(__FILE__) . '/includes/class.flyspray.php';
require_once dirname(__FILE__) . '/includes/constants.inc.php';
require_once BASEDIR . '/includes/i18n.inc.php';
require_once BASEDIR . '/includes/class.tpl.php';

// Get the translation for the wrapper page (this page)
setlocale(LC_ALL, str_replace('-', '_', L('locale')) . '.utf8');

if(is_readable(BASEDIR . '/vendor/autoload.php')){
        // Use composer autoloader
        require 'vendor/autoload.php';
}else{
        # use the translations from the setup/lang/, but too late for setting BASEDIR to setup dir
        #load_translations();
        #die (eL('needcomposer'));
        echo '<!DOCTYPE html>
<html>
<head>
<title>Flyspray Install - Third Party Packages needed</title>
<link media="screen" href="themes/CleanFS/theme.css" rel="stylesheet" type="text/css" />
</head>
<body style="padding:2em"><img src="flyspray.png" style="display:block;margin:auto;">
<h1>It seems you try to install a development version of Flyspray.</h1>
<h2>You need some required libraries installed by composer. Use ssh to login to your server, move to the root directory of your unpacked flyspray sources and execute this:</h2>
<h3><pre>
    curl -sS https://getcomposer.org/installer | php
    php composer.phar install
</pre></h3>
<h2>Or take an official release, which contains all needed external packages bundled.</h2>';

echo '<div id="content"><pre>'.file_get_contents('README.md').'</pre></div>';
echo '</body></html>';
        exit;
}

// If it is empty, take the user to the setup page
if (!$conf) {
    Flyspray::Redirect('setup/index.php');
}

//FIXME: This is currently a workaround for the fact that parts of the code/templates use i.e. "taskid" and "task_id" for the same thing. This should be fixed cleanly, means a bit of work though.
if      (isset($_GET["task_id"])) $_GET["taskid"]  = $_GET["task_id"];
else if (isset($_GET["taskid"]))  $_GET["task_id"] = $_GET["taskid"];
if      (isset($_POST["task_id"])) $_POST["taskid"]  = $_POST["task_id"];
else if (isset($_POST["taskid"]))  $_POST["task_id"] = $_POST["taskid"];
if      (isset($_REQUEST["task_id"])) $_REQUEST["taskid"]  = $_REQUEST["task_id"];
else if (isset($_REQUEST["taskid"]))  $_REQUEST["task_id"] = $_REQUEST["taskid"];


$db = new Database();
$db->dbOpenFast($conf['database']);
$fs = new Flyspray();

// If version number of database and files do not match, run upgrader
if (Flyspray::base_version($fs->version) != Flyspray::base_version($fs->prefs['fs_ver'])) {
    Flyspray::Redirect('setup/upgrade.php');
}

if (is_readable(BASEDIR . '/setup/index.php') && strpos($fs->version, 'dev') === false) {
    die('Please empty the folder "' . BASEDIR . DIRECTORY_SEPARATOR . "setup\"  before you start using Flyspray.\n".
        "If you are upgrading, please go to the setup directory and launch upgrade.php");
}

// Any "do" mode that accepts a task_id or id field should be added here.
if (in_array(Req::val('do'), array('details', 'depends'))) {
    if (Req::num('task_id')) {
        $result = $db->Query('SELECT  project_id
                                FROM  {tasks} WHERE task_id = ?', array(Req::num('task_id')));
        $project_id = $db->FetchOne($result);
    }
}

if (!isset($project_id)) {
    // Determine which project we want to see
    if (($project_id = Cookie::val('flyspray_project')) == '') {
        $project_id = $fs->prefs['default_project'];
    }
    $project_id = Req::val('project', Req::val('project_id', $project_id));
}

$proj = new Project($project_id);
$proj->setCookie();


