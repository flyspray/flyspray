<?php

/*
   This script sets up and shows the front page with
   the list of all available tasks that the user is
   allowed to view.
*/


if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

if (!$user->can_view_project($proj->id)) {
    $proj = new Project(0);
}

$perpage = '250';
if (isset($user->infos['tasks_perpage']) && $user->infos['tasks_perpage'] > 0) {
    $perpage = $user->infos['tasks_perpage'];
}

$pagenum = Get::num('pagenum', 1);
if ($pagenum < 1) {
  $pagenum = 1;
}
$offset = $perpage * ($pagenum - 1);

// Get the visibility state of all columns
$visible = explode(' ', trim($proj->id ? $proj->prefs['visible_columns'] : $fs->prefs['visible_columns']));
if (!is_array($visible) || !count($visible) || !$visible[0]) {
    $visible = array('id');
}

list($tasks, $id_list) = Backend::get_task_list($_GET, $visible, $offset, $perpage);

//-- Added 2/1/2014 LAE. See if user wants to export the task list
if (Get::has('export_list')) {
 export_task_list();
}

$page->uses('tasks', 'offset', 'perpage', 'pagenum', 'visible');

// List of task IDs for next/previous links
$_SESSION['tasklist'] = $id_list;
$page->assign('total', count($id_list));

// Send user variables to the template

$result = $db->Query('SELECT DISTINCT u.user_id, u.user_name, u.real_name, g.group_name, g.project_id
                            FROM {users} u
                       LEFT JOIN {users_in_groups} uig ON u.user_id = uig.user_id
                       LEFT JOIN {groups} g ON g.group_id = uig.group_id
                           WHERE (g.show_as_assignees = 1 OR g.is_admin = 1)
                                 AND (g.project_id = 0 OR g.project_id = ?) AND u.account_enabled = 1
                        ORDER BY g.project_id ASC, g.group_name ASC, u.user_name ASC', ($proj->id || -1)); // FIXME: -1 is a hack. when $proj->id is 0 the query fails
$userlist = array();
while ($row = $db->FetchRow($result)) {
    $userlist[$row['group_name']][] = array(0 => $row['user_id'],
                                            1 => sprintf('%s (%s)', $row['user_name'], $row['real_name']));
}

$page->assign('userlist', $userlist);

// tpl function that Displays a header cell for report list {{{

function tpl_list_heading($colname, $format = "<th%s>%s</th>")
{
    global $proj, $page;
    $imgbase = '<img src="%s" alt="%s" />';
    $class   = '';
    $html    = eL($colname);
    if ($colname == 'comments' || $colname == 'attachments') {
        $html = sprintf($imgbase, $page->get_image(substr($colname, 0, -1)), $html);
    }

    if (Get::val('order') == $colname) {
        $class  = ' class="orderby"';
        $sort1  = Get::safe('sort', 'desc') == 'desc' ? 'asc' : 'desc';
        $sort2  = Get::safe('sort2', 'desc');
        $order2 = Get::safe('order2');
        $html  .= '&nbsp;&nbsp;'.sprintf($imgbase, $page->get_image(Get::val('sort')), Get::safe('sort'));
    }
    else {
        $sort1  = 'desc';
        if (in_array($colname,
                    array('project', 'tasktype', 'category', 'openedby', 'assignedto')))
        {
            $sort1 = 'asc';
        }
        $sort2  = Get::safe('sort', 'desc');
        $order2 = Get::safe('order');
    }


    $new_order = array('order' => $colname, 'sort' => $sort1, 'order2' => $order2, 'sort2' => $sort2);
    $html = sprintf('<a title="%s" href="%s">%s</a>',
            eL('sortthiscolumn'), Filters::noXSS(CreateURL('index', $proj->id, null, array_merge($_GET, $new_order))), $html);

    return sprintf($format, $class, $html);
}

// }}}
// tpl function that  draws a cell {{{

function tpl_draw_cell($task, $colname, $format = "<td class='%s'>%s</td>") {
	global $fs, $proj, $page;

	$indexes = array (
            'id'         => 'task_id',
            'project'    => 'project_title',
            'tasktype'   => 'task_type',
            'category'   => 'category_name',
            'severity'   => '',
            'priority'   => '',
            'summary'    => 'item_summary',
            'dateopened' => 'date_opened',
            'status'     => 'status_name',
            'openedby'   => 'opened_by_name',
            'assignedto' => 'assigned_to_name',
            'lastedit'   => 'max_date',
            'reportedin' => 'product_version',
            'dueversion' => 'closedby_version',
            'duedate'    => 'due_date',
            'comments'   => 'num_comments',
            'votes'      => 'num_votes',
            'attachments'=> 'num_attachments',
            'dateclosed' => 'date_closed',
            'progress'   => '',
            'os'         => 'os_name',
            'private'    => 'mark_private',
            'parent'     => 'supertask_id',
        );

    //must be an array , must contain elements and be alphanumeric (permitted  "_")
    if(!is_array($task) || empty($task) || preg_match('![^A-Za-z0-9_]!', $colname)) {
        //run away..
        return '';
    }

	switch ($colname) {
        case 'id':
            $value = tpl_tasklink($task, $task['task_id']);
            break;
        case 'summary':
            $value = tpl_tasklink($task, utf8_substr($task['item_summary'], 0, 55));
            if (utf8_strlen($task['item_summary']) > 55) {
                $value .= '...';
            }
            break;

        case 'severity':
            $value = $fs->severities[$task['task_severity']];
            break;

        case 'priority':
            $value = $fs->priorities[$task['task_priority']];
            break;

        case 'lastedit':
        case 'duedate':
        case 'dateopened':
        case 'dateclosed':
            $value = formatDate($task[$indexes[$colname]]);
            break;

        case 'status':
            if ($task['is_closed']) {
                $value = eL('closed');
            } else {
                $value = htmlspecialchars($task[$indexes[$colname]], ENT_QUOTES, 'utf-8');
            }
            break;

        case 'progress':
            $value = tpl_img($page->get_image('percent-' . $task['percent_complete'], false),
                    $task['percent_complete'] . '%');
            break;

        case 'assignedto':
            $value = htmlspecialchars($task[$indexes[$colname]], ENT_QUOTES, 'utf-8');
            if ($task['num_assigned'] > 1) {
                $value .= ', +' . ($task['num_assigned'] - 1);
            }
            break;
        
        case 'private':
            $value = $task[$indexes[$colname]] ? L('yes') : L('no');
            break;

        case 'parent':
            $value = '';
            if ($task['supertask_id'] > 0) {
                $value = tpl_tasklink($task, $task['supertask_id']);
            }
            break;
            
	case 'estimated_effort':
		if ($task['estimated_effort']>0){
			$value=$task['estimated_effort'];
		}else{
			$value='';
		}
		break;
	
	case 'effort':
		$value=$task['effort'];
		break;
		
        default:
        	$value = htmlspecialchars($task[$indexes[$colname]], ENT_QUOTES, 'utf-8');
        	break;
	}
	return sprintf($format, 'task_'.$colname, $value);
}

// } }}

// }}}
// Added LAE 2/1/2014 - little function to export the tasklist into a .csv file and upload it to user browser {{{

$sort;
$orderby;

/*********************************************
*
* comparison function used by export_task_list
*
**********************************************
*/

function do_cmp($a, $b)
{
 global $sort,$orderby;

 if ($a[ $orderby ] == $b[ $orderby ]) { return 0; }

 if ($sort == 'asc')
   return ($a[ $orderby ] < $b[ $orderby ]) ? -1 : 1;
 else
   return ($a[ $orderby ] > $b[ $orderby ]) ? -1 : 1;
}


/*********************************************
*
* Export the task list as a .csv file
*
*********************************************
*/

function export_task_list() {

 global $tasks, $fs, $sort, $orderby;

 if (!is_array($tasks))
  return;

 $indexes = array (
            'id'         => 'task_id',
            'project'    => 'project_title',
            'tasktype'   => 'task_type',
            'category'   => 'category_name',
            'severity'   => 'task_severity',
            'priority'   => 'task_priority',
            'summary'    => 'item_summary',
            'dateopened' => 'date_opened',
            'status'     => 'status_name',
            'openedby'   => 'opened_by_name',
            'assignedto' => 'assigned_to_name',
            'lastedit'   => 'max_date',
            'reportedin' => 'product_version',
            'dueversion' => 'closedby_version',
            'duedate'    => 'due_date',
            'comments'   => 'num_comments',
            'votes'      => 'num_votes',
            'attachments'=> 'num_attachments',
            'dateclosed' => 'date_closed',
            'progress'   => 'percent_complete',
            'os'         => 'os_name',
            'private'    => 'mark_private',
            'supertask'  => 'supertask_id',
        );


 // first line of .csv will be project name

 $result = "Project: " .$tasks[0]['project_title'] . " - " . date("H:i:s d-m-Y") . "\r\n\r\n";

 // insert column headers

 $result .= '"ID","Category","Task Type","Severity","Summary","Status","Progress"' . "\r\n";


 // sort the tasks into the order selected by the user. Set
 // global vars for use by sort comparison function

 $sort = Get::safe('sort','desc') == 'desc' ? 'desc' : 'asc';
 $field = Get::safe('order', 'id');

 if ($field == '') $field = 'id';
 $orderby = $indexes[ $field ];

 usort($tasks, "do_cmp");	// sort the items

 // for each task create a line showing values
 // get the items

 foreach ($tasks as $task) {

  $array = array(
	$task['task_id'],
	$task['category_name'],
	$task['task_type'],
        $fs->severities[ $task['task_severity'] ],
	$task['item_summary'],
	$task['status_name'],
	$task['percent_complete'],
	);

  // create comma seperated values from array and append it to $result
 
  $result .= implode(',', $array) . "\r\n";
 }

 // now send data to user to download to their machine. First create
 // HTML header

 $outfile = "tasklist_" . date("Y-m-d") . ".csv";	// name user sees to save file as

 header('Content-Type: application/csv');
 header('Content-Disposition: attachment; filename=' . $outfile);
 header('Content-Transfer-Encoding: text');
 header('Expires: 0');
 header('Cache-Control: must-revalidate');
 header('Pragma: public');
 header('Content-Length: ' . strlen($result)); 
 ob_clean();
 flush();

 // finally send out our data

 printf ("%s", $result);
}

// } }}

// Javascript replacement
if (Get::val('toggleadvanced')) {
    $advanced_search = intval(!Req::val('advancedsearch'));
    Flyspray::setCookie('advancedsearch', $advanced_search, time()+60*60*24*30);
    $_COOKIE['advancedsearch'] = $advanced_search;
}
// Update check {{{
if(Get::has('hideupdatemsg')) {
    unset($_SESSION['latest_version']);
} else if ($conf['general']['update_check'] && $user->perms('is_admin')
           && $fs->prefs['last_update_check'] < time()-60*60*24*3) {
    if (!isset($_SESSION['latest_version'])) {
        $latest = Flyspray::remote_request('http://flyspray.org/version.txt', GET_CONTENTS);
		//if for some silly reason we get and empty response, we use the actual version
 		$_SESSION['latest_version'] = empty($latest) ? $fs->version : $latest ;
        $db->Query('UPDATE {prefs} SET pref_value = ? WHERE pref_name = ?', array(time(), 'last_update_check'));
	}
}
if (isset($_SESSION['latest_version']) && version_compare($fs->version, $_SESSION['latest_version'] , '<') ) {
    $page->assign('updatemsg', true);
}
// }}}
$page->setTitle($fs->prefs['page_title'] . $proj->prefs['project_title'] . ': ' . L('tasklist'));
$page->pushTpl('index.tpl');

?>
