<?php

class Project
{
    var $id = 0;
    var $prefs = array();

    function Project($id)
    {
        global $db, $fs;

        if (is_numeric($id)) {
            $sql = $db->Query("SELECT p.*, c.content AS pm_instructions, c.last_updated AS cache_update
                                 FROM {projects} p
                            LEFT JOIN {cache} c ON c.topic = p.project_id AND c.type = 'msg'
                                WHERE p.project_id = ?", array($id));
            if ($db->countRows($sql)) {
                $this->prefs = $db->FetchRow($sql);
                $this->id    = (int) $this->prefs['project_id'];
                return;
            }
        }

        $this->id = 0;
        $this->prefs['project_title'] = L('allprojects');
        $this->prefs['feed_description']  = L('feedforall');
        $this->prefs['theme_style']   = $fs->prefs['global_theme'];
        $this->prefs['lang_code']   = $fs->prefs['lang_code'];
        $this->prefs['project_is_active'] = 1;
        $this->prefs['others_view'] = 1;
        $this->prefs['intro_message'] = '';
        $this->prefs['anon_open'] = 0;
        $this->prefs['feed_img_url'] = '';
        $this->prefs['default_entry'] = 'index';
        $this->prefs['notify_reply'] = '';
        $this->prefs['default_due_version'] = 'Undecided';
        $this->prefs['disable_lostpw']=0;
        $this->prefs['disable_changepw'] = 0;
    }

    function setCookie()
    {
        Flyspray::setCookie('flyspray_project', $this->id);
    }

    /* cached list functions {{{ */

    // helpers {{{

    function _pm_list_sql($type, $join)
    {
        global $db;

        // deny the possibility of shooting ourselves in the foot.
        // although there is no risky usage atm, the api should never do unexpected things.
        if(preg_match('![^A-Za-z0-9_]!', $type)) {
            return '';
        }
        //Get the column names of list tables for the group by statement
        $groupby = $db->GetColumnNames('{list_' . $type . '}',  'l.' . $type . '_id', 'l.');

        $join = 't.'.join(" = l.{$type}_id OR t.", $join)." = l.{$type}_id";

        return "SELECT  l.*, count(t.task_id) AS used_in_tasks
                  FROM  {list_{$type}} l
             LEFT JOIN  {tasks}        t  ON ($join)
                            AND t.project_id = l.project_id
                 WHERE  l.project_id = ?
              GROUP BY  $groupby
              ORDER BY  list_position";
    }

    /**
     * _list_sql
     *
     * @param mixed $type
     * @param mixed $where
     * @access protected
     * @return string
     * @notes The $where parameter is dangerous, think twice what you pass there..
     */

    function _list_sql($type, $where = null)
    {
        // sanity check.
        if(preg_match('![^A-Za-z0-9_]!', $type)) {
            return '';
        }

        return "SELECT  {$type}_id, {$type}_name
                  FROM  {list_{$type}}
                 WHERE  show_in_list = 1 AND ( project_id = ? OR project_id = 0 )
                        $where
              ORDER BY  list_position";
    }

    // }}}
    // PM dependant functions {{{

    function listTaskTypes($pm = false)
    {
        global $db;
        if ($pm) {
            return $db->cached_query(
                    'pm_task_types',
                    $this->_pm_list_sql('tasktype', array('task_type')),
                    array($this->id));
        } else {
            return $db->cached_query(
                    'task_types', $this->_list_sql('tasktype'), array($this->id));
        }
    }

    function listOs($pm = false)
    {
        global $db;
        if ($pm) {
            return $db->cached_query(
                    'pm_os',
                    $this->_pm_list_sql('os', array('operating_system')),
                    array($this->id));
        } else {
            return $db->cached_query('os', $this->_list_sql('os'),
                    array($this->id));
        }
    }

    function listVersions($pm = false, $tense = null, $reported_version = null)
    {
        global $db;

        $params = array($this->id);

        if (is_null($tense)) {
            $where = '';
        } else {
            $where = 'AND version_tense = ?';
            $params[] = $tense;
        }

        if ($pm) {
            return $db->cached_query(
                    'pm_version',
                    $this->_pm_list_sql('version', array('product_version', 'closedby_version')),
                    array($params[0]));
        } elseif (is_null($reported_version)) {
            return $db->cached_query(
                    'version_'.$tense,
                    $this->_list_sql('version', $where),
                    $params);
        } else {
            $params[] = $reported_version;
            return $db->cached_query(
                    'version_'.$tense,
                    $this->_list_sql('version', $where . ' OR version_id = ?'),
                    $params);
        }
    }


    function listCategories($project_id = null, $hide_hidden = true, $remove_root = true, $depth = true)
    {
        global $db, $conf;

        // start with a empty arrays
        $right = array();
        $cats = array();
        $g_cats = array();

        // null = categories of current project + global project, int = categories of specific project
        if (is_null($project_id)) {
            $project_id = $this->id;
            if ($this->id != 0) {
                $g_cats = $this->listCategories(0);
            }
        }

        // retrieve the left and right value of the root node
        $result = $db->Query("SELECT lft, rgt
                                FROM {list_category}
                               WHERE category_name = 'root' AND lft = 1 AND project_id = ?",
                             array($project_id));
        $row = $db->FetchRow($result);

        $groupby = $db->GetColumnNames('{list_category}', 'c.category_id', 'c.');

        // now, retrieve all descendants of the root node
        $result = $db->Query('SELECT c.category_id, c.category_name, c.*, count(t.task_id) AS used_in_tasks
                                FROM {list_category} c
                           LEFT JOIN {tasks} t ON (t.product_category = c.category_id)
                               WHERE c.project_id = ? AND lft BETWEEN ? AND ?
                            GROUP BY ' . $groupby . '
                            ORDER BY lft ASC',
                             array($project_id, intval($row['lft']), intval($row['rgt'])));

        while ($row = $db->FetchRow($result)) {
            if ($hide_hidden && !$row['show_in_list'] && $row['lft'] != 1) {
                continue;
            }

           // check if we should remove a node from the stack
           while (count($right) > 0 && $right[count($right)-1] < $row['rgt']) {
               array_pop($right);
           }
           $cats[] = $row + array('depth' => count($right)-1);

           // add this node to the stack
           $right[] = $row['rgt'];
        }

        // Adjust output for select boxes
        if ($depth) {
            foreach ($cats as $key => $cat) {
                if ($cat['depth'] > 0) {
                    $cats[$key]['category_name'] = str_repeat('...', $cat['depth']) . $cat['category_name'];
                    $cats[$key]['1'] = str_repeat('...', $cat['depth']) . $cat['1'];
                }
            }
        }

        if ($remove_root) {
            unset($cats[0]);
        }

        return array_merge($cats, $g_cats);
    }

    function listResolutions($pm = false)
    {
        global $db;
        if ($pm) {
            return $db->cached_query(
                    'pm_resolutions',
                    $this->_pm_list_sql('resolution', array('resolution_reason')),
                    array($this->id));
        } else {
            return $db->cached_query('resolution',
                    $this->_list_sql('resolution'), array($this->id));
        }
    }

    function listTaskStatuses($pm = false)
    {
        global $db;
        if ($pm) {
            return $db->cached_query(
                    'pm_statuses',
                    $this->_pm_list_sql('status', array('item_status')),
                    array($this->id));
        } else {
            return $db->cached_query('status',
                    $this->_list_sql('status'), array($this->id));
        }
    }

    // }}}

    function listUsersIn($group_id = null)
    {
        global $db;
        return $db->cached_query(
                'users_in'.(is_null($group_id) ? $group_id : intval($group_id)),
                "SELECT  u.*
                   FROM  {users}           u
             INNER JOIN  {users_in_groups} uig ON u.user_id = uig.user_id
             INNER JOIN  {groups}          g   ON uig.group_id = g.group_id
                  WHERE  g.group_id = ?
               ORDER BY  u.user_name ASC",
                array($group_id));
    }

    function listAttachments($cid)
    {
        global $db;
        return $db->cached_query(
                'attach_'.intval($cid),
                "SELECT  *
                   FROM  {attachments}
                  WHERE  comment_id = ?
               ORDER BY  attachment_id ASC",
               array($cid));
    }

    function listLinks($cid)
    {
        global $db;
	return $db->cached_query(
		'link_'.intval($cid),
		"SELECT *
		   FROM {links}
		   WHERE comment_id = ?
		ORDER BY link_id ASC",
		array($cid));
    }

    function listTaskAttachments($tid)
    {
        global $db;
        return $db->cached_query(
                'attach_'.intval($tid),
                "SELECT  *
                   FROM  {attachments}
                  WHERE  task_id = ? AND comment_id = 0
               ORDER BY  attachment_id ASC",
               array($tid));
    }

    function listTaskLinks($tid)
    {
        global $db;
	return $db->cached_query(
		'link_'.intval($tid),
		"SELECT *
		FROM {links}
		WHERE task_id = ? AND comment_id = 0
		ORDER BY link_id ASC",
		array($tid));
    }
	/**
	 * Returns the activity by between dates for a project.
	 * @param date $startdate
	 * @param date $enddate
	 * @param integer $project_id
	 * @return array used to get the count
	 * @access public
	 */
	static function getActivityProjectCount($startdate, $enddate, $project_id)
	{
		global $db;
		//NOTE: from_unixtime() on mysql, to_timestamp() on PostreSQL
        $func = ('mysql' == $db->dblink->dataProvider) ? 'from_unixtime' : 'to_timestamp';
        
		$result = $db->Query("SELECT count(date({$func}(event_date))) as val
		FROM {history} h left join {tasks} t on t.task_id = h.task_id 
		WHERE t.project_id = ?
		AND date({$func}(event_date)) BETWEEN date(?) and date(?)", array($project_id, $startdate, $enddate));
        
        $result = $db->fetchCol($result);
		return $result[0];
	}
	/**
	 * Returns the day activity by the date for a project.
	 * @param date $date
	 * @param integer $project_id
	 * @return array used to get the count
	 * @access public
	 */
	static function getDayActivityByProject($date_start, $date_end, $project_id)
	{
		global $db;
		//NOTE: from_unixtime() on mysql, to_timestamp() on PostreSQL
        $func = ('mysql' == $db->dblink->dataProvider) ? 'from_unixtime' : 'to_timestamp';
        
		$result = $db->Query("SELECT count(date({$func}(event_date))) as val, MIN(event_date) as event_date
							  FROM {history} h left join {tasks} t on t.task_id = h.task_id 
							  WHERE t.project_id = ? 
							  AND date({$func}(event_date)) BETWEEN date(?) and date(?)
                              GROUP BY date({$func}(event_date)) ORDER BY event_date DESC", 
                              array($project_id, $date_start, $date_end));
        
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
