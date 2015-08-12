<?php

class Project
{
    var $id = 0;
    var $prefs = array();
    
    
    /*function SetInfosPAramLists ($param1,$param2)  {
    	global $_REQUEST;
    	$this -> lists_id =  $param1;
    	$tab  = explode(":", $param2);
    	$this -> lists_name  = $tab[0];
    	$this -> catlisttype = $tab[1];
  
    }
    /*
     *ADD DC 08/2015
     */
    function SetInfosPAramSession ()
    {
    	global $_SESSION,$_REQUEST;
    	if(isset($_SESSION))
    	{
    		if (isset($_REQUEST['params']))
    		{
    			//echo "<br>XXXXXXXXXXXXXXX__SetInfosPAramSession :".$_REQUEST['lists_id'];
    			$tab  = explode(":", $_REQUEST['params']);
    			//$tab[0] =>lists_name
    			//$tab[1] =>catlisttype
    			//echo "lists_name=>$tab[0]:catlisttype$tab[1]<br>";
    			//ADD IN SESSION
    			$_SESSION['lists_id']    = $_REQUEST['lists_id'];
    			$_SESSION['lists_name']  = trim($tab[0]);
    			$_SESSION['catlisttype'] = trim($tab[1]);
    		}
    		
    	}
    }
    /*
     *ADD DC 08/2015
     */
    function GetInfosPAramSession (&$lists_id,&$lists_name,&$catlisttype)
    {
    global $_SESSION;
    	if(isset($_SESSION)) 
    	{
		$lists_id    = $_SESSION['lists_id'];
		$lists_name  = $_SESSION['lists_name'];
		$catlisttype = $_SESSION['catlisttype'];
	    }
    }
    
    
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
        
    	switch ($type)
		{
		case 'lists':////MODE MENU USED IN ADMIN MENU "ADD LIST"
			return "SELECT  {$type}_id, {$type}_name, {$type}_type,
			list_position,
			project_id
			FROM  {list_{$type}}
			WHERE  show_in_list = 1 AND ( project_id = ?)
			$where
			ORDER BY  list_position";
		break;
		case 'listsavble'://MODE MENU USED IN ADMIN MENU "AFFECT LIST"
			$type_p = 'lists';
			return "SELECT  {$type_p}_id, {$type_p}_name, {$type_p}_type, list_position
			FROM  {list_{$type_p}}
			WHERE  show_in_list = 1 AND ( project_id = ? OR project_id = 0 )
			$where
			ORDER BY  {$type_p}_name";
		break;	
		default://DEFAULT
			return "SELECT  {$type}_id, {$type}_name, list_position
			FROM  {list_{$type}}
			WHERE  show_in_list = 1 AND ( project_id = ? OR project_id = 0 )
			$where
			ORDER BY  list_position";
		}
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

    // }}}
    //PM dependant functions {{{ (CUSTOMS FIELDS)
    //lists defined
    //TABLE : list_lists
    //add DC 10/03/2015
    //
    function listLists ($project_id = null)
    {
    	global $db;
    	//echo "f=>listLists";
    	return $db->cached_query(
    			'list_lists', $this->_list_sql('lists'), array($this->id));

    }
    
    // }}}
    //PM dependant functions {{{ (CUSTOMS FIELDS)
    //standard lists (CUSTOMS FIELDS)
    //TABLE : standard
    //add DC 16/03/2015
    //
    function standardLists ($lists_id = null)
    {
    	global $db;
    	//echo "f=>standardLists:lists_id:$lists_id";
    	return $db->cached_query('standard', $this->detail_list_sql('standard'), array($this->id,$lists_id ));
    	//$groupby = $db->GetColumnNames('{list_lists}', 'c.project_id', 'c.');
    }
    // }}}
    //PM dependant functions {{{ (CUSTOMS FIELDS)
    //idem _list_sql just adding AND lists_id = ? 
    //only used in function detailLists ) 
    //add DC 20/03/2015
    function detail_list_sql($type, $where = null)
    {
    	// sanity check.
    	if(preg_match('![^A-Za-z0-9_]!', $type)) {
    		return '';
    	} 	
    	if ($type == 'fields') //CASE call function list_fields
    	{
    		//echo "CASE:$type<br>";
    		return "SELECT  {$type}_id, {$type}_name, {$type}_type,
    		version_tense,
            default_value,
            force_default,
            value_required
    		FROM  {{$type}}
    		WHERE ( project_id = ?)
    		$where
    		ORDER BY  {$type}_name";
    	}
    	else 
    	{
        //echo "CASE:$type<br>";
    	return "SELECT  {$type}_id, {$type}_name,
    	lists_id,
    	list_position,
    	show_in_list
    	FROM  {list_{$type}}
    	WHERE ( project_id = ? OR project_id = 0 )
    	AND lists_id = ?
    	$where
    	ORDER BY  list_position";
    	} 
    	
    }
    
    //ASSIGMENT FIELDS 
    // }}}do=pm&project=xx&area=customsfields
    //listfields
    //TABLE : flyspray_fields
    //add DC 08/2015
    //
    function listfields ($project_id = null)
    {
    	global $db;
    	$where = null;
    	//Table flyspray_fields
    	$result = $db->Query("SELECT  fields_id, fields_name, fields_type,
    	version_tense,
    	default_value,
    	force_default,
    	value_required,
    	list_id
    	FROM  {fields}
    	WHERE ( project_id = ?)
    	$where
    	ORDER BY  fields_name",
    	array($project_id));

    	while ($row = $db->FetchRow($result)) 
    	{   
    		$fields_type = $row['fields_type'];
    		switch ($fields_type)
    		{
    		
    			case 1://CASE fields_type 1=>L('list')
    				$result1 = $db->Query("SELECT  fields_id, fields_name, fields_type,
    						version_tense,
    						default_value,
    						force_default,
    						value_required,
    						{fields}.list_id,
    						l.lists_name, 
    						l.lists_type, 
    						l.show_in_list
    						FROM  {fields}
    						LEFT JOIN flyspray_list_lists l ON {fields}.list_id = l.lists_id
    						WHERE      {fields}.list_id    = ?
    						AND      ( {fields}.project_id = ? )
    						AND             l.show_in_list = 1
    						$where
    						ORDER BY  {fields}.fields_name",
    						array($row['list_id'],$project_id));    						 
    						/*
    						*
    						*
    						*FROM  `flyspray_fields` f
    						 INNER JOIN flyspray_list_category lc ON f.list_id = lc.list_id
    						 WHERE f.`list_id` =51
    						 */
    						  
    						$row1 = $db->FetchRow($result1);
  
    					    //print_r($row1).'<br>';
    						$restab[] = $row1;
    						break;
    						
    						case 2://CASE fields_type 2=>L('date')
    						break;
    						
    						case 3://CASE fields_type 3=>L('text')
    					    break;
    					    
    					    case 4://CASE fields_type 4=>L('user')
    					    break;
    					    
    						default:
    			
    		}	
    	}
    //exit;	
    	//return $db->cached_query('fields', $this->detail_list_sql('fields'), array($this->id ));
    //$groupby = $db->GetColumnNames('{list_lists}', 'c.project_id', 'c.');
     //echo "fields_type == $fields_type<br>";
    // print_r($restab);
    return $restab;
    }
    
    
    // DISPLAY ALL EXISTING CATEGORY LIST 
    function listsrowslistsavble ($project_id = null)
    {
    	global $db;
    	return $db->cached_query(
    			'list_lists', $this->_list_sql('listsavble'), array($project_id));
    }
    

    
    //ASSIGMENT FIELDS 
    // }}}
    //PM dependant functions {{{ (CUSTOMS FIELDS)
    //listfieldsdetail
    //TABLE : 
    //add DC 20/03/2015
    //
    function listfieldsdetail ($project_id = null, $list_id=null)
    {
    	global $db;
    	echo "f=>listfieldetail<br>";
    	// retrieve the left and right value of the root node
    	$result = $db->Query("SELECT lft, rgt
                                FROM {list_category}
                               WHERE category_name = 'root' AND lft = 1 AND project_id = ?",
    			array($project_id));
    	$row = $db->FetchRow($result);
    	print_r($row).'<br>';
    	exit;
    	//return $db->cached_query('fields', $this->detail_list_fields_sql('fields'), array($this->id,$list_id));
    	//$groupby = $db->GetColumnNames('{list_lists}', 'c.project_id', 'c.');
    	
    }
    function detail_list_fields_sql($type, $where = null)
    {
    	// sanity check.
    	if(preg_match('![^A-Za-z0-9_]!', $type)) {
    		return '';
    	}
    	
    	/*	return "SELECT  t.{$type}_id, t.{$type}_name, t.{$type}_type,
    		t.version_tense,
    		t.default_value,
    		t.force_default,
    		t.value_required
    		FROM  {{$type}} t
    		WHERE ( project_id = ?)
    		$where
    		ORDER BY  t.{$type}_name";
    		*/
 
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
    
    //#########################
    //NEW DC 01/08/2015
    //(CUSTOM FIELDS)
    //#########################
    function listCategoriesCustFields($project_id = null, $list_id = null, $hide_hidden = true, $remove_root = true, $depth = true)
    {
    	global $db, $conf;
    
    	// start with a empty arrays
    	$right  = array();
    	$cats   = array();
    	$g_cats = array();
    
    	// null = categories of current project + global project, int = categories of specific project
    	if (is_null($project_id)) {
    		$project_id = $this->id;
    		if ($this->id != 0) {
    			$g_cats = $this->listCategories(0);
    		}
    	}
    
    	// retrieve the left and right value of the root node
    	//Start lft to 0
    	$result = $db->Query("SELECT lft, rgt
                                FROM {list_category}
                               WHERE category_name = 'root' AND lft = 0 AND list_id = ?",
    			array($list_id));
    	$row = $db->FetchRow($result);
    
    	$groupby = $db->GetColumnNames('{list_category}', 'c.category_id', 'c.');
    
    	// now, retrieve all descendants of the root node
    	$result = $db->Query('SELECT c.category_id, c.category_name, c.*, count(t.task_id) AS used_in_tasks
                                FROM {list_category} c
                           LEFT JOIN {tasks} t ON (t.product_category = c.category_id)
                               WHERE c.list_id = ? AND lft BETWEEN ? AND ?
                            GROUP BY ' . $groupby . '
                            ORDER BY lft ASC',
    			array($list_id, intval($row['lft']), intval($row['rgt'])));
    
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
        //print_r($cats[0]);
    	return array_merge($cats, $g_cats);
    }
    /*
   function listCategories($project_id = null, $hide_hidden = true, $remove_root = true, $depth = true)

    //function listCategories($project_id = null, $list_id = null,$hide_hidden = true, $remove_root = true, $depth = true)
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
                               WHERE category_name = 'root' AND lft = 1 AND project_id = ? AND list_id = ?",
                             array($project_id),array($list_id));
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
*/
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
        
		$result = $db->Query("SELECT count(date({$func}(event_date))) as val, event_date
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
