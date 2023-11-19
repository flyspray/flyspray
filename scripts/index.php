<?php

/*
   This script sets up and shows the tasklist page.
   It is for historical reason called index.php, because it was also the frontpage.
   But now there can be a different pagetype set up as frontpage in Flyspray.
*/


if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

// Need to get function ConvertSeconds
require_once BASEDIR . '/includes/class.effort.php';

if (!$user->can_select_project($proj->id)) {
    $proj = new Project(0);
}

$perpage = '50';
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

// Remove columns the user is not allowed to see
if (in_array('estimated_effort', $visible) && !$user->perms('view_estimated_effort')) {
    unset($visible[array_search('estimated_effort', $visible)]);
}
if (in_array('effort', $visible) && !$user->perms('view_current_effort_done')) {
    unset($visible[array_search('effort', $visible)]);
}

# for csv export no paging limits
if (Get::has('export_list')) {
        $offset = -1;
        $perpage = -1;
}

# Reduce searchform parameters for shorter tasklink URLs after performing a search.
if (isset($_GET['type']) && is_array($_GET['type']) && count($_GET['type'])===1 && current($_GET['type'])==='') {
	unset($_GET['type']);
}
if (isset($_GET['sev']) && is_array($_GET['sev']) && count($_GET['sev'])===1 && current($_GET['sev'])==='') {
	unset($_GET['sev']);
}
if (isset($_GET['pri']) && is_array($_GET['pri']) && count($_GET['pri'])===1 && current($_GET['pri'])==='') {
	unset($_GET['pri']);
}
if (isset($_GET['due']) && is_array($_GET['due']) && count($_GET['due'])===1 && current($_GET['due'])==='') {
	unset($_GET['due']);
}
if (isset($_GET['cat']) && is_array($_GET['cat']) && count($_GET['cat'])===1 && current($_GET['cat'])==='') {
	unset($_GET['cat']);
}
if (isset($_GET['reported']) && is_array($_GET['reported']) && count($_GET['reported'])===1 && current($_GET['reported'])==='') {
	unset($_GET['reported']);
}
// 'open' is default, '' means all tasks (open and closed)
if (isset($_GET['status']) && is_array($_GET['status']) && count($_GET['status'])===1 && current($_GET['status'])==='open') {
	unset($_GET['status']);
}
if (isset($_GET['percent']) && is_array($_GET['percent']) && count($_GET['percent'])===1 && current($_GET['percent'])==='') {
	unset($_GET['percent']);
}

if(!Get::val('string')){
	unset($_GET['string']);
}

# users
if(!Get::val('opened')){
	unset($_GET['opened']);
}
if(!Get::val('dev')){
	unset($_GET['dev']);
}
if(!Get::val('closed')){
	unset($_GET['closed']);
}

# dates
if(!Get::val('duedatefrom')){
	unset($_GET['duedatefrom']);
}
if(!Get::val('duedateto')){
	unset($_GET['duedateto']);
}
if(!Get::val('changedfrom')){
	unset($_GET['changedfrom']);
}
if(!Get::val('changedto')){
	unset($_GET['changedto']);
}
if(!Get::val('openedfrom')){
	unset($_GET['openedfrom']);
}
if(!Get::val('openedto')){
	unset($_GET['openedto']);
}
if(!Get::val('closedfrom')){
	unset($_GET['closedfrom']);
}
if(!Get::val('closedto')){
	unset($_GET['closedto']);
}

list($tasks, $id_list, $totalcount, $forbiddencount) = Backend::get_task_list($_GET, $visible, $offset, $perpage);

if (Get::has('export_list')) {
	export_task_list();
}

$page->uses('tasks', 'offset', 'perpage', 'pagenum', 'visible');

// List of task IDs for next/previous links
# Mmh the result is persistent in $_SESSION a bit for the length of each user session and can lead to a DOS quite fast on bigger installs?
# Do we really need prev-next on task details view or can we find an alternative solution?
# And using the $_SESSION for that is currently not working correct if someone uses 2 browser tabs for 2 different projects.
$_SESSION['tasklist'] = $id_list;

$page->assign('total', $totalcount);
$page->assign('forbiddencount', $forbiddencount);

// Send user variables to the template

$result = $db->query('SELECT DISTINCT u.user_id, u.user_name, u.real_name, g.group_name, g.project_id
                            FROM {users} u
                       LEFT JOIN {users_in_groups} uig ON u.user_id = uig.user_id
                       LEFT JOIN {groups} g ON g.group_id = uig.group_id
                           WHERE (g.show_as_assignees = 1 OR g.is_admin = 1)
                                 AND (g.project_id = 0 OR g.project_id = ?) AND u.account_enabled = 1
                        ORDER BY g.project_id ASC, g.group_name ASC, u.user_name ASC', ($proj->id || -1)); // FIXME: -1 is a hack. when $proj->id is 0 the query fails
$userlist = array();
while ($row = $db->fetchRow($result)) {
    $userlist[$row['group_name']][] = array(0 => $row['user_id'],
                                            1 => sprintf('%s (%s)', $row['user_name'], $row['real_name']));
}

$page->assign('userlist', $userlist);

/**
 * tpl function that Displays a header cell for report list
 */
function tpl_list_heading($colname, $format = "<th%s>%s</th>")
{
    global $proj, $page;
    $imgbase = '<img src="%s" alt="%s" />';
    $class   = $colname;
    $html    = eL($colname);
/*
    if ($colname == 'comments' || $colname == 'attachments') {
        $html = sprintf($imgbase, $page->get_image(substr($colname, 0, -1)), $html);
    }
*/
	if ($colname == 'attachments') {
		$html='<i class="fa fa-paperclip fa-lg" title="'.$html.'"></i>';
	}
	if ($colname == 'comments') {
		$html='<i class="fa fa-comments fa-lg" title="'.$html.'"></i>';
	}
	if ($colname == 'votes') {
		$html='<i class="fa fa-star-o fa-lg" title="'.$html.'"></i>';
	}

    if (Get::val('order') == $colname) {
        $class .= ' orderby';
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
	# unneeded or duplicate params from $_GET for the sort links
	$params=array_merge($_GET, $new_order);
	unset($params['do']);
	unset($params['project']);
	unset($params['switch']);
	# resorting a search result should show always the first results 
        unset($params['pagenum']);
	
	$html = sprintf('<a title="%s" href="%s">%s</a>',
		eL('sortthiscolumn'), Filters::noXSS(createURL('tasklist', $proj->id, null, $params )), $html);

	return sprintf($format, ' class="'.$class.'"', $html);
}


/**
 * tpl function that  draws a cell
 */
function tpl_draw_cell($task, $colname, $format = "<td class='%s'>%s</td>") {
	global $fs, $db, $proj, $page, $user;

	$indexes = array (
            'id'         => 'task_id',
            'project'    => 'project_title',
            'tasktype'   => 'task_type',
            'tasktypename'=> 'tasktype_name',
            'category'   => 'category_name',
            'severity'   => '',
            'priority'   => '',
            'summary'    => 'item_summary',
            'dateopened' => 'date_opened',
            'status'     => 'status_name',
            'openedby'   => 'opened_by',
            'openedbyname'=> 'opened_by_name',
            'assignedto' => 'assigned_to_name',
            'lastedit'   => 'max_date',
            'editedby'   => 'last_edited_by',
            'reportedin' => 'product_version_name',
            'dueversion' => 'closedby_version_name',
            'duedate'    => 'due_date',
            'comments'   => 'num_comments',
            'votes'      => 'num_votes',
            'attachments'=> 'num_attachments',
            'dateclosed' => 'date_closed',
            'closedby'   => 'closed_by',
            'commentedby'=> 'commented_by',
            'progress'   => '',
            'os'         => 'os_name',
            'private'    => 'mark_private',
            'parent'     => 'supertask_id',
            'estimatedeffort' => 'estimated_effort',
	);

    //must be an array , must contain elements and be alphanumeric (permitted  "_")
    if(!is_array($task) || empty($task) || preg_match('![^A-Za-z0-9_]!', $colname)) {
        //run away..
        return '';
    }
	$class= 'task_'.$colname;

	switch ($colname) {
        case 'id':
            $value = tpl_tasklink($task, $task['task_id']);
            break;
        case 'summary':
		$value = tpl_tasklink($task, utf8_substr($task['item_summary'], 0, 55));
		if (utf8_strlen($task['item_summary']) > 55) {
			$value .= '...';
		}

		if($task['tagids']!=''){
			#$tags=explode(',', $task['tags']);
			$tagids=explode(',', $task['tagids']);
			#$tagclass=explode(',', $task['tagclass']);
			$tgs='';
			for($i=0;$i< count($tagids); $i++){
				$tgs.=tpl_tag($tagids[$i]);
			}
                        $value.=$tgs;
		}
            break;

        case 'tasktype':
            $value = htmlspecialchars($task['tasktype_name'], ENT_QUOTES, 'utf-8');
            $class.=' typ'.$task['task_type'];
            break;

        case 'severity':
            $value = $task['task_severity']==0 ? '' : $fs->severities[$task['task_severity']];
            $class.=' sev'.$task['task_severity'];
            break;

        case 'priority':
            $value = $task['task_priority']==0 ? '' : $fs->priorities[$task['task_priority']];
            $class.=' pri'.$task['task_priority'];
            break;

	case 'attachments':
	case 'comments':
	case 'votes':
		$value = $task[$indexes[$colname]]>0 ? $task[$indexes[$colname]]:'';
		break;

	case 'lastedit':
	case 'dateopened':
	case 'dateclosed':
		$value = formatDate($task[$indexes[$colname]]);
		break;

	case 'duedate':
		# TODO: calc for duetoday, calc duewarn period, with correct timezones and DST
		# and use of $fs->prefs['days_before_alert']
		if ($task[$indexes[$colname]] < time()) {
			$class.=' overdue';
		}
		$value = formatDate($task[$indexes[$colname]]);
		break;

        case 'status':
            if ($task['is_closed']) {
                $value = eL('closed');
            } else {
                $value = htmlspecialchars($task[$indexes[$colname]], ENT_QUOTES, 'utf-8');
            }
            $class.=' sta'.$task['item_status'];
            break;

        case 'progress':
            $value = tpl_img($page->get_image('percent-' . $task['percent_complete'], false),
                    $task['percent_complete'] . '%');
            break;

        case 'assignedto':
		# group_concat-ed for mysql/pgsql
		#$value = htmlspecialchars($task[$indexes[$colname]], ENT_QUOTES, 'utf-8');
		$value='';
		$anames=explode(',',$task[$indexes[$colname]]);
		$aids=explode(',',$task['assignedids']);
		$aimages=explode(',',$task['assigned_image']);
		for($a=0;$a < count($anames);$a++){
			if($aids[$a]){
				# deactivated: avatars looks too ugly in the tasklist, user's name needs to be visible on a first look here, without needed mouse hovering..
				#if ($fs->prefs['enable_avatars']==1 && $aimages[$a]){
				#	$value.=tpl_userlinkavatar($aids[$a],30);
				#} else{
					$value.=tpl_userlink($aids[$a]);
				#}
				#$value.='<a href="'.$aids[$a].'">'.htmlspecialchars($anames[$a], ENT_QUOTES, 'utf-8').'</a>';
			}
		}

		# fallback for DBs we haven't written sql string aggregation yet (currently with group_concat() mysql and array_agg() postgresql)
		if( ('postgres' != $db->dblink->dataProvider) && ('mysql' != $db->dblink->dataProvider) && ($task['num_assigned'] > 1)) {
			$value .= ', +' . ($task['num_assigned'] - 1);
		}
		break;

        case 'private':
            $value = $task[$indexes[$colname]] ? L('yes') : L('no');
            break;

        case 'commentedby':
        case 'openedby':
        case 'editedby':
        case 'closedby':
                $value = '';
                # a bit expensive! tpl_userlinkavatar()  an additional sql query for each new user in the output table
                # at least tpl_userlink() uses a $cache array so query for repeated users
		if ($task[$indexes[$colname]] > 0) {
			# deactivated: avatars looks too ugly in the tasklist, user's name needs to be visible on a first look here, without needed mouse hovering..
			#if ($fs->prefs['enable_avatars']==1){
			#	$value = tpl_userlinkavatar($task[$indexes[$colname]],30);
			#} else{
				$value = tpl_userlink($task[$indexes[$colname]]);
			#}
		}
                break;

        case 'parent':
            $value = '';
            if ($task['supertask_id'] > 0) {
                $value = tpl_tasklink($task, $task['supertask_id']);
            }
            break;

	case 'estimatedeffort':
            $value = '';
            if ($user->perms('view_estimated_effort')) {
		if ($task['estimated_effort'] > 0){
                    $value = effort::secondsToString($task['estimated_effort'], $proj->prefs['hours_per_manday'], $proj->prefs['estimated_effort_format']);
		}
            }
            break;

	case 'effort':
            $value = '';
            if ($user->perms('view_current_effort_done')) {
		if ($task['effort'] > 0){
                    $value = effort::secondsToString($task['effort'], $proj->prefs['hours_per_manday'], $proj->prefs['current_effort_done_format']);
                }
            }
            break;

        default:
            $value = '';
            // $colname here is NOT column name in database but a name that can appear
            // both in a projects visible fields and as a key in language translation
            // file, which is also used to draw a localized heading. Column names in
            // database customarily use _ t to separate words, translation file entries
            // instead do not and can be also be quite different. If you do see an empty
            // value when you expected something, check your usage, what visible fields
            // in database actually constains, and maybe add a mapping from $colname to
            // to the database column name to array $indexes at the beginning of this
            // function. Note that inconsistencies between $colname, database column
            // name, translation entry key and name in visible fields do occur sometimes
            // during development phase.
            if (array_key_exists($colname, $indexes)) {
        	$value = htmlspecialchars($task[$indexes[$colname]], ENT_QUOTES, 'utf-8');
            }
            break;
	}
	return sprintf($format, $class, $value);
}

$sort;
$orderby;

/**
 *
 * comparison function used by export_task_list
 *
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

/**
* workaround fputcsv() bug https://bugs.php.net/bug.php?id=43225
*/
function my_fputcsv($handle, $fields)
{
  $out = array();

  foreach ($fields as $field) {
    if (empty($field)) {
      $out[] = '';
    }
    elseif (preg_match('/^\d+(\.\d+)?$/', $field)) {
      $out[] = $field;
    }
    else {
      $out[] = '"' . preg_replace('/"/', '""', $field) . '"';
    }
  }

  return fwrite($handle, implode(',', $out) . "\n");
}


/**
 * Export the tasks as a .csv file
 * Currently only a fixed list of task fields
 */
function export_task_list()
{
	global $tasks, $fs, $user, $sort, $orderby, $proj;

	if (!is_array($tasks)){
		return;
	}

	# TODO enforcing user permissions on allowed fields
	# TODO Flyspray 1.1 or later: selected fields by user request, saved user settings, tasklist settings or project defined list which fields should appear in an export
	# TODO Flyspray 1.1 or later: export in .ods open document spreadsheet, .xml ....
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
            'detailed_desc'=>'detailed_desc',
        );


        # we can put this info also in the filename ...
        #$projectinfo = array('Project ', $tasks[0]['project_title'], date("H:i:s d-m-Y") );

        // sort the tasks into the order selected by the user. Set
        // global vars for use by sort comparison function

        $sort = Get::safe('sort','desc') == 'desc' ? 'desc' : 'asc';
        $field = Get::safe('order', 'id');

        if ($field == '') $field = 'id';
        $orderby = $indexes[ $field ];

        usort($tasks, "do_cmp");

        $outfile = str_replace(' ', '_', $proj->prefs['project_title']).'_'.date("Y-m-d").'.csv';

        #header('Content-Type: application/csv');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename='.$outfile);
        header('Content-Transfer-Encoding: text');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        #header('Pragma: public');
        #header('Content-Length: '.strlen($result)); # unknown at this time..
        ob_clean();
        flush();

	$output = fopen('php://output', 'w');
	$headings= array(
		'ID',
		'Category',
		'Task Type',
		'Severity',
		'Summary',
		'Status',
		'Progress',
		'date_opened',
		'date_closed',
		'due_date',
		'supertask_id'
	);
	if($user->perms('view_estimated_effort') && $proj->id>0 && $proj->prefs['use_effort_tracking']){
		$headings[]='Estimated Effort';
	}
	$headings[]='Description';
	//if($user->perms('view_current_effort_done') && $proj->id>0 && $proj->prefs['use_effort_tracking']){ $headings[]='Done Effort'; }

        #fputcsv($output, $headings);
	my_fputcsv($output, $headings); # fixes 'SYLK' FS#2123 Excel problem
	foreach ($tasks as $task) {
		$row = array(
			$task['task_id'],
			$task['category_name'],
			$task['task_type'],
			$fs->severities[ $task['task_severity'] ],
			$task['item_summary'],
			$task['status_name'],
			$task['percent_complete'],
			$task['date_opened'],
			$task['date_closed'],
			$task['due_date'],
			$task['supertask_id']
		);
		if( $user->perms('view_estimated_effort') && $proj->id>0 && $proj->prefs['use_effort_tracking']){
			$row[]=$task['estimated_effort'];
		}
		$row[]=$task['detailed_desc'];
		//if( $user->perms('view_current_effort_done') && $proj->id>0 && $proj->prefs['use_effort_tracking']){ $row=$task['effort']; }

		my_fputcsv($output, $row); # fputcsv() is buggy
	}
	fclose($output);
	exit();
}

// Javascript replacement
if (Get::val('toggleadvanced')) {
    $advanced_search = intval(!Req::val('advancedsearch'));
    Flyspray::setCookie('advancedsearch', $advanced_search, time()+60*60*24*30);
    $_COOKIE['advancedsearch'] = $advanced_search;
}

/**
 * Update check
 */
if(Get::has('hideupdatemsg')) {
	unset($_SESSION['latest_version']);
} else if ($conf['general']['update_check']
	&& $user->perms('is_admin')
	&& $fs->prefs['last_update_check'] < time()-60*60*24*3) {
	if (!isset($_SESSION['latest_version'])) {
		$latest = Flyspray::remote_request('http://www.flyspray.org/version.txt', GET_CONTENTS);
		# if for some silly reason we get an empty response, we use the actual version
 		$_SESSION['latest_version'] = empty($latest) ? $fs->version : $latest ;
 		$db->query('UPDATE {prefs} SET pref_value = ? WHERE pref_name = ?', array(time(), 'last_update_check'));
	}
}
if (isset($_SESSION['latest_version']) && version_compare($fs->version, $_SESSION['latest_version'] , '<') ) {
	$page->assign('updatemsg', true);
}


$page->setTitle($fs->prefs['page_title'] . $proj->prefs['project_title'] . ': ' . L('tasklist'));
$page->pushTpl('index.tpl');

?>
