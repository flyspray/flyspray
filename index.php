<?php
/*
   This is the main script that everything else is included
   in.  Mostly what it does is check the user permissions
   to see what they have access to.
*/
define('IN_FS', true);

require_once(dirname(__FILE__).'/header.php');

// Get available do-modes
$modes = str_replace('.php', '', array_map('basename', glob_compat(BASEDIR ."/scripts/*.php")));

$do = Req::enum('do', $modes, $proj->prefs['default_entry']);

if ($do == 'admin' && Req::has('switch') && Req::val('project') != '0') {
    $do = 'pm';
} elseif ($do == 'pm' && Req::has('switch') && Req::val('project') == '0') {
    $do = 'admin';
} elseif (Req::has('show') || (Req::has('switch') && $do == 'details')
      || ($do == 'newtask' && Req::val('project') == '0'))  {
	$do = 'index';
} elseif (Req::has('code')) {
	$_SESSION['oauth_provider'] = 'microsoft';
	$do = 'oauth';
} elseif( Req::has('do') && Req::val('do') == 'tasklist') {
	$do='index';
}

// supertask_id for add new sub-task
$supertask_id = 0;
if (Req::has('supertask')) {
    $supertask_id = Req::val('supertask');
}


/* permission stuff */
if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')) {
    $user = new User(Cookie::val('flyspray_userid'), $proj);
    $user->check_account_ok();
    $user->save_search($do);
} else {
    $user = new User(0, $proj);
}



if (Get::val('getfile')) {
    // If a file was requested, deliver it
    $result = $db->Query("SELECT  t.project_id,
                                  a.orig_name, a.file_name, a.file_type, t.*
                            FROM  {attachments} a
                      INNER JOIN  {tasks}       t ON a.task_id = t.task_id
                           WHERE  attachment_id = ?", array(Get::val('getfile')));
    $task = $db->FetchRow($result);
    list($proj_id, $orig_name, $file_name, $file_type) = $task;

    // Check if file exists, and user permission to access it!
    if (!is_file(BASEDIR . "/attachments/$file_name")) {
        header('HTTP/1.1 410 Gone');
        echo 'File does not exist anymore.';
        exit();
    }

    if ($user->can_view_task($task))
    {
        $path = BASEDIR . "/attachments/$file_name";

        header('Pragma: public');
        header("Content-type: $file_type");
        header('Content-Disposition: filename="'.$orig_name.'"');
        header('Content-transfer-encoding: binary');
        header('Content-length: ' . filesize($path));

        readfile($path);
        exit();
    }
    else {
        Flyspray::show_error(1);
    }
    exit;
}

// Load translations
load_translations();

/*******************************************************************************/
/* Here begins the deep flyspray : html rendering                              */
/*******************************************************************************/
# no cache headers are now in header.php!

// see http://www.w3.org/TR/html401/present/styles.html#h-14.2.1
header('Content-Style-Type: text/css');
header('Content-type: text/html; charset=utf-8');

if ($conf['general']['output_buffering'] == 'gzip' && extension_loaded('zlib'))
{
    // Start Output Buffering and gzip encoding if setting is present.
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

$page = new FSTpl();

// make sure people are not attempting to manually fiddle with projects they are not allowed to play with
if (Req::has('project') && Req::val('project') != 0 && !$user->can_select_project(Req::val('project'))) {
    Flyspray::show_error( L('nopermission') );
    exit;
}

if ($show_task = Get::val('show_task')) {
    // If someone used the 'show task' form, redirect them
    if (is_numeric($show_task)) {
        Flyspray::Redirect( CreateURL('details', $show_task) );
    } else {
        Flyspray::Redirect( $baseurl . '?string=' .  $show_task);
    }
}

if (Flyspray::requestDuplicated()) {
    // Check that this page isn't being submitted twice
    Flyspray::show_error(3);
}

# handle all forms request that modify data

if (Req::has('action')) {
    # enforcing if the form sent the correct anti csrf token
    # only allow token by post
    if( !Post::has('csrftoken') ){
        die('missingtoken');
    }elseif( Post::val('csrftoken')==$_SESSION['csrftoken']){
        require_once(BASEDIR . '/includes/modify.inc.php');
    }else{
        die('wrongtoken');
    }
}

# start collecting infos for the answer page

if ($proj->id && $user->perms('manage_project')) {
    // Find out if there are any PM requests wanting attention
    $sql = $db->Query(
            'SELECT COUNT(*) FROM {admin_requests} WHERE project_id = ? AND resolved_by = 0',
            array($proj->id));
    list($count) = $db->fetchRow($sql);

    $page->assign('pm_pendingreq_num', $count);
}
if ($user->perms('is_admin')) {
    $sql = $db->Query(
    	    'SELECT COUNT(*) FROM {admin_requests} WHERE request_type = 3 AND project_id = 0 AND resolved_by = 0');
    list($count) = $db->fetchRow($sql);
    $page->assign('admin_pendingreq_num', $count);
}

# a bit hacky: First 3 MUST be project_id, project_title, project_is_active in this order!
# This first 3 indexes are used by tpl_options currently..
# removed upper(project_title) for sorting, should be handled by database collation (utf8_general_ci)
$sql = $db->Query('
	SELECT project_id, project_title, project_is_active, others_view, default_entry
	FROM {projects}
	ORDER BY project_is_active DESC, project_title'
);

# new: project_id as index for easier access, needs testing and maybe simplification 
# similiar situation also includes/class.flyspray.php function listProjects()
$sres=$db->FetchAllArray($sql);
foreach($sres as $p){
	$prs[$p['project_id']]=$p;
}
$fs->projects = array_filter($prs, array($user, 'can_select_project'));


// Get e-mail addresses of the admins
if ($user->isAnon() && !$fs->prefs['user_notify']) {
    $sql = $db->Query('SELECT email_address
                         FROM {users} u
                    LEFT JOIN {users_in_groups} g ON u.user_id = g.user_id
                        WHERE g.group_id = 1');
    $page->assign('admin_emails', array_map(create_function('$x', 'return str_replace("@", "#", $x);'), $db->fetchCol($sql)));
}

// default title
$page->setTitle($fs->prefs['page_title'] . $proj->prefs['project_title']);

$page->assign('do', $do);
$page->assign('supertask_id', $supertask_id);

$page->pushTpl('header.tpl');

if (!defined('NO_DO')) {
    require_once(BASEDIR . "/scripts/$do.php");
} else{
    # not nicest solution, NO_DO currently only used on register actions 
    $page->pushTpl('register.ok.tpl');
}
$page->pushTpl('footer.tpl');
$page->setTheme($proj->prefs['theme_style']);
$page->render();

if(isset($_SESSION)) {
// remove dupe data on error, since no submission happened
    if (isset($_SESSION['ERROR']) && isset($_SESSION['requests_hash'])) {
        $currentrequest = md5(serialize($_POST));
        unset($_SESSION['requests_hash'][$currentrequest]);
    }
    unset($_SESSION['ERROR'], $_SESSION['ERRORS'], $_SESSION['SUCCESS']);
}
