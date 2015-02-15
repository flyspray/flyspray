<?php

class User
{
    public $id = -1;
    public $perms = array();
    public $infos = array();
    public $searches = array();
    public $search_keys = array('project', 'string', 'type', 'sev', 'pri', 'due', 'dev', 'cat', 'status', 'percent', 'changedfrom', 'closedfrom',
                             'opened', 'closed', 'search_in_comments', 'search_in_details', 'search_for_all', 'reported', 'only_primary', 'only_watched', 'closedto',
                             'changedto', 'duedatefrom', 'duedateto', 'openedfrom', 'openedto', 'has_attachment');

    public function __construct($uid = 0)
    {
        global $db;

        if ($uid > 0) {
            $sql = $db->Query('SELECT *, g.group_id AS global_group, uig.record_id AS global_record_id
                                 FROM {users} u, {users_in_groups} uig, {groups} g
                                WHERE u.user_id = ? AND uig.user_id = ? AND g.project_id = 0
                                      AND uig.group_id = g.group_id',
                                array($uid, $uid));
        }

        if ($uid > 0 && $db->countRows($sql) == 1) {
            $this->infos = $db->FetchRow($sql);
            $this->id = intval($uid);
        } else {
            $this->infos['real_name'] = L('anonuser');
            $this->infos['user_name'] = '';
        }

        $this->get_perms();
    }

    /**
     * save_search
     *
     * @param string $do
     * @access public
     * @return void
     * @notes FIXME: must return something, should not merge _GET and _REQUEST with other stuff.
     */
    public function save_search($do = 'index')
    {
        global $db;

        if($this->isAnon()) {
            return;
        }

        // Only logged in users get to use the 'last search' functionality
        if ($do == 'index') {
            $arr = array();
            foreach ($this->search_keys as $key) {
                $arr[$key] = Get::val($key, ($key == 'status') ? 'open' : null);
            }
            foreach (array('order', 'sort', 'order2', 'sort2') as $key) {
                if (Get::val($key)) {
                    $arr[$key] = Get::val($key);
                }
            }

            if (Get::val('search_name')) {
                $fields = array('search_string'=> serialize($arr), 'time'=> time(),
                                'user_id'=> $this->id , 'name'=> Get::val('search_name'));

                $keys = array('name','user_id');

                $db->Replace('{searches}', $fields, $keys);
            }
        }

        $sql = $db->Query('SELECT * FROM {searches} WHERE user_id = ? ORDER BY name ASC', array($this->id));
        $this->searches = $db->FetchAllArray($sql);
    }

    public function perms($name, $project = null) {
        if (is_null($project)) {
            global $proj;
            $project = $proj->id;
        }

        if (isset($this->perms[$project][$name])) {
            return $this->perms[$project][$name];
        } else {
            return 0;
        }
    }

    public function get_perms()
    {
        global $db, $fs;

        $fields = array('is_admin', 'manage_project', 'view_tasks', 'edit_own_comments',
                'open_new_tasks', 'modify_own_tasks', 'modify_all_tasks',
                'view_comments', 'add_comments', 'edit_comments', 'edit_assignments',
                'delete_comments', 'create_attachments',
                'delete_attachments', 'view_history', 'close_own_tasks',
                'close_other_tasks', 'assign_to_self', 'assign_others_to_self',
                'add_to_assignees', 'view_reports', 'add_votes', 'group_open','view_effort','track_effort');

        $this->perms = array(0 => array());
        // Get project settings which are important for permissions
        $sql = $db->Query('SELECT project_id, others_view, project_is_active, anon_open, comment_closed
                             FROM {projects}');
        while ($row = $db->FetchRow($sql)) {
            $this->perms[$row['project_id']] = $row;
        }
        // Fill permissions for global project
        $this->perms[0] = array_map(create_function('$x', 'return 1;'), end($this->perms));

        if (!$this->isAnon()) {
            // Get the global group permissions for the current user
            $sql = $db->Query("SELECT  ".join(', ', $fields).", g.project_id, uig.record_id,
                                       g.group_open, g.group_id AS project_group
                                 FROM  {groups} g
                            LEFT JOIN  {users_in_groups} uig ON g.group_id = uig.group_id
                            LEFT JOIN  {projects} p ON g.project_id = p.project_id
                                WHERE  uig.user_id = ?
                             ORDER BY  g.project_id, g.group_id ASC",
                                array($this->id));

            while ($row = $db->FetchRow($sql)) {
                if (!isset($this->perms[$row['project_id']])) {
                    // should not happen, so clean up the DB
                    $db->Query('DELETE FROM {users_in_groups} WHERE record_id = ?', array($row['record_id']));
                    continue;
                }

                $this->perms[$row['project_id']] = array_merge($this->perms[$row['project_id']], $row);
            }

            // Set missing permissions and attachments
            foreach ($this->perms as $proj_id => $value) {
                foreach ($fields as $key) {
                    if ($key == 'project_group') {
                        continue;
                    }

                    $this->perms[$proj_id][$key] = max($this->perms[0]['is_admin'], @$this->perms[$proj_id][$key], $this->perms[0][$key]);
                    if ($proj_id && $key != 'is_admin') {
                        $this->perms[$proj_id][$key] = max(@$this->perms[$proj_id]['manage_project'], $this->perms[$proj_id][$key]);
                    }
                }

                // nobody can upload files if uploads are disabled at the system level..
                if (!$fs->max_file_size || !is_writable(BASEDIR .'/attachments')) {
                    $this->perms[$proj_id]['create_attachments'] = 0;
                }
            }
        }
    }

    public function check_account_ok()
    {
        global $conf, $baseurl;
        // Anon users are always OK
        if ($this->isAnon()) {
            return;
        }
        $saltedpass = crypt($this->infos['user_pass'], $conf['general']['cookiesalt']);

        if (Cookie::val('flyspray_passhash') !== $saltedpass || !$this->infos['account_enabled']
                || !$this->perms('group_open', 0))
        {
            $this->logout();
            Flyspray::Redirect($baseurl);
        }
    }

    public function isAnon()
    {
        return $this->id < 0;
    }

    /* }}} */
    /* permission related {{{ */

    public function can_edit_comment($comment)
    {
        return $this->perms('edit_comments')
               || ($comment['user_id'] == $this->id && $this->perms('edit_own_comments'));
    }

    public function can_view_project($proj)
    {
        if (is_array($proj) && isset($proj['project_id'])) {
            $proj = $proj['project_id'];
        }

        return $this->perms('view_tasks', $proj)
          || ($this->perms('project_is_active', $proj)
              && ($this->perms('others_view', $proj) || $this->perms('project_group', $proj)));
    }

    public function can_view_task($task)
    {
        if ($task['task_token'] && Get::val('task_token') == $task['task_token']) {
            return true;
        }

        if ($task['opened_by'] == $this->id && !$this->isAnon()
            || (!$task['mark_private'] && ($this->perms('view_tasks', $task['project_id']) || $this->perms('others_view', $task['project_id'])))
            || $this->perms('manage_project', $task['project_id'])) {
            return true;
        }

        return !$this->isAnon() && in_array($this->id, Flyspray::GetAssignees($task['task_id']));
    }

    public function can_edit_task($task)
    {
        return !$task['is_closed']
            && ($this->perms('modify_all_tasks', $task['project_id']) ||
                    ($this->perms('modify_own_tasks', $task['project_id'])
                     && in_array($this->id, Flyspray::GetAssignees($task['task_id']))));
    }

    public function can_take_ownership($task)
    {
        $assignees = Flyspray::GetAssignees($task['task_id']);

        return ($this->perms('assign_to_self', $task['project_id']) && empty($assignees))
               || ($this->perms('assign_others_to_self', $task['project_id']) && !in_array($this->id, $assignees));
    }

    public function can_add_to_assignees($task)
    {
        return ($this->perms('add_to_assignees', $task['project_id']) && !in_array($this->id, Flyspray::GetAssignees($task['task_id'])));
    }

    public function can_close_task($task)
    {
        return ($this->perms('close_own_tasks', $task['project_id']) && in_array($this->id, $task['assigned_to']))
                || $this->perms('close_other_tasks', $task['project_id']);
    }

//admin approve user registration
    public function need_admin_approval()
    {
	global $fs;
	return $this->isAnon() && $fs->prefs['need_approval'] && $fs->prefs['anon_reg'];
    }

    public function get_group_id()
    {
    }

    public function can_self_register()
    {
        global $fs;
        return $this->isAnon() && !$fs->prefs['spam_proof'] && $fs->prefs['anon_reg'];
    }

    public function can_register()
    {
        global $fs;
        return $this->isAnon() && !$fs->prefs['need_approval'] && $fs->prefs['spam_proof'] && $fs->prefs['anon_reg'];
    }

    public function can_open_task($proj)
    {
        return $proj->id && ($this->perms('manage_project') ||
                 $this->perms('project_is_active', $proj->id) && ($this->perms('open_new_tasks') || $this->perms('anon_open', $proj->id)));
    }

    public function can_change_private($task)
    {
        return !$task['is_closed'] && ($this->perms('manage_project', $task['project_id']) || in_array($this->id, Flyspray::GetAssignees($task['task_id'])));
    }

    public function can_vote($task)
    {
        global $db;

        if (!$this->perms('add_votes', $task['project_id'])) {
            return -1;
        }

        // Check that the user hasn't already voted this task
        $check = $db->Query('SELECT vote_id
                               FROM {votes}
                              WHERE user_id = ? AND task_id = ?',
                             array($this->id, $task['task_id']));
        if ($db->CountRows($check)) {
            return -2;
        }

        // Check that the user hasn't voted more than twice this day
        $check = $db->Query('SELECT vote_id
                               FROM {votes}
                              WHERE user_id = ? AND date_time > ?',
                             array($this->id, time() - 86400));
        if ($db->CountRows($check) > 2) {
            return -3;
        }

        return 1;
    }

    public function logout()
    {
        // Set cookie expiry time to the past, thus removing them
        Flyspray::setcookie('flyspray_userid',   '', time()-60);
        Flyspray::setcookie('flyspray_passhash', '', time()-60);
        Flyspray::setcookie('flyspray_project',  '', time()-60);
        if (Cookie::has(session_name())) {
            Flyspray::setcookie(session_name(), '', time()-60);
        }

        // Unset all of the session variables.
        $_SESSION = array();
        session_destroy();

        return !$this->isAnon();
    }
   	/**
	 * Returns the activity by between dates for a project and user.
	 * @param date $startdate
	 * @param date $enddate
	 * @param integer $project_id
	 * @param integer $userid
	 * @return array used to get the count
	 * @access public
	 */
	static function getActivityUserCount($startdate, $enddate, $project_id, $userid)
	{
		global $db;
		//NOTE: from_unixtime() on mysql, to_timestamp() on PostreSQL
        $func = ('mysql' == $db->dblink->dataProvider) ? 'from_unixtime' : 'to_timestamp';
        
        $result = $db->Query("SELECT count(date({$func}(event_date))) as val
							  FROM {history} h left join {tasks} t on t.task_id = h.task_id 
							  WHERE t.project_id = ? AND h.user_id = ?
							  AND date({$func}(event_date)) 
							  BETWEEN date(?) 
							  AND date(?)", array($project_id, $userid, $startdate, $enddate));
        $result = $db->fetchCol($result);
		return $result[0];
	}
	/**
	 * Returns the day activity by the date for a project and user.
	 * @param date $date
	 * @param integer $project_id
	 * @param integer $userid
	 * @return array used to get the count
	 * @access public
	 */
	static function getDayActivityByUser($date_start, $date_end, $project_id, $userid)
	{
		global $db;
		//NOTE: from_unixtime() on mysql, to_timestamp() on PostreSQL
        $func = ('mysql' == $db->dblink->dataProvider) ? 'from_unixtime' : 'to_timestamp';
        
        $result = $db->Query("SELECT count(date({$func}(event_date))) as val, MIN(event_date) as event_date
							  FROM {history} h left join {tasks} t on t.task_id = h.task_id 
							  WHERE t.project_id = ? AND h.user_id = ?
                              AND date({$func}(event_date)) BETWEEN date(?) and date(?)
                              GROUP BY date({$func}(event_date)) ORDER BY event_date DESC", 
                              array($project_id, $userid, $date_start, $date_end));
                              
		$date1   = new \DateTime($date_start);
        $date2   = new \DateTime($date_end);
        $days    = $date1->diff($date2);
        $days    = $days->format('%a');
        $results = array();
         
        for ($i = 0; $i < $days; $i++) {
            $event_date = (string) strtotime("-{$i} day", strtotime($date_end));
            $results[date('Y-m-d', $event_date)] = 0;
        }
        
        while ($row = $result->fetchRow()) {
            $event_date           = date('Y-m-d', $row['event_date']);
            $results[$event_date] = (integer) $row['val'];
        }
        
		return array_values($results);
	}

    /* }}} */
}
