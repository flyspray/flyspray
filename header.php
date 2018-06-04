<?php

// cant easly for the time being because of globals
require_once dirname(__FILE__) . '/includes/fix.inc.php';
require_once dirname(__FILE__) . '/includes/class.flyspray.php';
require_once dirname(__FILE__) . '/includes/constants.inc.php';
require_once BASEDIR . '/includes/i18n.inc.php';
require_once BASEDIR . '/includes/class.tpl.php';
require_once BASEDIR . '/includes/class.csp.php';

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
        Flyspray::redirect('setup/composertest.php');
        exit;
}

$csp= new ContentSecurityPolicy();
# deny everything first, then whitelist what is required.
$csp->add('default-src', "'none'");

// If it is empty, take the user to the setup page
if (!$conf) {
    Flyspray::redirect('setup/index.php');
}

$db = new Database();
$db->dbOpenFast($conf['database']);
$fs = new Flyspray();

// If version number of database and files do not match, run upgrader
if (Flyspray::base_version($fs->version) != Flyspray::base_version($fs->prefs['fs_ver'])) {
    Flyspray::redirect('setup/upgrade.php');
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

# load the correct $proj early also for checks on quickedit.php taskediting calls
if( (BASEDIR.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'callbacks'.DIRECTORY_SEPARATOR.'quickedit.php' == $_SERVER['SCRIPT_FILENAME']) && Post::num('task_id')){
        $result = $db->query('SELECT project_id FROM {tasks} WHERE task_id = ?', array(Post::num('task_id')));
        $project_id = $db->fetchOne($result);
}
# Any "do" mode that accepts a task_id field should be added here.
elseif (in_array(Req::val('do'), array('details', 'depends', 'editcomment'))) {
    if (Req::num('task_id')) {
        $result = $db->query('SELECT project_id FROM {tasks} WHERE task_id = ?', array(Req::num('task_id')));
        $project_id = $db->fetchOne($result);
    }
}

if (Req::val('do') =='pm' && Req::val('area')=='editgroup') {
    if (Req::num('id')) {
        $result = $db->query('SELECT project_id FROM {groups} WHERE group_id = ?', array(Req::num('id')));
        $project_id = $db->fetchOne($result);
    }
}

if (!isset($project_id)) {
        $project_id = $fs->prefs['default_project'];
        # Force default value if input format is not allowed
        if(is_array(Req::val('project'))) {
                Req::set('project', $fs->prefs['default_project']);
        }
        $project_id = Req::val('project', Req::val('project_id', $project_id));
}

$proj = new Project($project_id);
