<?php

/**
 * handle kanban board actions like drag&drop of tasks between task status kanban board columns
 *
 * @author Peter Liscovius (peterdd)
 */

define('IN_FS', true);

require_once '../../header.php';

load_translations(); // to load $fs severities and priorities and return messages

die('experimental');

if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')) {
	$user = new User(Cookie::val('flyspray_userid'));
	$user->check_account_ok();

	if( !Post::has('csrftoken') ){
		http_response_code(428); # 'Precondition Required'
		die('missingtoken');
	} elseif( Post::val('csrftoken')==$_SESSION['csrftoken']){
		# empty
	} else {
		http_response_code(412); # 'Precondition Failed'
		die('wrongtoken');
	}

	# global project choosen by header.php if task not exists
	if ($proj->id==0 or !(Post::num('task_id')>0)) {
		die('unknown task');
	}
	
	if (!$proj->prefs['use_kanban']) {
		http_response_code(403); # 'Forbidden'
		die(eL('projectnokanban'));
	}

	$task = Flyspray::getTaskDetails(Post::val('task_id'));

	if (!$user->can_edit_task($task)) {
		http_response_code(403); # 'Forbidden'
		die(eL('nopermission'));
	}

	if (
		(!$user->isAnon())
		&& $user->can_edit_task($task)
		&& Post::num('taskstatus')>0
		&& Post::num('taskstatus') != $task['item_status']
		&& $task['is_closed']==0
	) {
		$db->query('
			UPDATE {tasks}
			SET item_status = ?, last_edited_time=? , last_edited_by=?
			WHERE task_id=?',
			array(
				Post::num('taskstatus'),
				time(),
				$user->id,
				$task['task_id']
			)
		);
		
		# TODO add {history} entry

		echo $db->affectedRows();
	} else {
		die('notupdated');
	}

	# TODO handle/delegate notifications (ideally independent of the ajax request)
}
