<?php

  /*************************************************************\
  | Details a task (and edit it)                                |
  | ~~~~~~~~~~~~~~~~~~~~~~~~~~~~                                |
  | This script displays task details when in view mode,        |
  | and allows the user to edit task details when in edit mode. |
  | It also shows comments, attachments, notifications etc.     |
  \*************************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

$task_id = Req::num('task_id');

if ( !($task_details = Flyspray::GetTaskDetails($task_id)) ) {
    Flyspray::show_error(10);
}
if (!$user->can_view_task($task_details)) {
    Flyspray::show_error( $user->isAnon() ? 102 : 101, false);
} else{

	require_once(BASEDIR . '/includes/events.inc.php');

	if($proj->prefs['use_effort_tracking']){
		require_once(BASEDIR . '/includes/class.effort.php');
		$effort = new effort($task_id,$user->id);
		$effort->populateDetails();
		$page->assign('effort',$effort);
	}

	$page->uses('task_details');

	// Send user variables to the template
	$page->assign('assigned_users', $task_details['assigned_to']);
	$page->assign('old_assigned', implode(' ', $task_details['assigned_to']));
	$page->assign('tags', $task_details['tags']);

	$page->setTitle(sprintf('FS#%d : %s', $task_details['task_id'], $task_details['item_summary']));

	if ((Get::val('edit') || (Post::has('item_summary') && !isset($_SESSION['SUCCESS']))) && $user->can_edit_task($task_details)) {
		$result = $db->query('
			SELECT g.project_id, u.user_id, u.user_name, u.real_name, g.group_id, g.group_name
			FROM {users} u
			JOIN {users_in_groups} uig ON u.user_id = uig.user_id
			JOIN {groups} g ON g.group_id = uig.group_id
			WHERE (g.show_as_assignees = 1 OR g.is_admin = 1)
			AND (g.project_id = 0 OR g.project_id = ?)
			AND u.account_enabled = 1
			ORDER BY g.project_id ASC, g.group_name ASC, u.user_name ASC',
			($proj->id ? $proj->id : -1)
		); // FIXME: -1 is a hack. when $proj->id is 0 the query fails

		$userlist = array();
		$userids = array();
		while ($row = $db->fetchRow($result)) {
			if( !in_array($row['user_id'], $userids) ){
				$userlist[$row['group_id']][] = array(
					0 => $row['user_id'],
					1 => sprintf('%s (%s)', $row['user_name'], $row['real_name']),
					2 => $row['project_id'],
					3 => $row['group_name']
				);
				$userids[]=$row['user_id'];
			} else{
				# user is probably in a global group with assignee permission listed, so no need to show second time in a project group.
			}
		}

		if (is_array(Post::val('rassigned_to'))) {
			$page->assign('assignees', Post::val('rassigned_to'));
		} else {
			$assignees = $db->query('SELECT user_id FROM {assigned} WHERE task_id = ?', $task_details['task_id']);
			$page->assign('assignees', $db->fetchCol($assignees));
		}
		$page->assign('userlist', $userlist);

		# Build the select arrays, for 'move task' or normal taskedit
		# Then in the template just use tpl_select($xxxselect);

		# keep last selections
		$catselected=Req::val('product_category', $task_details['product_category']);
		$osselected=Req::val('operating_system', $task_details['operating_system']);
		$ttselected=Req::val('task_type', $task_details['task_type']);
		$stselected=Req::val('item_status', $task_details['item_status']);
		if(isset($move) && $move==1){
			# get global categories
			$gcats=$proj->listCategories(0);
			if( count($gcats)>0){
				foreach($gcats as $cat){
					$gcatopts[]=array('value'=>$cat['category_id'], 'label'=>$cat['category_name']);
					if($catselected==$cat['category_id']){
						$gcatopts[count($gcatopts)-1]['selected']=1;
					}
				}
				$catsel['options'][]=array('optgroup'=>1, 'label'=>L('categoriesglobal'), 'options'=>$gcatopts);
			}
			# get project categories
			$pcats=$proj->listCategories($proj->id);
			if( count($pcats)>0){
				foreach($pcats as $cat){
					$pcatopts[]=array('value'=>$cat['category_id'], 'label'=>$cat['category_name']);
					if($catselected==$cat['category_id']){
						$pcatopts[count($pcatopts)-1]['selected']=1;
					}
				}
				$catsel['options'][]=array('optgroup'=>1, 'label'=>L('categoriesproject'), 'options'=>$pcatopts);
			}
			# get target categories
			$tcats=$toproject->listCategories($toproject->id);
			if( count($tcats)>0){
				foreach($tcats as $cat){
					$tcatopts[]=array('value'=>$cat['category_id'], 'label'=>$cat['category_name']);
					if($catselected==$cat['category_id']){
						$tcatopts[count($tcatopts)-1]['selected']=1;
					}
				}
				$catsel['options'][]=array('optgroup'=>1, 'label'=>L('categoriestarget'), 'options'=>$tcatopts);
			}


			# get global task statuses
			$resgst=$db->query("SELECT status_id, status_name, list_position, show_in_list FROM {list_status} WHERE project_id=0 ORDER BY list_position");
			$gsts=$db->fetchAllArray($resgst);
			if(count($gsts)>0){
				foreach($gsts as $gst){
					$gstopts[]=array('value'=>$gst['status_id'], 'label'=>$gst['status_name']);
					if($stselected==$gst['status_id']){
						$gstopts[count($gstopts)-1]['selected']=1;
					}
				}
				$statussel['options'][]=array('optgroup'=>1, 'label'=>L('globaloptions'), 'options'=>$gstopts);
			}
			# get current project task statuses
			$rescst=$db->query("SELECT status_id, status_name, list_position, show_in_list FROM {list_status} WHERE project_id=? ORDER BY list_position", array($proj->id));
			$csts=$db->fetchAllArray($rescst);
			if(count($csts)>0){
				foreach($csts as $cst){
					$cstopts[]=array('value'=>$cst['status_id'], 'label'=>$cst['status_name']);
					if($stselected==$cst['status_id']){
						$cstopts[count($cstopts)-1]['selected']=1;
					}
				}
				$statussel['options'][]=array('optgroup'=>1, 'label'=>L('currentproject'), 'options'=>$cstopts);
			}
			# get target project task statuses
			$restst=$db->query("SELECT status_id, status_name, list_position, show_in_list FROM {list_status} WHERE project_id=? ORDER BY list_position", array($toproject->id));
			$tsts=$db->fetchAllArray($restst);
			if(count($tsts)>0){
				foreach($tsts as $tst){
					$tstopts[]=array('value'=>$tst['status_id'], 'label'=>$tst['status_name']);
					if($stselected==$tst['status_id']){
						$tstopts[count($tstopts)-1]['selected']=1;
					}
				}
				$statussel['options'][]=array('optgroup'=>1, 'label'=>L('targetproject'), 'options'=>$tstopts);
			}


			# get list global tasktypes
			$resgtt=$db->query("SELECT tasktype_id, tasktype_name, list_position, show_in_list FROM {list_tasktype} WHERE project_id=0 ORDER BY list_position");
			$gtts=$db->fetchAllArray($resgtt);
			if(count($gtts)>0){
				foreach($gtts as $gtt){
					$gttopts[]=array('value'=>$gtt['tasktype_id'], 'label'=>$gtt['tasktype_name']);
					if($ttselected==$gtt['tasktype_id']){
						$gttopts[count($gttopts)-1]['selected']=1;
					}
				}
				$tasktypesel['options'][]=array('optgroup'=>1, 'label'=>L('globaloptions'), 'options'=>$gttopts);
			}
			# get current project tasktypes
			$resctt=$db->query("SELECT tasktype_id, tasktype_name, list_position, show_in_list FROM {list_tasktype} WHERE project_id=? ORDER BY list_position", array($proj->id));
			$ctts=$db->fetchAllArray($resctt);
			if(count($ctts)>0){
				foreach($ctts as $ctt){
					$cttopts[]=array('value'=>$ctt['tasktype_id'], 'label'=>$ctt['tasktype_name']);
					if($ttselected==$ctt['tasktype_id']){
						$cttopts[count($cttopts)-1]['selected']=1;
					}
				}
				$tasktypesel['options'][]=array('optgroup'=>1, 'label'=>L('currentproject'), 'options'=>$cttopts);
			}
			# get target project tasktypes
			$resttt=$db->query("SELECT tasktype_id, tasktype_name, list_position, show_in_list FROM {list_tasktype} WHERE project_id=? ORDER BY list_position", array($toproject->id));
			$ttts=$db->fetchAllArray($resttt);
			if(count($ttts)>0){
				foreach($ttts as $ttt){
					$tttopts[]=array('value'=>$ttt['tasktype_id'], 'label'=>$ttt['tasktype_name']);
					if($ttselected==$ttt['tasktype_id']){
						$tttopts[count($tttopts)-1]['selected']=1;
					}
				}
				$tasktypesel['options'][]=array('optgroup'=>1, 'label'=>L('targetproject'), 'options'=>$tttopts);
			}

			
			# allow unset (0) value (field os_id currently defined with NOT NULL by flyspray-install.xml, so must use 0 instead null) 
			$ossel['options'][]=array('value'=>0, 'label'=>L('undecided'));
			# get global operating systems
			$resgos=$db->query("SELECT os_id, os_name, list_position, show_in_list FROM {list_os} WHERE project_id=0 ORDER BY list_position");
			$goses=$db->fetchAllArray($resgos);
			if(count($goses)>0){
				foreach($goses as $gos){
					$gosopts[]=array('value'=>$gos['os_id'], 'label'=>$gos['os_name']);
					if($osselected==$gos['os_id']){
						$gosopts[count($gosopts)-1]['selected']=1;
					}
				}
				$ossel['options'][]=array('optgroup'=>1, 'label'=>L('globaloptions'), 'options'=>$gosopts);
			}
			# get current project operating systems
			$rescos=$db->query("SELECT os_id, os_name, list_position, show_in_list FROM {list_os} WHERE project_id=? ORDER BY list_position", array($proj->id));
			$coses=$db->fetchAllArray($rescos);
			if(count($coses)>0){
				foreach($coses as $cos){
					$cosopts[]=array('value'=>$cos['os_id'], 'label'=>$cos['os_name']);
					if($osselected==$cos['os_id']){
						$cosopts[count($cosopts)-1]['selected']=1;
					}
				}
				$ossel['options'][]=array('optgroup'=>1, 'label'=>L('currentproject'), 'options'=>$cosopts);
			}
			# get target project operating systems
			$restos=$db->query("SELECT os_id, os_name, list_position, show_in_list FROM {list_os} WHERE project_id=? ORDER BY list_position", array($toproject->id));
			$toses=$db->fetchAllArray($restos);
			if(count($toses)>0){
				foreach($toses as $tos){
					$tosopts[]=array('value'=>$tos['os_id'], 'label'=>$tos['os_name']);
					if($osselected==$tos['os_id']){
						$tosopts[count($tosopts)-1]['selected']=1;
					}
				}
				$ossel['options'][]=array('optgroup'=>1, 'label'=>L('targetproject'), 'options'=>$tosopts);
			}

		}else{
			# just the normal merged global/project categories
			$cats=$proj->listCategories();
			if( count($cats)>0){
				foreach($cats as $cat){
					$catopts[]=array('value'=>$cat['category_id'], 'label'=>$cat['category_name']);
					if($catselected==$cat['category_id']){
						$catopts[count($catopts)-1]['selected']=1;
					}
				}
				$catsel['options']=$catopts;
			}

			# just the normal merged global/project statuses
			$sts=$proj->listTaskStatuses();
			if( count($sts)>0){
				foreach($sts as $st){
					$stopts[]=array('value'=>$st['status_id'], 'label'=>$st['status_name']);
					if($stselected==$st['status_id']){
						$stopts[count($stopts)-1]['selected']=1;
					}
				}
				$statussel['options']=$stopts;
			}

			# just the normal merged global/project tasktypes
			$tts=$proj->listTaskTypes();
			if( count($tts)>0){
				foreach($tts as $tt){
					$ttopts[]=array('value'=>$tt['tasktype_id'], 'label'=>$tt['tasktype_name']);
					if($ttselected==$tt['tasktype_id']){
						$ttopts[count($ttopts)-1]['selected']=1;
					}
				}
				$tasktypesel['options']=$ttopts;
			}

			# just the normal merged global/project os
			$osses=$proj->listOs();
			# also allow unsetting operating system entry
			$osopts[]=array('value'=>0, 'label'=>L('undecided'));
			if( count($osses)>0){
				foreach($osses as $os){
					$osopts[]=array('value'=>$os['os_id'], 'label'=>$os['os_name']);
					if($osselected==$os['os_id']){
						$osopts[count($osopts)-1]['selected']=1;
					}
				}
				$ossel['options']=$osopts;
			}
		}
		$catsel['name']='product_category';
		$catsel['attr']['id']='category';
		$page->assign('catselect', $catsel);

		$statussel['name']='item_status';
		$statussel['attr']['id']='status';
		$page->assign('statusselect', $statussel);

		$tasktypesel['name']='task_type';
		$tasktypesel['attr']['id']='tasktype';
		$page->assign('tasktypeselect', $tasktypesel);
		
		$ossel['name']='operating_system';
		$ossel['attr']['id']='os';
		$page->assign('osselect', $ossel);

		# user tries to move a task to a different project:
		if(isset($move) && $move==1){
			$page->assign('move', 1);
			$page->assign('toproject', $toproject);
		}
		$page->pushTpl('details.edit.tpl');
	} else {
		$prev_id = $next_id = 0;

		if (isset($_SESSION['tasklist']) && ($id_list = $_SESSION['tasklist'])
		    && ($i = array_search($task_id, $id_list)) !== false) {
			$prev_id = isset($id_list[$i - 1]) ? $id_list[$i - 1] : '';
			$next_id = isset($id_list[$i + 1]) ? $id_list[$i + 1] : '';
		}

		// Sub-Tasks
		$subtasks = $db->query('SELECT t.*, p.project_title 
                                 FROM {tasks} t
			    LEFT JOIN {projects} p ON t.project_id = p.project_id
                                WHERE t.supertask_id = ?', 
                                array($task_id));
		$subtasks_cleaned = Flyspray::weedOutTasks($user, $db->fetchAllArray($subtasks));

		for($i=0;$i<count($subtasks_cleaned);$i++){
			$subtasks_cleaned[$i]['assigned_to']=array();
			if ($assignees = Flyspray::getAssignees($subtasks_cleaned[$i]["task_id"], false)) {
				for($j=0;$j<count($assignees);$j++){
					$subtasks_cleaned[$i]['assigned_to'][$j] = tpl_userlink($assignees[$j]);
				}
			}
		}
    
		// Parent categories
		$parent = $db->query('SELECT *
                            FROM {list_category}
                           WHERE lft < ? AND rgt > ? AND project_id  = ? AND lft != 1
                        ORDER BY lft ASC',
                        array($task_details['lft'], $task_details['rgt'], $task_details['cproj']));
		// Check for task dependencies that block closing this task
		$check_deps   = $db->query('SELECT t.*, s.status_name, r.resolution_name, d.depend_id, p.project_title
                                  FROM {dependencies} d
                             LEFT JOIN {tasks} t on d.dep_task_id = t.task_id
                             LEFT JOIN {list_status} s ON t.item_status = s.status_id
                             LEFT JOIN {list_resolution} r ON t.resolution_reason = r.resolution_id
			     LEFT JOIN {projects} p ON t.project_id = p.project_id
                                 WHERE d.task_id = ?', array($task_id));
		$check_deps_cleaned = Flyspray::weedOutTasks($user, $db->fetchAllArray($check_deps));

		
		for($i=0;$i<count($check_deps_cleaned);$i++){
			$check_deps_cleaned[$i]['assigned_to']=array();
			if ($assignees = Flyspray::getAssignees($check_deps_cleaned[$i]["task_id"], false)) {
				for($j=0;$j<count($assignees);$j++){
					$check_deps_cleaned[$i]['assigned_to'][$j] = tpl_userlink($assignees[$j]);
				}
			}
		}
   
		// Check for tasks that this task blocks
		$check_blocks = $db->query('SELECT t.*, s.status_name, r.resolution_name, d.depend_id, p.project_title
                                  FROM {dependencies} d
                             LEFT JOIN {tasks} t on d.task_id = t.task_id
                             LEFT JOIN {list_status} s ON t.item_status = s.status_id
                             LEFT JOIN {list_resolution} r ON t.resolution_reason = r.resolution_id
			     LEFT JOIN {projects} p ON t.project_id = p.project_id
                                 WHERE d.dep_task_id = ?', array($task_id));
		$check_blocks_cleaned = Flyspray::weedOutTasks($user, $db->fetchAllArray($check_blocks));

				
		for($i=0;$i<count($check_blocks_cleaned);$i++){
			$check_blocks_cleaned[$i]['assigned_to']=array();
			if ($assignees = Flyspray::getAssignees($check_blocks_cleaned[$i]["task_id"], false)) {
				for($j=0;$j<count($assignees);$j++){
					$check_blocks_cleaned[$i]['assigned_to'][$j] = tpl_userlink($assignees[$j]);
				}
			}
		}

		// Check for pending PM requests
		$get_pending = $db->query("SELECT *
                                  FROM {admin_requests}
                                 WHERE task_id = ?  AND resolved_by = 0",
                                 array($task_id));

		// Get info on the dependencies again
		$open_deps = $db->query('SELECT COUNT(*) - SUM(is_closed)
                                  FROM {dependencies} d
                             LEFT JOIN {tasks} t on d.dep_task_id = t.task_id
                                 WHERE d.task_id = ?', array($task_id));

		$watching = $db->query('SELECT COUNT(*)
                                   FROM {notifications}
                                  WHERE task_id = ? AND user_id = ?',
                                  array($task_id, $user->id));

		// Check if task has been reopened some time
		$reopened = $db->query('SELECT COUNT(*)
                                   FROM {history}
                                  WHERE task_id = ? AND event_type = 13',
                                  array($task_id));

		// Check for cached version
		$cached = $db->query("SELECT content, last_updated
                            FROM {cache}
                           WHERE topic = ? AND type = 'task'",
                           array($task_details['task_id']));
		$cached = $db->fetchRow($cached);

		// List of votes
		$get_votes = $db->query('SELECT u.user_id, u.user_name, u.real_name, v.date_time
                               FROM {votes} v
                          LEFT JOIN {users} u ON v.user_id = u.user_id
                               WHERE v.task_id = ?
                            ORDER BY v.date_time DESC',
                            array($task_id));

		if ($task_details['last_edited_time'] > $cached['last_updated'] || !defined('FLYSPRAY_USE_CACHE')) {
			$task_text = TextFormatter::render($task_details['detailed_desc'], 'task', $task_details['task_id']);
		} else {
			$task_text = TextFormatter::render($task_details['detailed_desc'], 'task', $task_details['task_id'], $cached['content']);
		}

		$page->assign('prev_id',   $prev_id);
		$page->assign('next_id',   $next_id);
		$page->assign('task_text', $task_text);
		$page->assign('subtasks',  $subtasks_cleaned);
		$page->assign('deps',      $check_deps_cleaned);
		$page->assign('parent',    $db->fetchAllArray($parent));
		$page->assign('blocks',    $check_blocks_cleaned);
		$page->assign('votes',     $db->fetchAllArray($get_votes));
		$page->assign('penreqs',   $db->fetchAllArray($get_pending));
		$page->assign('d_open',    $db->fetchOne($open_deps));
		$page->assign('watched',   $db->fetchOne($watching));
		$page->assign('reopened',  $db->fetchOne($reopened));
		$page->pushTpl('details.view.tpl');

		///////////////
		// tabbed area

		// Comments + cache
		$sql = $db->query('SELECT * FROM {comments} c
                      LEFT JOIN {cache} ca ON (c.comment_id = ca.topic AND ca.type = ?)
                          WHERE task_id = ?
                       ORDER BY date_added ASC',
                           array('comm', $task_id));
		$page->assign('comments', $db->fetchAllArray($sql));

		// Comment events
		$sql = get_events($task_id, ' AND (event_type = 3 OR event_type = 14)');
		$comment_changes = array();
		while ($row = $db->fetchRow($sql)) {
			$comment_changes[$row['event_date']][] = $row;
		}
		$page->assign('comment_changes', $comment_changes);

		// Comment attachments
		$attachments = array();
		$sql = $db->query('SELECT *
                         FROM {attachments} a, {comments} c
                        WHERE c.task_id = ? AND a.comment_id = c.comment_id',
                       array($task_id));
		while ($row = $db->fetchRow($sql)) {
			$attachments[$row['comment_id']][] = $row;
		}
		$page->assign('comment_attachments', $attachments);

		// Comment links
		$links = array();
		$sql = $db->query('SELECT *
	                 FROM {links} l, {comments} c
			WHERE c.task_id = ? AND l.comment_id = c.comment_id',
	               array($task_id));
		while ($row = $db->fetchRow($sql)) {
			$links[$row['comment_id']][] = $row;
		}
		$page->assign('comment_links', $links);

		// Relations, notifications and reminders
		$sql = $db->query('SELECT t.*, r.*, s.status_name, res.resolution_name
                         FROM {related} r
                    LEFT JOIN {tasks} t ON (r.related_task = t.task_id AND r.this_task = ? OR r.this_task = t.task_id AND r.related_task = ?)
                    LEFT JOIN {list_status} s ON t.item_status = s.status_id
                    LEFT JOIN {list_resolution} res ON t.resolution_reason = res.resolution_id
                        WHERE t.task_id is NOT NULL AND is_duplicate = 0 AND ( t.mark_private = 0 OR ? = 1 )
                     ORDER BY t.task_id ASC',
                     array($task_id, $task_id, $user->perms('manage_project')));
		$related_cleaned = Flyspray::weedOutTasks($user, $db->fetchAllArray($sql));
		$page->assign('related', $related_cleaned);

		$sql = $db->query('SELECT t.*, r.*, s.status_name, res.resolution_name
                         FROM {related} r
                    LEFT JOIN {tasks} t ON r.this_task = t.task_id
                    LEFT JOIN {list_status} s ON t.item_status = s.status_id
                    LEFT JOIN {list_resolution} res ON t.resolution_reason = res.resolution_id
                        WHERE is_duplicate = 1 AND r.related_task = ?
                     ORDER BY t.task_id ASC',
                      array($task_id));
		$duplicates_cleaned = Flyspray::weedOutTasks($user, $db->fetchAllArray($sql));
    		$page->assign('duplicates', $duplicates_cleaned);

		$sql = $db->query('SELECT *
                         FROM {notifications} n
                    LEFT JOIN {users} u ON n.user_id = u.user_id
                        WHERE n.task_id = ?', array($task_id));
		$page->assign('notifications', $db->fetchAllArray($sql));

		$sql = $db->query('SELECT *
                         FROM {reminders} r
                    LEFT JOIN {users} u ON r.to_user_id = u.user_id
                        WHERE task_id = ?
                     ORDER BY reminder_id', array($task_id));
		$page->assign('reminders', $db->fetchAllArray($sql));

		$page->pushTpl('details.tabs.tpl');

		if ($user->perms('view_comments') || $proj->prefs['others_view'] || ($user->isAnon() && $task_details['task_token'] && Get::val('task_token') == $task_details['task_token'])) {
			$page->pushTpl('details.tabs.comment.tpl');
		}

		$page->pushTpl('details.tabs.related.tpl');

		if ($user->perms('manage_project')) {
			$page->pushTpl('details.tabs.notifs.tpl');
			$page->pushTpl('details.tabs.remind.tpl');
		}

		if ($proj->prefs['use_effort_tracking']) {
			$page->pushTpl('details.tabs.efforttracking.tpl');
		}
	
		$page->pushTpl('details.tabs.history.tpl');
    
	} # endif can_edit_task

} # endif can_view_task
?>
