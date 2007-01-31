<?php

// As of 24 July 2004, all editable config is stored in flyspray.conf.php
// There should be no reason to edit this file anymore, except if you
// move flyspray.conf.php to a directory where a browser can't access it.
// (RECOMMENDED).

require_once dirname(__FILE__) . '/includes/fix.inc.php';
require_once dirname(__FILE__) . '/includes/class.flyspray.php';
require_once dirname(__FILE__) . '/includes/constants.inc.php';
require_once BASEDIR . '/includes/i18n.inc.php';

// If it is empty,take the user to the setup page

if (!$conf) {
    Flyspray::Redirect('setup/index.php');
}

require_once BASEDIR . '/includes/class.gpc.php';
require_once BASEDIR . '/includes/utf8.inc.php';
require_once BASEDIR . '/includes/class.database.php';
require_once BASEDIR . '/includes/class.backend.php';
require_once BASEDIR . '/includes/class.project.php';
require_once BASEDIR . '/includes/class.user.php';
require_once BASEDIR . '/includes/class.tpl.php';

$db = new Database;
$db->dbOpenFast($conf['database']);
$fs = new Flyspray;

if (is_readable(BASEDIR . '/setup/index.php') && strpos($fs->version, 'dev') === false) {
    die('Please empty the folder "' . BASEDIR . DIRECTORY_SEPARATOR . "setup  before you start using Flyspray.\n".
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

// Load translations
load_translations();

for ($i = 6; $i >= 1; $i--) {
    $fs->priorities[$i] = L('priority' . $i);
}
for ($i = 5; $i >= 1; $i--) {
    $fs->severities[$i] = L('severity' . $i);
}

?>
