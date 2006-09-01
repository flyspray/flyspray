<?php

class Project
{
    var $id = 0;
    var $prefs = array();

    function Project($id)
    {
        global $db, $fs;

        if ($id) {
            $sql = $db->Query("SELECT p.*, c.content AS pm_instructions, c.last_updated AS cache_update
                                 FROM {projects} p
                            LEFT JOIN {cache} c ON c.topic = p.project_id AND c.type = 'msg'
                                WHERE p.project_id = ?", array($id));
            if ($db->countRows($sql)) {
                $this->prefs = $db->FetchRow($sql);
                $this->id    = $this->prefs['project_id'];
                return;
            }
        }
        
        $this->id = 0;
        $this->prefs['project_title'] = L('allprojects');
        $this->prefs['theme_style']   = $fs->prefs['global_theme'];
        $this->prefs['lang_code']   = $fs->prefs['lang_code'];
        $this->prefs['project_is_active'] = 1;
        $this->prefs['others_view'] = 1;
        $this->prefs['intro_message'] = '';
        $this->prefs['anon_open'] = 0;
        $this->prefs['feed_description']  = L('feedforall');
        $this->prefs['feed_img_url'] = '';
        $this->prefs['default_entry'] = 'index';
        $this->prefs['notify_reply'] = '';
    }

    function setCookie()
    {
        Flyspray::setCookie('flyspray_project', $this->id);
    }

    /* cached list functions {{{ */

    // helpers {{{

    function _pm_list_sql($type, $join)
    {
        global $db, $conf;
        //Get the column names of list tables for the group by statement
        $groupby = $db->GetColumnNames('{list_' . $type . '}',  'l.' . $type . '_id', 'l.');

        settype($join, 'array');
        $join = 't.'.join(" = l.{$type}_id OR t.", $join)." = l.{$type}_id";
        return "SELECT  l.*, count(t.task_id) AS used_in_tasks
                  FROM  {list_{$type}} l
             LEFT JOIN  {tasks}        t  ON ($join)
                            AND t.project_id = l.project_id
                 WHERE  l.project_id = ?
              GROUP BY  $groupby
              ORDER BY  list_position";
    }

    function _list_sql($type, $where = null)
    {
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
                    $this->_pm_list_sql('tasktype', 'task_type'),
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
                    $this->_pm_list_sql('os', 'operating_system'),
                    array($this->id));
        } else {
            return $db->cached_query('os', $this->_list_sql('os'),
                    array($this->id));
        }
    }

    function listVersions($pm = false, $tense = null, $reported_version = null)
    {
        global $db;
        if (is_null($tense)) {
            $where = '';
        } else {
            $where = 'AND version_tense = ' . $db->qstr($tense);
        }
        
        if ($pm) {
            return $db->cached_query(
                    'pm_version',
                    $this->_pm_list_sql('version', array('product_version', 'closedby_version')),
                    array($this->id));
        } elseif (is_null($reported_version)) {
            return $db->cached_query(
                    'version_'.$tense,
                    $this->_list_sql('version', $where),
                    array($this->id));
        } else {
            return $db->cached_query(
                    'version_'.$tense,
                    $this->_list_sql('version', $where . ' OR version_id = '. $db->qstr($reported_version) ),
                    array($this->id));
        }
    }
    
    
    function listCategories($project_id = null, $remove_root = true, $depth = true)
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
           // only check stack if there is one
           if (count($right) > 0) {
               // check if we should remove a node from the stack
               while ($right[count($right)-1] < $row['rgt']) {
                   array_pop($right);
               }
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
                    $this->_pm_list_sql('resolution', 'resolution_reason'),
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
                    $this->_pm_list_sql('status', 'item_status'),
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
                'users_in'.$group_id,
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
                'attach_'.$cid,
                "SELECT  *
                   FROM  {attachments}
                  WHERE  comment_id = ?
               ORDER BY  attachment_id ASC",
               array($cid));
    }

    function listTaskAttachments($tid)
    {
        global $db;
        return $db->cached_query(
                'attach_'.$tid,
                "SELECT  *
                   FROM  {attachments}
                  WHERE  task_id = ? AND comment_id = 0
               ORDER BY  attachment_id ASC",
               array($tid));
    }
    /* }}} */
}

?>
