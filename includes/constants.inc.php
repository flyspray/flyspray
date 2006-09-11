<?php

define('BASEDIR', dirname(dirname(__FILE__)));

// Change this line if you move flyspray.conf.php elsewhere
$conf    = @parse_ini_file(BASEDIR . '/flyspray.conf.php', true);

// $baseurl
// htmlspecialchars because PHP_SELF is user submitted data, and can be used as an XSS vector.
if (!isset($webdir)) {
    $webdir = dirname(htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'utf-8'));
    if (substr($webdir, -9) == 'index.php') {
        $webdir = dirname($webdir);
    }
}

$baseurl = rtrim(Flyspray::absoluteURI($webdir),'/\\') . '/' ;

$path_to_plugin = BASEDIR . '/plugins/' . trim($conf['general']['syntax_plugin']) . '/' 
                  . trim($conf['general']['syntax_plugin']) . '_constants.inc.php';

if (is_readable($path_to_plugin)) {
    include($path_to_plugin);
}

define('NOTIFY_TASK_OPENED',      1);
define('NOTIFY_TASK_CHANGED',     2);
define('NOTIFY_TASK_CLOSED',      3);
define('NOTIFY_TASK_REOPENED',    4);
define('NOTIFY_DEP_ADDED',        5);
define('NOTIFY_DEP_REMOVED',      6);
define('NOTIFY_COMMENT_ADDED',    7);
define('NOTIFY_ATT_ADDED',        8);
define('NOTIFY_REL_ADDED',        9);
define('NOTIFY_OWNERSHIP',       10);
define('NOTIFY_CONFIRMATION',    11);
define('NOTIFY_PM_REQUEST',      12);
define('NOTIFY_PM_DENY_REQUEST', 13);
define('NOTIFY_NEW_ASSIGNEE',    14);
define('NOTIFY_REV_DEP',         15);
define('NOTIFY_REV_DEP_REMOVED', 16);
define('NOTIFY_ADDED_ASSIGNEES', 17);
define('NOTIFY_ANON_TASK',       18);
define('NOTIFY_PW_CHANGE',       19);
define('NOTIFY_NEW_USER',        20);

define('NOTIFY_EMAIL',            1);
define('NOTIFY_JABBER',           2);
define('NOTIFY_BOTH',             3);

define('STATUS_UNCONFIRMED',      1);
define('STATUS_NEW',              2);
define('STATUS_ASSIGNED',         3);

// developers or advanced users only
//define('DEBUG_SQL',true);
//define('JABBER_DEBUG', true);
//define('JABBER_DEBUG_FILE''/path/to/my/debug/file');
?>
