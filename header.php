<?php

// cant easly for the time being because of globals
require_once dirname(__FILE__) . '/includes/fix.inc.php';
require_once dirname(__FILE__) . '/includes/class.flyspray.php';
require_once dirname(__FILE__) . '/includes/constants.inc.php';
require_once BASEDIR . '/includes/i18n.inc.php';
require_once BASEDIR . '/includes/class.tpl.php';

// Get the translation for the wrapper page (this page)
setlocale(LC_ALL, str_replace('-', '_', L('locale')) . '.utf8');

// make browsers back button work
header('Expires: -1');
header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

if(is_readable(BASEDIR . '/vendor/autoload.php')){
        // Use composer autoloader
        require 'vendor/autoload.php';
}else{
        Flyspray::Redirect('setup/composertest.php');
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
        die('<div style="text-align:center;padding:20px;font-family:sans-serif;font-size:16px;">
<p>If you are upgrading, please <a href="setup/upgrade.php"
style="
margin:2em;
background-color: white;
border: 1px solid #bbb;
border-radius: 4px;
box-shadow: 0 1px 1px #ddd;
color: #565656;
cursor: pointer;
display: inline-block;
font-family: sans-serif;
font-size: 100%;
font-weight: bold;
line-height: 130%;
padding: 8px 13px 8px 10px;
text-decoration: none;
">Go to the upgrade settings</a></p>
<p>If you have finished Flyspray installation or an upgrade, please <a href="setup/cleanupaftersetup.php"
style="
margin:2em;
background-color: white;
border: 1px solid #bbb;
border-radius: 4px;
box-shadow: 0 1px 1px #ddd;
color: #565656;
cursor: pointer;
display: inline-block;
font-family: sans-serif;
font-size: 100%;
font-weight: bold;
line-height: 130%;
padding: 8px 13px 8px 10px;
text-decoration: none;
">Remove the folder '.DIRECTORY_SEPARATOR.'setup</a> before you start using Flyspray</p>
');
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
# no more project cookie!
#$proj->setCookie();


