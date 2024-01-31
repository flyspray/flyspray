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
	 * For github development use e.g. '1.0-beta dev' ; Flyspray::base_version() currently splits on the ' ' ...
	 * For making github release use e.g. '1.0-beta' here.
	 * For online version check www.flyspray.org/version.txt use e.g. '1.0-beta'
	 * For making releases on github use github's recommended versioning e.g. 'v1.0-beta' --> release files are then named v1.0-beta.zip and v1.0-beta.tar.gz and unzips to a flyspray-1.0-beta/ directory.
	 * Well, looks like a mess but hopefully consolidate this in future. Maybe use version_compare() everywhere in future instead of an own invented Flyspray::base_version()
	 */
	public $version = '1.0-rc12 dev';

	/**
	 * Flyspray preferences
	 * @access public
	 * @var array
	 */
	public $prefs = array();

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

		$res = $db->query('SELECT pref_name, pref_value FROM {prefs}');

		while ($row = $db->fetchRow($res)) {
			$this->prefs[$row['pref_name']] = $row['pref_value'];
		}

		$this->setDefaultTimezone();

		// only needed to calculate max_file_size if uploads are allowed by PHP configuration
		if (ini_get('file_uploads')) {
		
			$sizes = array();
			foreach (array(ini_get('memory_limit'), ini_get('post_max_size'), ini_get('upload_max_filesize')) as $val) {
				if($val === '-1') {
					// unlimited value in php configuration
					$val = PHP_INT_MAX;
				}
				if (!$val || $val < 0) {
					continue;
				}

				if (!is_int($val)) {
					$last = strtolower($val[strlen($val)-1]);
					$val = trim($val, 'gGmMkK');
					switch ($last) {
						case 'g':
							$val *= 1024;
						case 'm':
							$val *= 1024;
						case 'k':
							$val *= 1024;
					}
				}
				$sizes[] = $val;
			}

			clearstatcache();
			$this->max_file_size = (
				is_file(BASEDIR.DIRECTORY_SEPARATOR.'attachments'.DIRECTORY_SEPARATOR.'index.html')
				&& is_writable(BASEDIR.DIRECTORY_SEPARATOR.'attachments')
				) ? round((min($sizes)/1024/1024), 1) : 0;
		}
	}

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
    public static function redirect($url, $exit = true, $rfc2616 = true)
    {

        @ob_clean();

        if (isset($_SESSION) && count($_SESSION)) {
            session_write_close();
        }

        if (headers_sent()) {
            die('Headers are already sent, this should not have happened. Please inform Flyspray developers.');
        }

        $url = Flyspray::absoluteURI($url);

	if($_SERVER['REQUEST_METHOD']=='POST' && version_compare(PHP_VERSION, '5.4.0')>=0 ) {
		http_response_code(303);
	}
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
    }

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


        if (!strlen($url) || $url[0] == '?' || $url[0] == '#') {
            $uri = isset($_SERVER['REQUEST_URI']) ?
                $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
            if ($url && $url[0] == '?' && false !== ($q = strpos($uri, '?'))) {
                $url = substr($uri, 0, $q) . $url;
            } else {
                $url = $uri . $url;
            }
        }

        if ($url[0] == '/') {
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
    }

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
    }

    /**
     * Gets all information about a task (and caches information if wanted)
     * @param integer $task_id
     * @param bool $cache_enabled
     * @access public static
     * @return mixed an array with all taskdetails or false on failure
     * @version 1.0
     */
   public static function getTaskDetails($task_id, $cache_enabled = false)
    {
        global $db, $fs;

        static $cache = array();

        if (isset($cache[$task_id]) && $cache_enabled) {
            return $cache[$task_id];
        }

        //for some reason, task_id is not here
        // run away immediately..
        if(!is_numeric($task_id)) {
            return false;
        }

        $get_details = $db->query('SELECT t.*, p.*,
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

        if (!$db->countRows($get_details)) {
            return false;
        }

        if ($get_details = $db->fetchRow($get_details)) {
            $get_details += array('severity_name' => $get_details['task_severity']==0 ? '' : $fs->severities[$get_details['task_severity']]);
            $get_details += array('priority_name' => $get_details['task_priority']==0 ? '' : $fs->priorities[$get_details['task_priority']]);
        }
	
	$get_details['tags'] = Flyspray::getTags($task_id);

        $get_details['assigned_to'] = $get_details['assigned_to_name'] = array();
        if ($assignees = Flyspray::getAssignees($task_id, true)) {
            $get_details['assigned_to'] = $assignees[0];
            $get_details['assigned_to_name'] = $assignees[1];
        }
	
		/**
		 * prevent RAM growing array like creating 100000 tasks with Backend::create_task() in a loop (Tests)
		 * Costs maybe some SQL queries if getTaskDetails is called first without $cache_enabled
		 * and later with $cache_enabled within same request
		 */
		if($cache_enabled){
			$cache[$task_id] = $get_details;
		}
		return $get_details;
	}

	/**
	* Returns a list of all projects
	* @param bool $active_only show only active projects
	* @access public static
	* @return array
	* @version 1.0
	*/
	// FIXME: $active_only would not work since the templates are accessing the returned array implying to be sortyed by project id, which is aparently wrong and error prone ! Same applies to the case when a project was deleted, causing a shift in the project id sequence, hence -> severe bug!
	# comment by peterdd 20151012: reenabled param active_only with false as default. I do not see a problem within current Flyspray version. But consider using $fs->projects when possible, saves this extra sql request.
	public static function listProjects($active_only = false)
	{
		global $db;
		$query = 'SELECT project_id, project_title, project_is_active FROM {projects}';

		if ($active_only) {
			$query .= ' WHERE project_is_active = 1';
		}

		$query .= ' ORDER BY project_is_active DESC, project_id DESC'; # active first, latest projects first for option groups and new projects are probably the most used.

		$sql = $db->query($query);
		return $db->fetchAllArray($sql);
	}
    
    /**
     * Returns a list of all themes
     * @access public static
     * @return array
     * @version 1.0
     */
    public static function listThemes()
    {
        $themes = array();
        $dirname = dirname(dirname(__FILE__));
        if ($handle = opendir($dirname . '/themes/')) {
            while (false !== ($file = readdir($handle))) {
                if (substr($file,0,1) != '.' && is_dir("$dirname/themes/$file")
		    && (is_file("$dirname/themes/$file/theme.css") || is_dir("$dirname/themes/$file/templates"))
		   ) {
                    $themes[] = $file;
                }
            }
            closedir($handle);
        }

        sort($themes);
		# always put the full default Flyspray theme first, [0] works as fallback in class Tpl->setTheme()
		array_unshift($themes, 'CleanFS');
		$themes = array_unique($themes);
        return $themes;
    }

    /**
     * Returns a list of global groups or a project's groups
     * @param integer $proj_id
     * @access public static
     * @return array
     * @version 1.0
     */
    public static function listGroups($proj_id = 0)
    {
	global $db;
	$res = $db->query('SELECT g.*, COUNT(uig.user_id) AS users
		FROM {groups} g
		LEFT JOIN {users_in_groups} uig ON uig.group_id=g.group_id
		WHERE project_id = ?
		GROUP BY g.group_id
		ORDER BY g.group_id ASC', array($proj_id));
        return $db->fetchAllArray($res);
    }

	/**
	 * Returns a list of a all users
	 *
	 * @access public static
	 * @param array $opts optional filter and verbosity of user infos
	 *              $opts=array(
	 *                  offset => unset|integer,
	 *                  perpage => unset|integer,
	 *                  status => unset|0|1,
	 *                  namesearch => unset|string,
	 *                  mailsearch => unset|string,
	 *                  stats => unset|isset
	 *                  order => unset|string (one of allowed sortable fields)
	 *                  sort => unset|desc
	 *              )
	 * @return array
	 * @version 1.0
	 */
	public static function listUsers($opts = array())
	{
		global $db;

		if (!isset($opts['offset'])) {
			$opts['offset'] = 0;
		}

		if (!isset($opts['perpage'])) {
			# default max_input_vars of PHP is 1000, so 1 checkbox per user + other/hidden/submitbutton form vars <= max_input_vars
			# we now have filterable userlist, 100 per page seems ok.
			$opts['perpage'] = 100;
		}

		$sortable = array(
			'regdate' => 'register_date',
			'lastlogin' => 'last_login',
			'username' => 'user_name',
			'realname' => 'real_name',
			'emailaddress' => 'email_address',
			'jabberid' => 'jabber_id'
		);

		$filter = array();
		$params = array();
		
		if (isset($opts['status']) && ($opts['status']===1 || $opts['status']===0)) {
			$filter[] = 'account_enabled = '.$opts['status'];
		}

		if (isset($opts['namesearch']) && is_string($opts['namesearch']) && ($opts['namesearch']!='')) {
			$filter[] = "(user_name LIKE ? OR real_name LIKE ?)";
			$params[] = $opts['namesearch'];
			$params[] = $opts['namesearch'];
		}
		
		/**
		 * @note currently only primary email address in {users}, not {user_emails}
		 * @todo when doing table user_emails review, see reopened FS#1812
		 */
		if (isset($opts['mailsearch']) && is_string($opts['mailsearch']) && ($opts['mailsearch']!='')) {
			$filter[] = "email_address LIKE ?";
			$params[] = $opts['mailsearch'];
		}

		if (count($filter)) {
			$where = "\nWHERE ".implode( "\nAND " , $filter);
			$having = "\nHAVING ".implode( "\nAND " , $filter);
		} else {
			$where = '';
			$having = '';
		}

		if (isset($opts['order']) && is_string($opts['order']) && array_key_exists($opts['order'], $sortable)) {
			$orderby = "\nORDER BY ".$sortable[$opts['order']];
			if (isset($opts['sort']) && $opts['sort'] ==='desc') {
				$orderby.= ' DESC';
			}
		} else {
			$orderby = "\nORDER BY account_enabled DESC, user_name ASC";
		}

		if (!isset($opts['stats'])) {
			$sql = 'SELECT account_enabled, user_id, user_name, real_name,
				email_address, jabber_id, oauth_provider, oauth_uid,
				notify_type, notify_own, notify_online,
				tasks_perpage, lang_code, time_zone, dateformat, dateformat_extended,
				register_date, login_attempts, lock_until,
				profile_image, hide_my_email, last_login
				FROM {users}';
			$sql .= $where;
			$sql .= $orderby;
			
		} else {
			# Well, this is a big and slow query, but the current solution I found.
			# If you know a more elegant for calculating user stats from the different tables with one query let us know!
			$sql = '
SELECT
MIN(u.account_enabled) AS account_enabled,
MIN(u.user_id) AS user_id,
MIN(u.user_name) AS user_name,
MIN(u.real_name) AS real_name,
MIN(u.email_address) AS email_address,
MIN(u.jabber_id) AS jabber_id,
MIN(u.oauth_provider) AS oauth_provider,
MIN(u.oauth_uid) AS oauth_uid,
MIN(u.notify_type) AS notify_type,
MIN(u.notify_own) AS notify_own,
MIN(u.notify_online) AS notify_online,
MIN(u.tasks_perpage) AS tasks_perpage,
MIN(u.lang_code) AS lang_code,
MIN(u.time_zone) AS time_zone,
MIN(u.dateformat) AS dateformat,
MIN(u.dateformat_extended) AS dateformat_extended,
MIN(u.register_date) AS register_date,
MIN(u.login_attempts) AS login_attempts,
MIN(u.lock_until) AS lock_until,
MIN(u.profile_image) AS profile_image,
MIN(u.hide_my_email) AS hide_my_email,
MIN(u.last_login) AS last_login,
SUM(countopen) AS countopen,
SUM(countclose) AS countclose,
SUM(countlastedit) AS countlastedit,
SUM(comments) AS countcomments,
SUM(assigned) AS countassign,
SUM(watching) AS countwatching,
SUM(votes) AS countvotes
FROM
(	SELECT u.account_enabled, u.user_id, u.user_name, u.real_name,
        u.email_address, u.jabber_id, u.oauth_provider, u.oauth_uid,
        u.notify_type, u.notify_own, u.notify_online,
        u.tasks_perpage, u.lang_code, u.time_zone, u.dateformat, u.dateformat_extended,
        u.register_date, u.login_attempts, u.lock_until,
        u.profile_image, u.hide_my_email, u.last_login,
        COUNT(topen.opened_by) AS countopen, 0 AS countclose, 0 AS countlastedit, 0 AS comments, 0 AS assigned, 0 AS watching, 0 AS votes
        FROM {users} u
        LEFT JOIN {tasks} topen ON topen.opened_by=u.user_id
        GROUP BY u.user_id
UNION
       	SELECT u.account_enabled, u.user_id, u.user_name, u.real_name,
        u.email_address, u.jabber_id, u.oauth_provider, u.oauth_uid,
        u.notify_type, u.notify_own, u.notify_online,
        u.tasks_perpage, u.lang_code, u.time_zone, u.dateformat, u.dateformat_extended,
        u.register_date, u.login_attempts, u.lock_until,
        u.profile_image, u.hide_my_email, u.last_login,
        0, COUNT(tclose.closed_by) AS countclose, 0, 0, 0, 0, 0
        FROM {users} u
        LEFT JOIN {tasks} tclose ON tclose.closed_by=u.user_id
        GROUP BY u.user_id
UNION
        SELECT u.account_enabled, u.user_id, u.user_name, u.real_name,
        u.email_address, u.jabber_id, u.oauth_provider, u.oauth_uid,
        u.notify_type, u.notify_own, u.notify_online,
        u.tasks_perpage, u.lang_code, u.time_zone, u.dateformat, u.dateformat_extended,
        u.register_date, u.login_attempts, u.lock_until,
        u.profile_image, u.hide_my_email, u.last_login,
        0, 0, COUNT(tlast.last_edited_by) AS countlastedit, 0, 0, 0, 0
        FROM {users} u
        LEFT JOIN {tasks} tlast ON tlast.last_edited_by=u.user_id
        GROUP BY u.user_id
UNION
     	SELECT u.account_enabled, u.user_id, u.user_name, u.real_name,
        u.email_address, u.jabber_id, u.oauth_provider, u.oauth_uid,
        u.notify_type, u.notify_own, u.notify_online,
        u.tasks_perpage, u.lang_code, u.time_zone, u.dateformat, u.dateformat_extended,
        u.register_date, u.login_attempts, u.lock_until,
        u.profile_image, u.hide_my_email, u.last_login,
        0, 0, 0, COUNT(c.user_id) AS comments, 0, 0, 0
        FROM {users} u
        LEFT JOIN {comments} c ON c.user_id=u.user_id
        GROUP BY u.user_id
UNION
     	SELECT u.account_enabled, u.user_id, u.user_name, u.real_name,
        u.email_address, u.jabber_id, u.oauth_provider, u.oauth_uid,
        u.notify_type, u.notify_own, u.notify_online,
        u.tasks_perpage, u.lang_code, u.time_zone, u.dateformat, u.dateformat_extended,
        u.register_date, u.login_attempts, u.lock_until,
        u.profile_image, u.hide_my_email, u.last_login,
        0, 0, 0, 0, COUNT(a.user_id) AS assigned, 0, 0
        FROM {users} u
        LEFT JOIN {assigned} a ON a.user_id=u.user_id
        GROUP BY u.user_id
UNION
     	SELECT u.account_enabled, u.user_id, u.user_name, u.real_name,
        u.email_address, u.jabber_id, u.oauth_provider, u.oauth_uid,
        u.notify_type, u.notify_own, u.notify_online,
        u.tasks_perpage, u.lang_code, u.time_zone, u.dateformat, u.dateformat_extended,
        u.register_date, u.login_attempts, u.lock_until,
        u.profile_image, u.hide_my_email, u.last_login,
        0, 0, 0, 0, 0, COUNT(n.user_id) AS watching, 0
        FROM {users} u
        LEFT JOIN {notifications} n ON n.user_id=u.user_id
        GROUP BY u.user_id
UNION
     	SELECT u.account_enabled, u.user_id, u.user_name, u.real_name,
        u.email_address, u.jabber_id, u.oauth_provider, u.oauth_uid,
        u.notify_type, u.notify_own, u.notify_online,
        u.tasks_perpage, u.lang_code, u.time_zone, u.dateformat, u.dateformat_extended,
        u.register_date, u.login_attempts, u.lock_until,
        u.profile_image, u.hide_my_email, u.last_login,
        0, 0, 0, 0, 0, 0, COUNT(v.user_id) AS votes
        FROM {users} u
        LEFT JOIN {votes} v ON v.user_id=u.user_id
        GROUP BY u.user_id
) u
GROUP BY u.user_id';

			$sql .= $having;
			$orderby = "\nORDER BY MIN(u.account_enabled) DESC, MIN(u.user_name) ASC";
			$sql .= $orderby;
		}

		$sqlcount=$db->query('SELECT COUNT(*) FROM ('.$sql.') u', $params);
		$usercount=$db->fetchOne($sqlcount);
		$res = $db->query($sql, $params, $opts['perpage'], $opts['offset']);
		$users=$db->fetchAllArray($res);
		return array(
			'users'=>$users,
			'count'=>$usercount
		);
	}

    /**
     * Returns a list of installed languages
     * @access public static
     * @return array
     * @version 1.0
     */
    public static function listLangs()
    {
        return str_replace('.php', '', array_map('basename', glob_compat(BASEDIR ."/lang/[a-zA-Z]*.php")));
    }

    /**
     * Saves an event to the {history} db table
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
        // 34: Add new parent
        // 35: Remove parent

        $query_params = array(intval($task_id), intval($user->id),
                             ((!is_numeric($time)) ? time() : $time),
                              $type, $field, $oldvalue, $newvalue);

        if($db->query('INSERT INTO {history} (task_id, user_id, event_date, event_type, field_changed,
                       old_value, new_value) VALUES (?, ?, ?, ?, ?, ?, ?)', $query_params)) {

                           return true;
         }

        return false;
    }

    /**
     * Adds an admin or project manager request to the database
     * @param integer $type 1: Task close, 2: Task re-open, 3: Pending user registration
     * @param integer $project_id
     * @param integer $task_id
     * @param integer $submitter
     * @param string $reason
     * @access public static
     * @return void
     * @version 1.0
     */
    public static function adminRequest($type, $project_id, $task_id, $submitter, $reason)
    {
        global $db;
        $db->query('INSERT INTO {admin_requests} (project_id, task_id, submitted_by, request_type, reason_given, time_submitted, deny_reason)
                         VALUES (?, ?, ?, ?, ?, ?, ?)',
                    array($project_id, $task_id, $submitter, $type, $reason, time(), ''));
    }

    /**
     * Checks whether or not there is an admin request for a task
     * @param integer $type 1: Task close, 2: Task re-open, 3: Pending user registration
     * @param integer $task_id
     * @access public static
     * @return bool
     * @version 1.0
     */
    public static function adminRequestCheck($type, $task_id)
    {
        global $db;

        $check = $db->query("SELECT *
                               FROM {admin_requests}
                              WHERE request_type = ? AND task_id = ? AND resolved_by = 0",
                            array($type, $task_id));
        return (bool)($db->countRows($check));
    }

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
        $result = $db->query('SELECT * FROM {users} WHERE user_id = ?', array(intval($user_id)));
        return $db->fetchRow($result);
    }

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
        $sql = $db->query('SELECT * FROM {groups} WHERE group_id = ?', array($group_id));
        return $db->fetchRow($sql);
    }

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

		# during install e.g. not set
		if(isset($conf['general']['passwdcrypt'])){
			$pwcrypt = strtolower($conf['general']['passwdcrypt']);
		}else{
			$pwcrypt='';
		}

	# sha1, md5, sha512 are unsalted, hashing methods, not suited for storing passwords anymore.
	# Use password_hash(), that adds random salt, customizable rounds and customizable hashing algorithms.
	if ($pwcrypt == 'sha1') {
		return sha1($password);
	} elseif ($pwcrypt == 'md5') {
		return md5($password);
	} elseif ($pwcrypt == 'sha512') {
		return hash('sha512', $password);
	} elseif ($pwcrypt =='argon2i' && version_compare(PHP_VERSION,'7.2.0')>=0){
		# php7.2+
		return password_hash($password, PASSWORD_ARGON2I);
	} else {
		$bcryptoptions=array('cost'=>14);
		return password_hash($password, PASSWORD_BCRYPT, $bcryptoptions);
	}
  }


	public static function fetchAuthDetails($username, $method = 'native')
	{
		global $db;
		
		if($method === 'ldap') {
			$user_id = -42; // just an invalid id
		} else {
			// handle multiple email addresses
			$temp = $db->query("SELECT id FROM {user_emails} WHERE email_address = ?", $username);
			$user_id = $db->fetchRow($temp);
			$user_id = is_array($user_id) ? $user_id['id'] : -41; // just an invalid id
		}
		$result = $db->query("SELECT  uig.*, g.group_open, u.account_enabled, u.user_pass,
		                              lock_until, login_attempts
		                      FROM  {users_in_groups} uig
		                      LEFT JOIN  {groups} g ON uig.group_id = g.group_id
		                      LEFT JOIN  {users} u ON uig.user_id = u.user_id
		                      WHERE  (u.user_id = ? OR u.user_name = ?) AND g.project_id = ?
		                      ORDER BY  g.group_id ASC",
		                     array($user_id, $username, 0));
		$auth_details = $db->fetchRow($result);
		if(!$result || (is_array($auth_details) && !count($auth_details))) {
			return false;
		}
		if ($auth_details['lock_until'] > 0 && $auth_details['lock_until'] < time()) {
			$db->query('UPDATE {users} SET lock_until = 0, account_enabled = 1, login_attempts = 0
			            WHERE user_id = ?',
			           array($auth_details['user_id']));
			$auth_details['account_enabled'] = 1;
			$_SESSION['was_locked'] = true;
		}
		return $auth_details;
	}

	/**
	 * Check if a user provided the right credentials
	 * @param string $username
	 * @param string $password
	 * @param string $method '', 'oauth', 'ldap', 'native'
	 * @access public static
	 * @return integer user_id on success, 0 if account or user is disabled, -1 if password is wrong
	 * @version 1.0
	 */
	public static function checkLogin($username, $password, $method = 'native')
	{
		$pwok = null;
		if($method == 'oauth') {
			// skip password check if the user is using oauth
			$pwok = true;
		} elseif($method === 'ldap') {
			$pwok = Flyspray::checkForLDAPUser($username, $password);
			if(!$pwok) {
				return -1;
			}
		}

		$auth_details = Flyspray::fetchAuthDetails($username, $method);
		if($auth_details === false) {
			return -2;
		}

		if(is_null($pwok)) {
			// encrypt the password with the method used in the db
			if(substr($auth_details['user_pass'],0,1)!='$' && (
			           strlen($auth_details['user_pass'])==32
			        || strlen($auth_details['user_pass'])==40
			        || strlen($auth_details['user_pass'])==128
				)){
				# detecting (old) password stored with old unsalted hashing methods: md5,sha1,sha512
				switch(strlen($auth_details['user_pass'])){
				case 32:
					$pwhash = md5($password);
					break;
				case 40:
					$pwhash = sha1($password);
					break;
				case 128:
					$pwhash = hash('sha512', $password);
					break;
				}
				$pwok = hash_equals($auth_details['user_pass'], $pwhash);
			}else{
				#$pwhash = crypt($password, $auth_details['user_pass']); // user_pass contains algorithm, rounds, salt
				$pwok = password_verify($password, $auth_details['user_pass']);
			}
		}

		// Admin users cannot be disabled
		if ($auth_details['group_id'] == 1 /* admin */ && $pwok) {
			return $auth_details['user_id'];
		}
		if ($pwok && $auth_details['account_enabled'] == '1' && $auth_details['group_open'] == '1'){
			return $auth_details['user_id'];
		}

		return ($auth_details['account_enabled'] && $auth_details['group_open']) ? 0 : -1;
	}

    static public function checkForOauthUser($uid, $provider)
    {
        global $db;

        if(empty($uid) || empty($provider)) {
            return false;
        }

        $sql = $db->query("SELECT id FROM {user_emails} WHERE oauth_uid = ? AND oauth_provider = ?",array($uid, $provider));

        if ($db->fetchOne($sql)) {
            return true;
        } else {
            return false;
        }
    }

	/**
	 * Check if a LDAP user exists and binds
	 * @param string $username
	 * @param string $password
	 * @access public static
	 * @return bool
	 */
	public static function checkForLDAPUser($username, $password)
	{
		global $conf, $db, $fs;

		$ldap_uri =         isset($conf['ldap']['uri']) ? $conf['ldap']['uri'] : null; # ldap://example.com:389 
		$ldap_version =     isset($conf['ldap']['version']) ? $conf['ldap']['version'] : 3; 
		$base_dn =          $conf['ldap']['base_dn'];      # ou=users,dc=example,dc=com
		$ldap_search_user = $conf['ldap']['search_user'];  # cn=admin,dc=example,dc=com
		$ldap_search_pass = $conf['ldap']['search_pass'];  #
		$filter =           $conf['ldap']['filter'];       # uid=%USERNAME%
		$lf_name =          isset($conf['ldap']['field_name']) ? $conf['ldap']['field_name'] : 'cn';
		$lf_email =         isset($conf['ldap']['field_email']) ? $conf['ldap']['field_email'] : 'mail';

		if (strlen($password) == 0){ // LDAP will succeed binding with no password on AD (defaults to anon bind)
			return false;
		}

		$rs = ldap_connect($ldap_uri);
		@ldap_set_option($rs, LDAP_OPT_PROTOCOL_VERSION, $ldap_version);
		@ldap_set_option($rs, LDAP_OPT_REFERRALS, 0);
		$ldap_bind_dn = empty($ldap_search_user) ? NULL : $ldap_search_user;
		$ldap_bind_pw = empty($ldap_search_pass) ? NULL : $ldap_search_pass;
		if (!$bindok = @ldap_bind($rs, $ldap_bind_dn, $ldap_search_pass)){
			# Uncomment for LDAP debugging
			#$error_msg = ldap_error($rs);
			#die("Couldn't bind using ".$ldap_bind_dn."@".$ldap_uri." Because:".$error_msg);
			return false;
		} else{
			$filter_r = str_replace("%USERNAME%", $username, $filter);
			$result = @ldap_search($rs, $base_dn, $filter_r);
			if (!$result){ // ldap search returned nothing or error
				return false;
			}
			$result_user = ldap_get_entries($rs, $result);
			if ($result_user["count"] == 0){ // No users match the filter
				return false;
			}
			$first_user = $result_user[0];
			$ldap_user_dn = $first_user["dn"];
			# Bind with the dn of the user that matched our filter (only one user should match sAMAccountName or uid etc..)
			if (!$bind_user = @ldap_bind($rs, $ldap_user_dn, $password)){
				#$error_msg = ldap_error($rs);
				#die("Couldn't bind using ".$ldap_user_dn."@".$ldap_uri." Because:".$error_msg);
				return false;
			} else{
				# Create user if it doesn't exist
				$result = $db->query("SELECT user_id
					              FROM {users}
						      WHERE user_name = ?", array($username));
				$user_id = $db->fetchRow($result);
				if (!$result || !$user_id) {
					$group_in = $fs->prefs['anon_group'];
					$success  = Backend::create_user(
						$username,                    // login
						null,                         // password
						$first_user[$lf_name][0],     // name
						'',                           // jabber id
						$first_user[$lf_email][0],    // email
						1,                            // notify type
						(intval(strftime("%z"))/100), // time zone
						$group_in,                    // group in
						1);                           // enabled
					if(!$success) {
						die('Unable to register new LDAP user');
					}
				}
				return true;
			}
		}
	}

    /**
     * Sets a cookie, automatically setting the URL
     * Now same params as PHP's builtin setcookie()
     * @param string $name
     * @param string $val
     * @param integer $time
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @access public static
     * @return bool
     * @version 1.1
     */
    public static function setCookie($name, $val, $time = null, $path=null, $domain=null, $secure=false, $httponly=false)
    {
	global $conf;
	
        if (null===$path){
            $url = parse_url($GLOBALS['baseurl']);
        }else{
            $url['path']=$path;
        }

        if (!is_int($time)) {
            $time = time()+60*60*24*30;
        }
        if(null===$domain){
            $domain='';
        }
        if(null===$secure){
            $secure = isset($conf['general']['securecookies']) ? $conf['general']['securecookies'] : false;
        }
        if((strlen($name) + strlen($val)) > 4096) {
            //violation of the protocol
            trigger_error("Flyspray sent a too big cookie, browsers will not handle it");
            return false;
        }

        return setcookie($name, $val, $time, $url['path'],$domain,$secure,$httponly);
    }

    /**
     * Starts the session
     * @access public static
     * @return void
     * @version 1.0
     * @notes smile intented
     */
	public static function startSession()
	{
		global $conf;
		if (defined('IN_FEED') || php_sapi_name() === 'cli') {
			return;
		}

		$url = parse_url($GLOBALS['baseurl']);
		session_name('flyspray');
		session_set_cookie_params(0,$url['path'],'', (isset($conf['general']['securecookies'])? $conf['general']['securecookies']:false), TRUE);
		session_start();
		if(!isset($_SESSION['csrftoken'])){
			$_SESSION['csrftoken']=rand(); # lets start with one anti csrf token secret for the session and see if it's simplicity is good enough (I hope together with enforced Content Security Policies)
		}
	
		/**
		 * For the access key help: differences of browser and operating system combinations.
		 * As it is relative expensive and very slow below PHP 7.1.1:
		 * only do that once per user session and
		 * only on newer php versions and
		 * only if a browscap file is installed.
		 * lite_php_browscap.ini from browscap.org should be sufficient. 
		 * (below 1ms on a linux with php7.3 in virtualbox on old laptop)
		*/
		if (!isset($_SESSION['ua']) && version_compare(PHP_VERSION, '7.1.1') >= 0 && ini_get('browscap')){
			$ua = get_browser(null, true);
			$_SESSION['ua'] = array();
			$_SESSION['ua']['platform'] = $ua['platform'];
			$_SESSION['ua']['browser'] = $ua['browser'];
		}
	}

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
    }

		/**
		 * Get all tags of a task
		 * @param int $task_id
		 * 
		 * @access public static
		 * @return array
		 * @version 1.0
		 */
		public static function getTags($task_id)
		{
			global $db;
			$sql = $db->query('SELECT tg.tag_id, tg.tag_name AS tag, tg.class, tt.added, tt.added_by
				FROM {task_tag} tt
				JOIN {list_tag} tg ON tg.tag_id=tt.tag_id 
				WHERE task_id = ?
				ORDER BY list_position',
				array($task_id)
			);
			return $db->fetchAllArray($sql);
		}

	/**
	* load all task tags into array
	*
	* Compared to listTags() of class project, this loads all tags in Flyspray database into a global array.
	* Ideally called only once per http request, then using the array index for getting tag info.
	*
	* Used mainly for tasklist view to simplify get_task_list() sql query.
	*
	* @return array
	*/
	public static function getAllTags()
	{
		global $db;
		$at=array();
		$res = $db->query('SELECT tag_id, project_id, list_position, tag_name, class, show_in_list FROM {list_tag}');
		while ($t = $db->fetchRow($res)){
			$at[$t['tag_id']]=array(
				'project_id'=>$t['project_id'],
				'list_position'=>$t['list_position'],
				'tag_name'=>$t['tag_name'],
				'class'=>$t['class'],
				'show_in_list'=>$t['show_in_list']
			);
		}
		return $at;
	}

    /**
     * Get a list of assignees for a task
     * @param integer $task_id
     * @param bool $name whether or not names of the assignees should be returned as well
     * @access public static
     * @return array
     * @version 1.0
     */
    public static function getAssignees($task_id, $name = false)
    {
        global $db;

        $sql = $db->query('SELECT u.real_name, u.user_id
                             FROM {users} u, {assigned} a
                            WHERE task_id = ? AND u.user_id = a.user_id',
                              array($task_id));

        $assignees = array();
        while ($row = $db->fetchRow($sql)) {
            if ($name) {
                $assignees[0][] = $row['user_id'];
                $assignees[1][] = $row['real_name'];
            } else {
                $assignees[] = $row['user_id'];
            }
        }

        return $assignees;
    }

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
    }

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
     * returns 0 for first index, so take care if you want check when useing to check if a value exists, use ===
     *
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
	return false;
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
                Flyspray::redirect( (is_null($url) ? $baseurl : $url) );
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
    public static function validUserId($id)
    {
        global $db;

        $sql = $db->query('SELECT user_id FROM {users} WHERE user_id = ?', array(intval($id)));

        return intval($db->fetchOne($sql));
    }

    /**
     * Returns the ID of a user with $name
     * @param string $name
     * @access public static
     * @return integer 0 if the user does not exist
     * @version 1.0
     */
    public static function usernameToId($name)
    {
        global $db;

	if(!is_string($name)){
		return 0;
	}

        $sql = $db->query('SELECT user_id FROM {users} WHERE user_name = ?', array($name));

        return intval($db->fetchOne($sql));
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
            $out .= "Host: {$url['host']}\r\n";
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
    public function getNotificationOptions($noneAllowed = true)
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

    public static function weedOutTasks($user, $tasks) {
        $allowedtasks = array();
        foreach ($tasks as $task) {
            if ($user->can_view_task($task)) {
                $allowedtasks[] = $task;
            }
        }
        return $allowedtasks;
    }
}
