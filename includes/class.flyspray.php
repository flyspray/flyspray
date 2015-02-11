<?php
/**
 * Flyspray
 *
 * Flyspray class
 *
 * This script contains all the functions we use often in
 * Flyspray to do miscellaneous things.
 *
 * @license http://opensource.org/licenses/lgpl-license.php Lesser GNU Public License
 * @package flyspray
 * @author Tony Collins
 * @author Florian Schmitz
 * @author Cristian Rodriguez
 */

class Flyspray
{

    /**
     * Current Flyspray version. Change this for each release.  Don't forget!
     * @access public
     * @var string
     */
    public $version = '1.0 dev';

    /**
     * Flyspray preferences
     * @access public
     * @var array
     */
    public $prefs   = array();

    /**
     * Max. file size for file uploads. 0 = no uploads allowed
     * @access public
     * @var integer
     */
    public $max_file_size = 0;

    /**
     * List of projects the user is allowed to view
     * @access public
     * @var array
     */
    public $projects = array();

    /**
     * List of severities. Loaded in i18n.inc.php
     * @access public
     * @var array
     */
    public $severities = array();

    /**
     * List of priorities. Loaded in i18n.inc.php
     * @access public
     * @var array
     */
    public $priorities = array();

    // Application-wide preferences {{{
    /**
     * Constructor, starts session, loads settings
     * @access private
     * @return void
     * @version 1.0
     */
    public function __construct()
    {
        global $db;

        $this->startSession();

        $res = $db->Query('SELECT pref_name, pref_value FROM {prefs}');

        while ($row = $db->FetchRow($res)) {
            $this->prefs[$row['pref_name']] = $row['pref_value'];
        }

        $this->setDefaultTimezone();

        $sizes = array();
        foreach (array(ini_get('memory_limit'), ini_get('post_max_size'), ini_get('upload_max_filesize')) as $val) {
            if (!$val) {
                continue;
            }

            $val = trim($val);
            $last = strtolower($val{strlen($val)-1});
            switch ($last) {
                // The 'G' modifier is available since PHP 5.1.0
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }

            $sizes[] = $val;
        }
        clearstatcache();
        $func = create_function('$x', 'return @is_file($x . "/index.html") && is_writable($x);');
        $this->max_file_size = ((bool) ini_get('file_uploads') && $func(BASEDIR . '/attachments')) ? round((min($sizes)/1024/1024), 1) : 0;
    } // }}}

    protected function setDefaultTimezone()
    {
        $default_timezone = isset($this->prefs['default_timezone']) && !empty($this->prefs['default_timezone']) ? $this->prefs['default_timezone'] : 'UTC';
        // set the default time zone - this will be redefined as we go
        define('DEFAULT_TIMEZONE',$default_timezone);
        date_default_timezone_set(DEFAULT_TIMEZONE);
    }

    public static function base_version($version)
    {
        if (strpos($version, ' ') === false) {
            return $version;
        }
        return substr($version, 0, strpos($version, ' '));
    }

    public static function get_config_path($basedir = BASEDIR)
    {
        $cfile = $basedir . '/flyspray.conf.php';
        if (is_readable($hostconfig = sprintf('%s/%s.conf.php', $basedir, $_SERVER['SERVER_NAME']))) {
            $cfile = $hostconfig;
        }
        return $cfile;
    }


    // {{{ Redirect to $url
    /**
     * Redirects the browser to the page in $url
     * This function is based on PEAR HTTP class
     * @param string $url
     * @param bool $exit
     * @param bool $rfc2616
     * @license BSD
     * @access public static
     * @return bool
     * @version 1.0
     */
    public static function Redirect($url, $exit = true, $rfc2616 = true)
    {

        @ob_clean();

        if (isset($_SESSION) && count($_SESSION)) {
            session_write_close();
        }

        if (headers_sent()) {
            die('Headers are already sent, this should not have happened. Please inform Flyspray developers.');
        }

        $url = FlySpray::absoluteURI($url);

        header('Location: '. $url);

        if ($rfc2616 && isset($_SERVER['REQUEST_METHOD']) &&
            $_SERVER['REQUEST_METHOD'] != 'HEAD') {
            $url = htmlspecialchars($url, ENT_QUOTES, 'utf-8');
            printf('%s to: <a href="%s">%s</a>.', eL('Redirect'), $url, $url);
        }
        if ($exit) {
            exit;
        }

        return true;
    } // }}}

    /**
     * Absolute URI (This function is part of PEAR::HTTP licensed under the BSD) {{{
     *
     * This function returns the absolute URI for the partial URL passed.
     * The current scheme (HTTP/HTTPS), host server, port, current script
     * location are used if necessary to resolve any relative URLs.
     *
     * Offsets potentially created by PATH_INFO are taken care of to resolve
     * relative URLs to the current script.
     *
     * You can choose a new protocol while resolving the URI.  This is
     * particularly useful when redirecting a web browser using relative URIs
     * and to switch from HTTP to HTTPS, or vice-versa, at the same time.
     *
     * @author  Philippe Jausions <Philippe.Jausions@11abacus.com>
     * @static
     * @access  public
     * @return  string  The absolute URI.
     * @param   string  $url Absolute or relative URI the redirect should go to.
     * @param   string  $protocol Protocol to use when redirecting URIs.
     * @param   integer $port A new port number.
     */
    public static function absoluteURI($url = null, $protocol = null, $port = null)
    {
        // filter CR/LF
        $url = str_replace(array("\r", "\n"), ' ', $url);

        // Mess around with already absolute URIs
        if (preg_match('!^([a-z0-9]+)://!i', $url)) {
            if (empty($protocol) && empty($port)) {
                return $url;
            }
            if (!empty($protocol)) {
                $url = $protocol .':'. end($array = explode(':', $url, 2));
            }
            if (!empty($port)) {
                $url = preg_replace('!^(([a-z0-9]+)://[^/:]+)(:[\d]+)?!i',
                    '\1:'. $port, $url);
            }
            return $url;
        }

        $host = 'localhost';
        if (!empty($_SERVER['HTTP_HOST'])) {
            list($host) = explode(':', $_SERVER['HTTP_HOST']);

            if (strpos($_SERVER['HTTP_HOST'], ':') !== false && !isset($port)) {
                $port = explode(':', $_SERVER['HTTP_HOST']);
            }
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            list($host) = explode(':', $_SERVER['SERVER_NAME']);
        }

        if (empty($protocol)) {
            if (isset($_SERVER['HTTPS']) && !strcasecmp($_SERVER['HTTPS'], 'on')) {
                $protocol = 'https';
            } else {
                $protocol = 'http';
            }
            if (!isset($port) || $port != intval($port)) {
                $port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;
            }
        }

        if ($protocol == 'http' && $port == 80) {
            unset($port);
        }
        if ($protocol == 'https' && $port == 443) {
            unset($port);
        }

        $server = $protocol .'://'. $host . (isset($port) ? ':'. $port : '');


        if (!strlen($url) || $url{0} == '?' || $url{0} == '#') {
            $uri = isset($_SERVER['REQUEST_URI']) ?
                $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
            if ($url && $url{0} == '?' && false !== ($q = strpos($uri, '?'))) {
                $url = substr($uri, 0, $q) . $url;
            } else {
                $url = $uri . $url;
            }
        }

        if ($url{0} == '/') {
            return $server . $url;
        }

        // Check for PATH_INFO
        if (isset($_SERVER['PATH_INFO']) && strlen($_SERVER['PATH_INFO']) &&
                $_SERVER['PHP_SELF'] != $_SERVER['PATH_INFO']) {
            $path = dirname(substr($_SERVER['PHP_SELF'], 0, -strlen($_SERVER['PATH_INFO'])));
        } else {
            $path = dirname($_SERVER['PHP_SELF']);
        }

        if (substr($path = strtr($path, '\\', '/'), -1) != '/') {
            $path .= '/';
        }

        return $server . $path . $url;
    } // }}}

    // Duplicate submission check {{{
    /**
     * Test to see if user resubmitted a form.
     * Checks only newtask and addcomment actions.
     * @return bool true if user has submitted the same action within less than 6 hours, false otherwise
     * @access public static
     * @version 1.0
     */
    public static function requestDuplicated()
    {
        // garbage collection -- clean entries older than 6 hrs
        $now = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
        if (!empty($_SESSION['requests_hash'])) {
            foreach ($_SESSION['requests_hash'] as $key => $val) {
                if ($val < $now-6*60*60) {
                    unset($_SESSION['requests_hash'][$key]);
                }
            }
        }

      if (count($_POST)) {

        if (preg_match('/^newtask.newtask|details.addcomment$/', Post::val('action', '')))
        {
            $currentrequest = md5(serialize($_POST));
            if (!empty($_SESSION['requests_hash'][$currentrequest])) {
                return true;
            }
            $_SESSION['requests_hash'][$currentrequest] = time();
        }
      }
        return false;
    } // }}}
    // Retrieve task details {{{
    /**
     * Gets all information about a task (and caches information if wanted)
     * @param integer $task_id
     * @param bool $cache_enabled
     * @access public static
     * @return mixed an array with all taskdetails or false on failure
     * @version 1.0
     */
   public static  function GetTaskDetails($task_id, $cache_enabled = false)
    {
        global $db, $fs;

        static $cache = array();

        if (isset($cache[$task_id]) && $cache_enabled) {
            return $cache[$task_id];
        }

        //for some reason, task_id is not here
        // run away inmediately..
        if(!is_numeric($task_id)) {
            return false;
        }

        $get_details = $db->Query('SELECT t.*, p.*,
                                          c.category_name, c.category_owner, c.lft, c.rgt, c.project_id as cproj,
                                          o.os_name,
                                          r.resolution_name,
                                          tt.tasktype_name,
                                          vr.version_name   AS reported_version_name,
                                          vd.version_name   AS due_in_version_name,
                                          uo.real_name      AS opened_by_name,
                                          ue.real_name      AS last_edited_by_name,
                                          uc.real_name      AS closed_by_name,
                                          lst.status_name   AS status_name
                                    FROM  {tasks}              t
                               LEFT JOIN  {projects}           p  ON t.project_id = p.project_id
                               LEFT JOIN  {list_category}      c  ON t.product_category = c.category_id
                               LEFT JOIN  {list_os}            o  ON t.operating_system = o.os_id
                               LEFT JOIN  {list_resolution}    r  ON t.resolution_reason = r.resolution_id
                               LEFT JOIN  {list_tasktype}      tt ON t.task_type = tt.tasktype_id
                               LEFT JOIN  {list_version}       vr ON t.product_version = vr.version_id
                               LEFT JOIN  {list_version}       vd ON t.closedby_version = vd.version_id
                               LEFT JOIN  {list_status}       lst ON t.item_status = lst.status_id
                               LEFT JOIN  {users}              uo ON t.opened_by = uo.user_id
                               LEFT JOIN  {users}              ue ON t.last_edited_by = ue.user_id
                               LEFT JOIN  {users}              uc ON t.closed_by = uc.user_id
                                   WHERE  t.task_id = ?', array($task_id));

        if (!$db->CountRows($get_details)) {
            return false;
        }

        if ($get_details = $db->FetchRow($get_details)) {
            $get_details += array('severity_name' => $fs->severities[$get_details['task_severity']]);
            $get_details += array('priority_name' => $fs->priorities[$get_details['task_priority']]);
        }

        $get_details['assigned_to'] = $get_details['assigned_to_name'] = array();
        if ($assignees = Flyspray::GetAssignees($task_id, true)) {
            $get_details['assigned_to'] = $assignees[0];
            $get_details['assigned_to_name'] = $assignees[1];
        }
        $cache[$task_id] = $get_details;

        return $get_details;
    } // }}}
    // List projects {{{
    /**
     * Returns a list of all projects
     * @param bool $active_only show only active projects
     * @access public static
     * @return array
     * @version 1.0
     */
    public static function listProjects(/*$active_only = true*/) // FIXME: $active_only would not work since the templates are accessing the returned array implying to be sortyed by project id, which is aparently wrong and error prone ! Same applies to the case when a project was deleted, causing a shift in the project id sequence, hence -> severe bug!
    {
        global $db;

        $query = 'SELECT  project_id, project_title FROM {projects}';

//         if ($active_only)  {
//             $query .= ' WHERE  project_is_active = 1';
//         }

        $query .= ' ORDER BY  project_id ASC';

        $sql = $db->Query($query);
        return $db->fetchAllArray($sql);
    } // }}}
    // List themes {{{
    /**
     * Returns a list of all themes
     * @access public static
     * @return array
     * @version 1.0
     */
    public static function listThemes()
    {
        $theme_array = array();
        if ($handle = opendir(dirname(dirname(__FILE__)) . '/themes/')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..' && is_file(dirname(dirname(__FILE__)) . "/themes/$file/theme.css")) {
                    $theme_array[] = $file;
                }
            }
            closedir($handle);
        }

        sort($theme_array);
        return $theme_array;
    } // }}}
    // List a project's group {{{
    /**
     * Returns a list of a project's groups
     * @param integer $proj_id
     * @access public static
     * @return array
     * @version 1.0
     */
    public static function listGroups($proj_id = 0)
    {
        global $db;
        $res = $db->Query('SELECT  *
                             FROM  {groups}
                            WHERE  project_id = ?
                         ORDER BY  group_id ASC', array($proj_id));
        return $db->FetchAllArray($res);
    } // }}}

    // Get info on all users {{{
    /**
     * Returns a list of a project's groups
     * @param integer $proj_id
     * @access public static
     * @return array
     * @version 1.0
     */
    public static function listUsers()
    {
        global $db;
        $res = $db->Query('SELECT  account_enabled, user_id, user_name, real_name, email_address
                             FROM  {users}
                         ORDER BY  account_enabled DESC, user_name ASC');
        return $db->FetchAllArray($res);
    }

    // }}}
    // List languages {{{
    /**
     * Returns a list of installed languages
     * @access public static
     * @return array
     * @version 1.0
     */
    public static function listLangs()
    {
        return str_replace('.php', '', array_map('basename', glob_compat(BASEDIR ."/lang/[a-zA-Z]*.php")));

    } // }}}
    // Log events to the history table {{{
    /**
     * Saves an event to the database
     * @param integer $task_id
     * @param integer $type
     * @param string $newvalue
     * @param string $oldvalue
     * @param string $field
     * @param integer $time for synchronisation with other functions
     * @access public static
     * @return void
     * @version 1.0
     */
    public static function logEvent($task_id, $type, $newvalue = '', $oldvalue = '', $field = '', $time = null)
    {
        global $db, $user;

        // This function creates entries in the history table.  These are the event types:
        //  0: Fields changed in a task
        //  1: New task created
        //  2: Task closed
        //  3: Task edited (for backwards compatibility with events prior to the history system)
        //  4: Comment added
        //  5: Comment edited
        //  6: Comment deleted
        //  7: Attachment added
        //  8: Attachment deleted
        //  9: User added to notification list
        // 10: User removed from notification list
        // 11: Related task added to this task
        // 12: Related task removed from this task
        // 13: Task re-opened
        // 14: Task assigned to user / re-assigned to different user / Unassigned
        // 15: This task was added to another task's related list
        // 16: This task was removed from another task's related list
        // 17: Reminder added
        // 18: Reminder deleted
        // 19: User took ownership
        // 20: Closure request made
        // 21: Re-opening request made
        // 22: Adding a new dependency
        // 23: This task added as a dependency of another task
        // 24: Removing a dependency
        // 25: This task removed from another task's dependency list
        // 26: Task was made private
        // 27: Task was made public
        // 28: PM request denied
        // 29: User added to the list of assignees
        // 30: New user registration
        // 31: User deletion
        // 32: Add new subtask
        // 33: Remove Subtask

        $query_params = array(intval($task_id), intval($user->id),
                             ((!is_numeric($time)) ? time() : $time),
                              $type, $field, $oldvalue, $newvalue);

        if($db->Query('INSERT INTO {history} (task_id, user_id, event_date, event_type, field_changed,
                       old_value, new_value) VALUES (?, ?, ?, ?, ?, ?, ?)', $query_params)) {

                           return true;
         }

        return false;
    } // }}}
    // Log a request for an admin/project manager to do something {{{
    /**
     * Adds an admin request to the database
     * @param integer $type 1: Task close, 2: Task re-open
     * @param integer $project_id
     * @param integer $task_id
     * @param integer $submitter
     * @param string $reason
     * @access public static
     * @return void
     * @version 1.0
     */
    public static function AdminRequest($type, $project_id, $task_id, $submitter, $reason)
    {
        global $db;
        $db->Query('INSERT INTO {admin_requests} (project_id, task_id, submitted_by, request_type, reason_given, time_submitted, deny_reason)
                         VALUES (?, ?, ?, ?, ?, ?, ?)',
                    array($project_id, $task_id, $submitter, $type, $reason, time(), ''));
    } // }}}
    // Check for an existing admin request for a task and event type {{{;
    /**
     * Checks whether or not there is an admin request for a task
     * @param integer $type 1: Task close, 2: Task re-open
     * @param integer $task_id
     * @access public static
     * @return bool
     * @version 1.0
     */
    public static function AdminRequestCheck($type, $task_id)
    {
        global $db;

        $check = $db->Query("SELECT *
                               FROM {admin_requests}
                              WHERE request_type = ? AND task_id = ? AND resolved_by = 0",
                            array($type, $task_id));
        return (bool)($db->CountRows($check));
    } // }}}
    // Get the current user's details {{{
    /**
     * Gets all user details of a user
     * @param integer $user_id
     * @access public static
     * @return array
     * @version 1.0
     */
   public static function getUserDetails($user_id)
    {
        global $db;

        // Get current user details.  We need this to see if their account is enabled or disabled
        $result = $db->Query('SELECT * FROM {users} WHERE user_id = ?', array(intval($user_id)));
        return $db->FetchRow($result);
    } // }}}
    // Get group details {{{
    /**
     * Gets all information about a group
     * @param integer $group_id
     * @access public static
     * @return array
     * @version 1.0
     */
    public static function getGroupDetails($group_id)
    {
        global $db;
        $sql = $db->Query('SELECT * FROM {groups} WHERE group_id = ?', array($group_id));
        return $db->FetchRow($sql);
    } // }}}
    //  {{{
    /**
     * Crypt a password with the method set in the configfile
     * @param string $password
     * @access public static
     * @return string
     * @version 1.0
     */
    public static function cryptPassword($password)
    {
        global $conf;
        $pwcrypt = $conf['general']['passwdcrypt'];

        if (strtolower($pwcrypt) == 'sha1') {
            return sha1($password);
        } elseif (strtolower($pwcrypt) == 'md5') {
            return md5($password);
        } else {
            return crypt($password);
        }
    } // }}}
    // {{{
    /**
     * Check if a user provided the right credentials
     * @param string $username
     * @param string $password
     * @access public static
     * @return integer user_id on success, 0 if account or user is disabled, -1 if password is wrong
     * @version 1.0
     */
    public static function checkLogin($username, $password, $method = 'native')
    {
        global $db;

	$email_address = $username;  //handle multiple email addresses
        $temp = $db->Query("SELECT id FROM {user_emails} WHERE email_address = ?",$email_address);
	$user_id = $db->FetchRow($temp);
	$user_id = $user_id["id"];

	$result = $db->Query("SELECT  uig.*, g.group_open, u.account_enabled, u.user_pass,
                                        lock_until, login_attempts
                                FROM  {users_in_groups} uig
                           LEFT JOIN  {groups} g ON uig.group_id = g.group_id
                           LEFT JOIN  {users} u ON uig.user_id = u.user_id
                               WHERE  u.user_id = ? OR u.user_name = ? AND g.project_id = ?
                            ORDER BY  g.group_id ASC", array($user_id, $username, 0));

        $auth_details = $db->FetchRow($result);

        if($auth_details === false) {
            return -2;
        }
        if(!$result || !count($auth_details)) {
            return 0;
        }

        //encrypt the password with the method used in the db
        switch (strlen($auth_details['user_pass'])) {
            case 40:
                $password = sha1($password);
                break;
            case 32:
                $password = md5($password);
                break;
            default:
                $password = crypt($password, $auth_details['user_pass']); //using the salt from db
                break;
        }

        if ($auth_details['lock_until'] > 0 && $auth_details['lock_until'] < time()) {
            $db->Query('UPDATE {users} SET lock_until = 0, account_enabled = 1, login_attempts = 0
                           WHERE user_id = ?', array($auth_details['user_id']));
            $auth_details['account_enabled'] = 1;
            $_SESSION['was_locked'] = true;
        }

        // Compare the crypted password to the one in the database
        // skip password check if the user is using oauth
        $pwOk = ($method == 'oauth') ? true : ($password == $auth_details['user_pass']);
        // Admin users cannot be disabled
        if ($auth_details['group_id'] == 1 /* admin */ && $pwOk) {
            return $auth_details['user_id'];
        }
        if ($pwOk && $auth_details['account_enabled'] == '1' && $auth_details['group_open'] == '1')
        {
            return $auth_details['user_id'];
        }

        return ($auth_details['account_enabled'] && $auth_details['group_open']) ? 0 : -1;
    } // }}}

    static public function checkForOauthUser($uid, $provider)
    {
        global $db;

        if(empty($uid) || empty($provider)) {
            return false;
        }

        $sql = $db->Query("SELECT id FROM {user_emails} WHERE oauth_uid = ? AND oauth_provider = ?",array($uid, $provider));

        if ($db->fetchOne($sql)) {
            return true;
        } else {
            return false;
        }
    }


    // Set cookie {{{
    /**
     * Sets a cookie, automatically setting the URL
     * @param string $name
     * @param string $val
     * @param integer $time
     * @access public static
     * @return bool
     * @version 1.0
     */
    public static function setCookie($name, $val, $time = null)
    {
        $url = parse_url($GLOBALS['baseurl']);
        if (!is_int($time)) {
            $time = time()+60*60*24*30;
        }

        if((strlen($name) + strlen($val)) > 4096) {
            //violation of the protocol
            trigger_error("Flyspray sent a too big cookie, browsers will not handle it");
            return false;
        }

        return setcookie($name, $val, $time, $url['path']);
    } // }}}
            // Start the session {{{
    /**
     * Starts the session
     * @access public static
     * @return void
     * @version 1.0
     * @notes smile intented
     */
    public static function startSession()
    {
        if (defined('IN_FEED') || php_sapi_name() === 'cli') {
            return;
        }

        $names = array( 'GetFirefox',
                        'UseLinux',
                        'NoMicrosoft',
                        'ThinkB4Replying',
                        'FreeSoftware',
                        'ReadTheFAQ',
                        'RTFM',
                        'VisitAU',
                        'SubliminalAdvertising',
                      );

        foreach ($names as $val)
        {
            session_name($val);
            session_start();

            if (isset($_SESSION['SESSNAME']))
            {
                $sessname = $_SESSION['SESSNAME'];
                break;
            }

            $_SESSION = array();
            session_destroy();
            setcookie(session_name(), '', time()-60, '/');
        }

        if (empty($sessname))
        {
            $rand_key = array_rand($names);
            $sessname = $names[$rand_key];
            session_name($sessname);
            session_start();
            $_SESSION['SESSNAME'] = $sessname;
        }
    }  // }}}

    // Compare tasks {{{
    /**
     * Compares two tasks and returns an array of differences
     * @param array $old
     * @param array $new
     * @access public static
     * @return array array('field', 'old', 'new')
     * @version 1.0
     */
    public static function compare_tasks($old, $new)
    {
        $comp = array('priority_name', 'severity_name', 'status_name', 'assigned_to_name', 'due_in_version_name',
                     'reported_version_name', 'tasktype_name', 'os_name', 'category_name',
                     'due_date', 'percent_complete', 'item_summary', 'due_in_version_name',
                     'detailed_desc', 'project_title', 'mark_private');

        $changes = array();
        foreach ($old as $key => $value)
        {
            if (!in_array($key, $comp) || ($key === 'due_date' && intval($old[$key]) === intval($new[$key]))) {
                continue;
            }

            if($old[$key] != $new[$key]) {
                switch ($key)
                {
                    case 'due_date':
                        $new[$key] = formatDate($new[$key]);
                        $value = formatDate($value);
                        break;

                    case 'percent_complete':
                        $new[$key] .= '%';
                        $value .= '%';
                        break;

                    case 'mark_private':
                        $new[$key] = $new[$key] ? L('private') : L('public');
                        $value = $value ? L('private') : L('public');
                        break;
                }
                $changes[] = array($key, $value, $new[$key]);
            }
        }

        return $changes;
    } // }}}
    // {{{
    /**
     * Get a list of assignees for a task
     * @param integer $task_id
     * @param bool $name whether or not names of the assignees should be returned as well
     * @access public static
     * @return array
     * @version 1.0
     */
    public static function GetAssignees($task_id, $name = false)
    {
        global $db;

        $sql = $db->Query('SELECT u.real_name, u.user_id
                             FROM {users} u, {assigned} a
                            WHERE task_id = ? AND u.user_id = a.user_id',
                              array($task_id));

        $assignees = array();
        while ($row = $db->FetchRow($sql)) {
            if ($name) {
                $assignees[0][] = $row['user_id'];
                $assignees[1][] = $row['real_name'];
            } else {
                $assignees[] = $row['user_id'];
            }
        }

        return $assignees;
    } /// }}}

    // {{{
    /**
     * Explode string to the array of integers
     * @param string $separator
     * @param string $string
     * @access public static
     * @return array
     * @version 1.0
     */
    public static function int_explode($separator, $string)
    {
    	$ret = array();
    	foreach (explode($separator, $string) as $v)
    	{
            if (ctype_digit($v)) {// $v is always string, this func returns false if $v == ''
    			$ret[] = intval($v); // convert to int
            }
    	}
    	return $ret;
    } /// }} }

    /**
     * Checks if a function is disabled
     * @param string $func_name
     * @access public static
     * @return bool
     * @version 1.0
     */
    public static function function_disabled($func_name)
    {
        $disabled_functions = explode(',', ini_get('disable_functions'));
        return in_array($func_name, $disabled_functions);
    }

    /**
     * Returns the key number of an array which contains an array like array($key => $value)
     * For use with SQL result arrays
     * @param string $key
     * @param string $value
     * @param array $array
     * @access public static
     * @return integer
     * @version 1.0
     */
    public static function array_find($key, $value, $array)
    {
        foreach ($array as $num => $part) {
            if (isset($part[$key]) && $part[$key] == $value) {
                return $num;
            }
        }
    }

    /**
     * Shows an error message
     * @param string $error_message if it is an integer, an error message from the language file will be loaded
     * @param bool $die enable/disable redirection (if outside the database modification script)
     * @param string $advanced_info append a string to the error message
     * @param string $url alternate redirection
     * @access public static
     * @return void
     * @version 1.0
     * @notes if a success and error happens on the same page, a mixed error message will be shown
     * @todo is the if ($die) meant to be inside the else clause?
     */
    public static function show_error($error_message, $die = true, $advanced_info = null, $url = null)
    {
        global $modes, $baseurl;

        if (!is_int($error_message)) {
            // in modify.inc.php
            $_SESSION['ERROR'] = $error_message;
        } else {
            $_SESSION['ERROR'] = L('error#') . $error_message . ': ' . L('error' . $error_message);
            if (!is_null($advanced_info)) {
                $_SESSION['ERROR'] .= ' ' . $advanced_info;
            }
            if ($die) {
                Flyspray::Redirect( (is_null($url) ? $baseurl : $url) );
            }
        }
    }

    /**
     * Returns the user ID if valid, 0 otherwise
     * @param int $id
     * @access public static
     * @return integer 0 if the user does not exist
     * @version 1.0
     */
    public static function ValidUserId($id)
    {
        global $db;

        $sql = $db->Query('SELECT user_id FROM {users} WHERE user_id = ?', array(intval($id)));

        return intval($db->FetchOne($sql));
    }

    /**
     * Returns the ID of a user with $name
     * @param string $name
     * @access public static
     * @return integer 0 if the user does not exist
     * @version 1.0
     */
    public static function UserNameToId($name)
    {
        global $db;

        $sql = $db->Query('SELECT user_id FROM {users} WHERE user_name = ?', array($name));

        return intval($db->FetchOne($sql));
    }

    /**
     * check_email
     *  checks if an email is valid
     * @param string $email
     * @access public
     * @return bool
     */
    public static function check_email($email)
    {
        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * get_tmp_dir
     * Based on PEAR System::tmpdir() by Tomas V.V.Cox.
     * @access public
     * @return void
     */
    public static function get_tmp_dir()
    {
        $return = '';

        if (function_exists('sys_get_temp_dir')) {
            $return = sys_get_temp_dir();
        } elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            if ($var = isset($_ENV['TEMP']) ? $_ENV['TEMP'] : getenv('TEMP')) {
                $return = $var;
            } else
            if ($var = isset($_ENV['TMP']) ? $_ENV['TMP'] : getenv('TMP')) {
                $return = $var;
            } else
            if ($var = isset($_ENV['windir']) ? $_ENV['windir'] : getenv('windir')) {
                $return = $var;
            } else {
                $return = getenv('SystemRoot') . '\temp';
            }

        } elseif ($var = isset($_ENV['TMPDIR']) ? $_ENV['TMPDIR'] : getenv('TMPDIR')) {
             $return = $var;
        } else {
            $return = '/tmp';
        }
        // Now, the final check
        if (@is_dir($return) && is_writable($return)) {
            return rtrim($return, DIRECTORY_SEPARATOR);
        // we have a problem at this stage.
        } elseif(is_writable(ini_get('upload_tmp_dir'))) {
            $return = ini_get('upload_tmp_dir');
        } elseif(is_writable(ini_get('session.save_path'))) {
            $return = ini_get('session.save_path');
        }
        return rtrim($return, DIRECTORY_SEPARATOR);
    }

    /**
     * check_mime_type
     *
     * @param string $fname path to filename
     * @access public
     * @return string the mime type of the offended file.
     * @notes DO NOT use this function for any security related
     * task (i.e limiting file uploads by type)
     * it wasn't designed for that purpose but to UI related tasks.
     */
    public static function check_mime_type($fname) {

        $type = '';

        if (extension_loaded('fileinfo') && class_exists('finfo')) {

            $info = new finfo(FILEINFO_MIME);
            $type = $info->file($fname);

        } elseif(function_exists('mime_content_type')) {

            $type = @mime_content_type($fname);
        // I hope we don't have to...
        } elseif(!FlySpray::function_disabled('exec') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN'
                 && php_uname('s') !== 'SunOS') {

               $type = @exec(sprintf('file -bi %s', escapeshellarg($fname)));

        }
        // if wasn't possible to determine , return empty string so
        // we can use the browser reported mime-type (probably fake)
        return trim($type);
    }

    /**
     * Works like strtotime, but it considers the user's timezone
     * @access public
     * @param string $time
     * @return integer
     */
    public static function strtotime($time)
    {
        global $user;

        $time = strtotime($time);

        if (!$user->isAnon()) {
            $st = date('Z')/3600; // server GMT timezone
            // Example: User is GMT+3, Server GMT-2.
            // User enters 7:00. For the server it must be converted to 2:00 (done below)
            $time += ($st - $user->infos['time_zone']) * 60 * 60;
            // later it adds 5 hours to 2:00 for the user when the date is displayed.
        }
        //strtotime()  may return false, making this method to return bool instead of int.
        return $time ? $time : 0;
    }

    /**
     * Writes content to a file using a lock.
     * @access public
     * @param string $filename location to write to
     * @param string $content data to write
     */
    public static function write_lock($filename, $content)
    {
        if ($f = fopen($filename, 'wb')) {
            if(flock($f, LOCK_EX)) {
                fwrite($f, $content);
                flock($f, LOCK_UN);
            }
            fclose($f);
        }
    }

    /**
     * file_get_contents replacement for remote files
     * @access public
     * @param string $url
     * @param bool $get_contents whether or not to return file contents, use GET_CONTENTS for true
     * @param integer $port
     * @param string $connect manually choose server for connection
     * @return string an empty string is not necessarily a failure
     */
    public static function remote_request($url, $get_contents = false, $port = 80, $connect = '', $host = null)
    {
        $url = parse_url($url);
        if (!$connect) {
            $connect = $url['host'];
        }

        if ($host) {
            $url['host'] = $host;
        }

        $data = '';

        if ($conn = @fsockopen($connect, $port, $errno, $errstr, 10)) {
            $out =  "GET {$url['path']} HTTP/1.0\r\n";
            $out .= "Host: {$url['host']}\r\n\r\n";
            $out .= "Connection: Close\r\n\r\n";

            stream_set_timeout($conn, 5);
            fwrite($conn, $out);

            if ($get_contents) {
                while (!feof($conn)) {
                    $data .= fgets($conn, 128);
                }

                $pos = strpos($data, "\r\n\r\n");

                if ($pos !== false) {
                   //strip the http headers.
                    $data = substr($data, $pos + 2 * strlen("\r\n"));
                }
            }
                fclose($conn);
        }

        return $data;
    }

    /**
     * Returns an array containing all notification options the user is
     * allowed to use.
     * @access public
     * @return array
     */
    public function GetNotificationOptions($noneAllowed = true)
    {
        switch ($this->prefs['user_notify'])
        {
            case 0:
                return array(0             => L('none'));
            case 2:
                return array(NOTIFY_EMAIL  => L('email'));
            case 3:
                return array(NOTIFY_JABBER => L('jabber'));

        }

        $return = array(0             => L('none'),
                        NOTIFY_EMAIL  => L('email'),
                        NOTIFY_JABBER => L('jabber'),
                        NOTIFY_BOTH   => L('both'));
        if (!$noneAllowed) {
            unset($return[0]);
        }

        return $return;
    }

    /**
     * getSvnRev
     *  For internal use
     * @access public
     * @return string
     */
    public static function getSvnRev()
    {
        if(is_file(BASEDIR. '/REVISION') && is_dir(BASEDIR . '/.svn')) {

            return sprintf('r%d',file_get_contents(BASEDIR .'/REVISION'));
        }

        return '';
    }

}
