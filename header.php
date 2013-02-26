<?php

//This function does not bleong here

//prints web readable debug output when not in CLI mode
//	exact same prototype as PHPs var_dump
function debug_dump(){
	if(php_sapi_name() != 'cli'){
		//if(ob_start()){
			echo '<pre>';
			call_user_func_array('var_dump',func_get_args());
			echo '</pre>';
			//Tpl::_get()->addDebug(ob_get_contents());
			//ob_end_clean();
		//}
	} else {
		call_user_func_array('var_dump',func_get_args());
	}
}


require_once dirname(__FILE__) . '/includes/fix.inc.php';
require_once dirname(__FILE__) . '/includes/class.flyspray.php';
require_once dirname(__FILE__) . '/includes/constants.inc.php';
require_once BASEDIR . '/includes/i18n.inc.php';

// Get the translation for the wrapper page (this page)
setlocale(LC_ALL, str_replace('-', '_', L('locale')) . '.utf8');

// If it is empty, take the user to the setup page
if (!$conf) {
    Flyspray::Redirect('setup/index.php');
}

require_once BASEDIR . '/includes/class.gpc.php';
require_once BASEDIR . '/includes/utf8.inc.php';
require_once BASEDIR . '/includes/class.backend.php';
require_once BASEDIR . '/includes/class.project.php';
require_once BASEDIR . '/includes/class.user.php';
require_once BASEDIR . '/includes/class.tpl.php';
require_once BASEDIR . '/includes/db.php';

//---------------------------------------------------------
//startup the database
//---------------------------------------------------------

//figure out driver (backwards compat for incompat driver names
if(strpos($conf['database']['dbtype'],'mysql') !== false) $dbtype = 'mysql';
else $dbtype = $conf['database']['dbtype'];
//set the DB_PREFIX constant
define('DB_PREFIX',$conf['database']['dbprefix']);
//connect to the database
Db::_get()->setConfig(array(
	 'driver'		=>	$dbtype
	,'host'			=>	$conf['database']['dbhost']
	,'port'			=>	3306
	,'user'			=>	$conf['database']['dbuser']
	,'password'		=>	$conf['database']['dbpass']
	,'database'		=>	$conf['database']['dbname']
));
Db::_get()->connect();

//start the FS main class
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


