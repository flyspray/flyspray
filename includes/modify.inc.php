<?php

/**
 * Database Modifications
 * @version  $Id$
 */

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

$notify = new Notifications;

$lt = Post::isAlnum('list_type') ? Post::val('list_type') : '';
$list_table_name = null;
$list_column_name = null;
$list_id = null;

if (strlen($lt)) {
    $list_table_name  = '{list_'.$lt .'}';
    $list_column_name = $lt . '_name';
    $list_id = $lt . '_id';
}

function Post_to0($key) { return Post::val($key, 0); }

function resizeImage($file, $max_x, $max_y, $forcePng = false)
{
	if ($max_x <= 0 || $max_y <= 0) {
		$max_x = 5;
		$max_y = 5;
	}

	$src = BASEDIR.'/avatars/'.$file;

	list($width, $height, $type) = getImageSize($src);

	$scale = min($max_x / $width, $max_y / $height);
	$newWidth = $width * $scale;
	$newHeight = $height * $scale;

	$img = imagecreatefromstring(file_get_contents($src));
	$black = imagecolorallocate($img, 0, 0, 0);
	$resizedImage = imageCreateTrueColor($newWidth, $newHeight);
	imagecolortransparent($resizedImage, $black);
	imageCopyResampled($resizedImage, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
	imageDestroy($img);
	unlink($src);

	if (!$forcePng) {
		switch ($type) {
			case IMAGETYPE_JPEG:
				imageJpeg($resizedImage, BASEDIR.'/avatars/'.$file);
				break;
			case IMAGETYPE_GIF:
				imageGif($resizedImage, BASEDIR.'/avatars/'.$file);
				break;
			case IMAGETYPE_PNG:
				imagePng($resizedImage, BASEDIR.'/avatars/'.$file);
				break;
			default:
				imagePng($resizedImage, BASEDIR.'/avatars/'.$file);
				break;
		}
	}
	else {
		imagePng($resizedImage, BASEDIR.'/avatars/'.$file.'.png');
	}

	return;
}

if (Req::num('task_id')) {
    $task = Flyspray::getTaskDetails(Req::num('task_id'));
}

if(isset($_SESSION)) {
    unset($_SESSION['SUCCESS'], $_SESSION['ERROR'], $_SESSION['ERRORS']);
}

switch ($action = Req::val('action'))
{
    // ##################
    // Adding a new task
    // ##################
    case 'newtask.newtask':

		$newtaskerrors=array();

		if (!Post::val('item_summary') || trim(Post::val('item_summary')) == '') { // description not required anymore
			$newtaskerrors['summaryrequired']=1;
		}

		if ($user->isAnon() && !filter_var(Post::val('anon_email'), FILTER_VALIDATE_EMAIL)) {
			$newtaskerrors['invalidemail']=1;
		}

		if (count($newtaskerrors)>0){
			$_SESSION['ERRORS']=$newtaskerrors;
			$_SESSION['ERROR']=L('invalidnewtask');
			break;
		}

        list($task_id, $token) = Backend::create_task($_POST);
        // Status and redirect
        if ($task_id) {
            $_SESSION['SUCCESS'] = L('newtaskadded');

            if ($user->isAnon()) {
                Flyspray::redirect(createURL('details', $task_id, null, array('task_token' => $token)));
            } else {
                Flyspray::redirect(createURL('details', $task_id));
            }
        } else {
            Flyspray::show_error(L('databasemodfailed'));
            break;
        }
        break;

        // ##################
        // Adding multiple new tasks
        // ##################
    case 'newmultitasks.newmultitasks':
        if(!isset($_POST['item_summary'])) {
            #Flyspray::show_error(L('summaryanddetails'));
            Flyspray::show_error(L('summaryrequired'));
            break;
        }
        $flag = true;
        foreach($_POST['item_summary'] as $summary) {
            if(!$summary || trim($summary) == "") {
                $flag = false;
                break;
            }
        }
        $i = 0;
        foreach($_POST['detailed_desc'] as $detail) {
            if($detail){
            	# only for ckeditor/html, not for dokuwiki (or other syntax plugins in future)
            	if ($conf['general']['syntax_plugin'] != 'dokuwiki') {
                  $_POST['detailed_desc'][$i] = "<p>" . $detail . "</p>";
            	}
            }
            $i++;
        }
        if(!$flag) {
            #Flyspray::show_error(L('summaryanddetails'));
            Flyspray::show_error(L('summaryrequired'));
            break;
        }

        $flag = true;
        $length = count($_POST['detailed_desc']);
        for($i = 0; $i < $length; $i++) {
            $ticket = array();
            foreach($_POST as $key => $value) {
                if($key == "assigned_to") {
                    $sql = $db->query("SELECT user_id FROM {users} WHERE user_name = ? or real_name = ?", array($value[$i], $value[$i]));
                    $ticket["rassigned_to"] = array(intval($db->fetchOne($sql)));
                    continue;
                }
                if(is_array($value))
                    $ticket[$key] = $value[$i];
                else
                    $ticket[$key] = $value;
            }
            list($task_id, $token) = Backend::create_task($ticket);
            if (!$task_id) {
                $flag = false;
                break;
            }
        }

        if(!$flag) {
            Flyspray::show_error(L('databasemodfailed'));
            break;
        }

        $_SESSION['SUCCESS'] = L('newtaskadded');
        Flyspray::redirect(createURL('index', $proj->id));
        break;

        // ##################
        // Modifying an existing task
        // ##################
    case 'details.update':
        if (!$user->can_edit_task($task)) {
		Flyspray::show_error(L('nopermission')); # TODO create a better error message
		break;
        }

	$errors=array();

	# TODO add checks who should be able to move a task, modify_all_tasks perm should not be enough and the target project perms are required too.
	# - User has project manager permission in source project AND in target project: Allowed to move task
	# - User has project manager permission in source project, but NOT in target project: Can send request to PUSH task to target project. A user with project manager permission of target project can accept the PUSH request.
	# - User has NO project manager permission in source project, but in target project: Can send request to PULL task to target project. A user with project manager permission of source project can accept the PULL request.
	# - User has calculated can_edit_task permission in source project AND (at least) newtask perm in target project: Can send a request to move task (similiar to 'close task please'-request) with the target project id, sure.

	$move=0;
	if($task['project_id'] != Post::val('project_id')) {
		$toproject=new Project(Post::val('project_id'));
		if($user->perms('modify_all_tasks', $toproject->id)){
			$move=1;
		} else{
			$errors['invalidtargetproject']=1;
		}
	}

	if($move==1){
		# Check that a task is not moved to a different project than its
		# possible parent or subtasks. Note that even closed tasks are
		# included in the result, a task can be always reopened later.
		$result = $db->query('
			SELECT parent.task_id, parent.project_id FROM {tasks} p
			JOIN {tasks} parent ON parent.task_id = p.supertask_id
			WHERE p.task_id = ?
			AND parent.project_id <> ?',
			array( $task['task_id'], Post::val('project_id') )
		);
		$parentcheck = $db->fetchRow($result);
		if ($parentcheck && $parentcheck['task_id']) {
			if ($parentcheck['project_id'] != Post::val('project_id')) {
				$errors['denymovehasparent']=L('denymovehasparent');
			}
		}

		$result = $db->query('
			SELECT sub.task_id, sub.project_id FROM {tasks} p
			JOIN {tasks} sub ON p.task_id = sub.supertask_id
			WHERE p.task_id = ?
			AND sub.project_id <> ?',
			array( $task['task_id'], Post::val('project_id') )
		);
		$subcheck = $db->fetchRow($result);

		# if there are any subtasks, check that the project is not changed
		if ($subcheck && $subcheck['task_id']) {
			$errors['denymovehassub']=L('denymovehassub');
		}
	}

	# summary form input fields, so user get notified what needs to be done right to be accepted
        if (!Post::val('item_summary')) {
		# description can be empty now
		#Flyspray::show_error(L('summaryanddetails'));
		#Flyspray::show_error(L('summaryrequired'));
		$errors['summaryrequired']=L('summaryrequired');
        }

	# ids of severity and priority are (probably!) intentional fixed in Flyspray.
	if( isset($_POST['task_severity']) && (!is_numeric(Post::val('task_severity')) || Post::val('task_severity')>5 || Post::val('task_severity')<0 ) ){
		$errors['invalidseverity']=1;
	}

	# peterdd:temp fix to allow priority 6 again
	# But I think about 1-5 valid (and 0 for unset) only in future to harmonize
	# with other trackers/taskplaner software and for severity-priority graphs like
	# https://en.wikipedia.org/wiki/Time_management#The_Eisenhower_Method
	if( isset($_POST['task_priority']) && (!is_numeric(Post::val('task_priority')) || Post::val('task_priority')>6 || Post::val('task_priority')<0 ) ){
		$errors['invalidpriority']=1;
	}

	if( isset($_POST['percent_complete']) && (!is_numeric(Post::val('percent_complete')) || Post::val('percent_complete')>100 || Post::val('percent_complete')<0 ) ){
		$errors['invalidprogress']=1;
	}

	# Description for the following list values here when moving a task to a different project:
	# - Do we use the old invalid values? (current behavior until 1.0-beta2, invalid id-values in database can be set, can result in php-'notices' or values arent shown on pages)
	# - Or set to default value of the new project? And inform the user to adjust the task properties in the new project?
	# - Or create a new tasktype for the new project, but:
	#    - Has the user the permission to create a new tasktype for the new project?
	#    - similiar named tasktypes exists?
	#
	# Maybe let's go with 2 steps when in this situation:
	# When user want move task to other project, a second page shows the form again but:
	# dropdown list forms show - maybe divided as optiongroups - :
	# -global list values ()
	# -current project list values
	# -target project list values
	# -option to create a new option based on current project value (if the user has the permission for the target project!)
	# -option to set to default value in target project or unset value
	# Also consider that not all list dropdown field may be shown to the user because of project settings (visible_fields)!


	# which $proj should we use here? $proj object is set in header.php by a request param before modify.inc.php is loaded, so it can differ from $task['project_id']!
	if($move==1){
		$statusarray=$toproject->listTaskStatuses();
	} else{
		$statusarray=$proj->listTaskStatuses();
	}

	# FIXME what if we move to different project, but the status_id is defined for the old project only (not global)?
	# FIXME what if we move to different project and item_status selection is deactivated/not shown in edit task page?
	if( isset($_POST['item_status']) && (!is_numeric(Post::val('item_status')) || false===Flyspray::array_find('status_id', Post::val('item_status'), $statusarray) ) ){
		$errors['invalidstatus']=1;
	}

	if($move==1){
		$typearray=$toproject->listTaskTypes();
	} else{
		$typearray=$proj->listTaskTypes();
	}

	# FIXME what if we move to different project, but tasktype_id is defined for the old project only (not global)?
	# FIXME what if we move to different project and task_type selection is deactiveated/not shown in edit task page?
	if( isset($_POST['task_type']) && (!is_numeric(Post::val('task_type')) || false===Flyspray::array_find('tasktype_id', Post::val('task_type'), $typearray) ) ){
		$errors['invalidtasktype']=1;
	}

        # FIXME what if we move to different project and reportedver selection is deactivated/not shown in edit task page?
        # FIXME what if we move to different project and reportedver is deactivated/not shown in edit task page?
        # FIXME what if we move to different project and closedby_version selection is deactivated/not shown in edit task page?
        # FIXME what if we move to different project and closedby_version is deactivated/not shown in edit task page?
	if($move==1){
		$versionarray=$toproject->listVersions();
	} else{
		$versionarray=$proj->listVersions();
	}
	if( isset($_POST['reportedver']) && (!is_numeric(Post::val('reportedver')) || ( $_POST['reportedver']!=='0' && false===Flyspray::array_find('version_id', Post::val('reportedver'), $versionarray)) ) ){
		$errors['invalidreportedversion']=1;
	}
	if( isset($_POST['closedby_version']) && (!is_numeric(Post::val('closedby_version')) || ( $_POST['closedby_version']!=='0' && false===Flyspray::array_find('version_id', Post::val('closedby_version'), $versionarray)) ) ){
		$errors['invaliddueversion']=1;
	}

	# FIXME what if we move to different project, but category_id is defined for the old project only (not global)?
        # FIXME what if we move to different project and category selection is deactivated/not shown in edit task page?
	if($move==1){
		$catarray=$toproject->listCategories();
	} else{
		$catarray=$proj->listCategories();
	}
	if( isset($_POST['product_category']) && (!is_numeric(Post::val('product_category')) || false===Flyspray::array_find('category_id', Post::val('product_category'), $catarray) ) ){
		$errors['invalidcategory']=1;
	}

	# FIXME what if we move to different project, but os_id is defined for the old project only (not global)?
	# FIXME what if we move to different project and operating_system selection is deactivated/not shown in edit task page?
	if($move==1){
		$osarray=$toproject->listOs();
	} else{
		$osarray=$proj->listOs();
	}
	if( isset($_POST['operating_system']) && (!is_numeric(Post::val('operating_system')) || ( $_POST['operating_system']!=='0' && false===Flyspray::array_find('os_id', Post::val('operating_system'), $osarray)) ) ){
		$errors['invalidos']=1;
	}

	if ($due_date = Post::val('due_date', 0)) {
		$due_date = Flyspray::strtotime(Post::val('due_date'));
	}

        $estimated_effort = 0;
        if (($estimated_effort = effort::editStringToSeconds(Post::val('estimated_effort'), $proj->prefs['hours_per_manday'], $proj->prefs['estimated_effort_format'])) === FALSE) {
		$errors['invalideffort']=1;
        }

        $time = time();

        $result = $db->query('SELECT * from {tasks} WHERE task_id = ?', array($task['task_id']));
        $defaults = $db->fetchRow($result);

	if (!Post::has('due_date')) {
		$due_date = $defaults['due_date'];
	}

	if (!Post::has('estimated_effort')) {
		$estimated_effort = $defaults['estimated_effort'];
	}


	if(count($errors)>0){
		# some invalid input by the user. Do not save the input and in the details-edit-template show the user where in the form the invalid values are.
		$_SESSION['ERRORS']=$errors; # $_SESSION['ERROR'] is very limited, holds only one string and often just overwritten
		$_SESSION['ERROR']=L('invalidinput');
		# pro and contra http 303 redirect here:
                # - good: browser back button works, browser history.
                # -  bad: form inputs of user not preserved (at the moment). Annoying if user wrote a long description and then the form submit gets denied because of other reasons.
                #Flyspray::redirect(createURL('edittask', $task['task_id']));
		break;
	}

	# FIXME/TODO: If a user has only 'edit own task edit' permission and task remains in the same project,
	# but there are not all fields visible/editable so the browser do not send that values with the form,
	# the sql update query should not touch that fields. And it should not overwrite the field with the default value in this case.
	# So this update query should be build dynamic (And for the future: when 'custom fields' are implemented ..)
	# Alternative: Read task field values before update query.
	# And we should check too what task fields the 'edit own task only'-user is allowed to change.
	# (E.g ignore form fields the user is not allowed to change. Currently hardcoded in template..)

/*
	# Dynamic creation of the UPDATE query required
	# First step: Settings which task fields can be changed by 'permission level': Based on situation found in FS 1.0-rc1 'status quo' in backend::create_task() and CleanFS/templates/template details.edit.tpl
	#$basicfields[]=array('item_summary','detailed_desc', 'task_type', 'product_category', 'operating_system', 'task_severity', 'percent_complete', 'product_version', 'estimated_effort'); # modify_own_tasks, anon_open
	$basicfields=$proj->prefs['basic_fields'];

	# peterdd: just saved a bit work in progress for future dynamic sql update string
	$sqlup='';
	foreach($basicfields as $bf){
		$sqlup.=' '.$bf.' = ?,';
		$sqlparam[]= Post::val($bf, $oldvals[$bf]);
	}
	$sqlup.=' last_edited_by = ?,';
	$sqlparam[]= $user->id;
	$sqlup.=' last_edited_time = ?,';
	$sqlparam[]= $time;

	$devfields[]=array('task_priority', 'due_date', 'item_status', 'closedby_version'); # modify_all_tasks
	$managerfields[]=array('project_id','mark_private'); # manage_project
	#$customfields[]=array(); # Flyspray 1.? future: perms depend of each custom field setting in a project..

	$sqlparam[]=$task['task_id'];
	$sqlupdate='UPDATE {tasks} SET '.$sqlup.' WHERE task_id = ?';

	echo '<pre>';print_r($sqlupdate);print_r($sqlparam);die();
	$db->query($sqlupdate, $sqlparam);
*/

	$detailed_desc = Post::val('detailed_desc', $defaults['detailed_desc']);

	# dokuwiki syntax plugin filters on output
	if ($conf['general']['syntax_plugin'] != 'dokuwiki') {
		$purifierconfig = HTMLPurifier_Config::createDefault();
		$purifierconfig->set('CSS.AllowedProperties', array());
		if ($fs->prefs['relnofollow']) {
			$purifierconfig->set('HTML.Nofollow', true);
		}
		$purifier = new HTMLPurifier($purifierconfig);
		$detailed_desc = $purifier->purify($detailed_desc);
	}

	$db->query('UPDATE {tasks}
		SET
		project_id = ?,
		task_type = ?,
		item_summary = ?,
		detailed_desc = ?,
		item_status = ?,
		mark_private = ?,
		product_category = ?,
		closedby_version = ?,
		operating_system = ?,
		task_severity = ?,
		task_priority = ?,
		last_edited_by = ?,
		last_edited_time = ?,
		due_date = ?,
		percent_complete = ?,
		product_version = ?,
		estimated_effort = ?
		WHERE task_id = ?',
		array(
			Post::val('project_id', $defaults['project_id']),
			Post::val('task_type', $defaults['task_type']),
			Post::val('item_summary', $defaults['item_summary']),
			$detailed_desc,
			Post::val('item_status', $defaults['item_status']),
			intval($user->can_change_private($task) && Post::val('mark_private', $defaults['mark_private'])),
			Post::val('product_category', $defaults['product_category']),
			Post::val('closedby_version', $defaults['closedby_version']),
			Post::val('operating_system', $defaults['operating_system']),
			Post::val('task_severity', $defaults['task_severity']),
			Post::val('task_priority', $defaults['task_priority']),
			intval($user->id), $time, intval($due_date),
			Post::val('percent_complete', $defaults['percent_complete']),
			Post::val('reportedver', $defaults['product_version']),
			intval($estimated_effort),
			$task['task_id']
		)
	);

		// Update the list of users assigned to this task
		$assignees = array();
		if (isset($_POST['rassigned_to']) && is_array($_POST['rassigned_to'])) {
			foreach ($_POST['rassigned_to'] as $ass) {
				if (is_numeric($ass)) {
					$assignees[] = $ass;
				}
			}
		}
		$assignees_changed = count(array_diff($task['assigned_to'], $assignees)) + count(array_diff($assignees, $task['assigned_to']));
		
		if ($user->perms('edit_assignments') && $assignees_changed) {
			// TODO: only update assignee changes without deletion
			// So date of assignment is kept if table {assigned} gets a timestamp field 'added' someday.
			// Delete the current assignees for this task
			$db->query('DELETE FROM {assigned} WHERE task_id = ?', array($task['task_id']));
			foreach ($assignees as $val) {
				$db->replace('{assigned}', array('user_id'=> $val, 'task_id'=> $task['task_id']), array('user_id', 'task_id'));
			}
		}

		# FIXME what if we move to different project, but tag(s) is/are defined for the old project only (not global)?
		# FIXME what if we move to different project and tag input field is deactivated/not shown in edit task page?
		#   - Create new tag(s) in target project if user has permission to create new tags but what with the users who have not the permission?
		# update tags
		$tagList = explode(';', Post::val('tags'));
		$tagList = array_map('strip_tags', $tagList);
		$tagList = array_map('trim', $tagList);
		$tagList = array_unique($tagList); # avoid duplicates for inputs like: "tag1;tag1" or "tag1; tag1<p></p>"
		$storedtags=array();
		foreach($task['tags'] as $temptag){
			$storedtags[]=$temptag['tag'];
		}
		$tags_changed = count(array_diff($storedtags, $tagList)) + count(array_diff($tagList, $storedtags));

		if ($tags_changed) {
			/*
			// Delete the current assigned tags for this task
			$db->query('DELETE FROM {task_tag} WHERE task_id = ?',  array($task['task_id']));
			foreach ($tagList as $tag){
				if ($tag == ''){
					continue;
				}
				# size of {list_tag}.tag_name, see flyspray-install.xml
				if(mb_strlen($tag) > 40){
					# report that softerror
					$errors['tagtoolong']=1;
					continue;
				}

				$res=$db->query("SELECT tag_id FROM {list_tag} WHERE (project_id=0 OR project_id=?) AND tag_name LIKE ? ORDER BY project_id", array($proj->id,$tag) );
				if($t=$db->fetchRow($res)){
					$tag_id=$t['tag_id'];
				} else {
					if( $proj->prefs['freetagging']==1){
						# add to taglist of the project
						$db->query("INSERT INTO {list_tag} (project_id,tag_name) VALUES (?,?)", array($proj->id,$tag));
						$tag_id=$db->insert_ID();
					} else{
						continue;
					}
				};
				$db->query("INSERT INTO {task_tag}(task_id,tag_id) VALUES(?,?)", array($task['task_id'], $tag_id) );
			}
			*/

			foreach ($tagList as $tag){
				if ($tag == ''){
					continue;
				}

				if (in_array($tag, $storedtags) ){
					echo "\n<br>in array storetags: $tag";
					# no db change required, just drop this tag from $storedtags
					$storedtags = array_diff($storedtags, array($tag));
				} else {
					$res=$db->query("SELECT tag_id
						FROM {list_tag}
						WHERE (project_id=0 OR project_id=?)
						AND tag_name LIKE ?
						ORDER BY project_id",
						array($proj->id, $tag)
					);

					if ($t=$db->fetchRow($res)) {
						$tag_id=$t['tag_id'];
					} else {
						if ($proj->prefs['freetagging']==1) {
							# size of {list_tag}.tag_name, see flyspray-install.xml
							if (mb_strlen($tag) > 40) {
								# report that softerror
								$errors['tagtoolong']=1;
								continue;
							}

							# add to taglist of the project, not global
							$db->query("INSERT INTO {list_tag} (project_id,tag_name) VALUES (?,?)", array($proj->id, $tag));
							$tag_id=$db->insert_ID();
						} else {
							continue;
						}
					}
					$db->query("INSERT INTO {task_tag} (task_id, tag_id, added, added_by)
						VALUES(?, ?, ?, ?)",
						array($task['task_id'], $tag_id, time(), $user->id)
					);
				}
			}

			# What is left in $storedtags should be removed
			# TODO: Log the remove of a tag from a task to {history} table?
			if (count($storedtags)>0) {
				# get ids of storedtags
				$removetags=array();
				foreach ($storedtags as $tagname) {
					$res=$db->query("SELECT tag_id
						FROM {list_tag}
						WHERE (project_id=0 OR project_id=?)
						AND tag_name LIKE ?
						ORDER BY project_id",
						array($proj->id, $tagname)
					);

					if ($t=$db->fetchRow($res)) {
						$tag_id=$t['tag_id'];
						$removetags[]=$tag_id;

						# maybe tag same name stored as global and project so remove both to be sure.
						if ($t=$db->fetchRow($res)) {
							$tag_id=$t['tag_id'];
							$removetags[]=$tag_id;
						}
					}
				}

				if (count($removetags)>0) {
					$db->query(
						'DELETE FROM {task_tag} WHERE task_id = ? AND tag_id IN('.implode(',', $removetags).')',
						array($task['task_id'])
					);
				}
			}
		}

        // Get the details of the task we just updated
        // To generate the changed-task message
        $new_details_full = Flyspray::getTaskDetails($task['task_id']);
        // Not very nice...maybe combine compare_tasks() and logEvent() ?
        $result = $db->query("SELECT * FROM {tasks} WHERE task_id = ?",
                             array($task['task_id']));
        $new_details = $db->fetchRow($result);

        foreach ($new_details as $key => $val) {
            if (strstr($key, 'last_edited_') || $key == 'assigned_to' || is_numeric($key)) {
                continue;
            }

            if ($val != $task[$key]) {
                // Log the changed fields in the task history
                Flyspray::logEvent($task['task_id'], 3, $val, $task[$key], $key, $time);
            }
        }

        $changes = Flyspray::compare_tasks($task, $new_details_full);
        if (count($changes) > 0) {
            $notify->create(NOTIFY_TASK_CHANGED, $task['task_id'], $changes, null, NOTIFY_BOTH, $proj->prefs['lang_code']);
        }

        if ($assignees_changed) {
            // Log to task history
            Flyspray::logEvent($task['task_id'], 14, implode(' ', $assignees), implode(' ', $task['assigned_to']), '', $time);

            // Notify the new assignees what happened.  This obviously won't happen if the task is now assigned to no-one.
            if (count($assignees)) {
                $new_assignees = array_diff($task['assigned_to'], $assignees);
                // Remove current user from notification list
                if (!$user->infos['notify_own']) {
                    $new_assignees = array_filter($new_assignees, function($u) use($user) { return $user->id != $u; } );
                }
                if(count($new_assignees)) {
                    $notify->create(NOTIFY_NEW_ASSIGNEE, $task['task_id'], null, $notify->specificAddresses($new_assignees), NOTIFY_BOTH, $proj->prefs['lang_code']);
                }
            }
        }

		Backend::add_comment($task, Post::val('comment_text'), $time);
		if (isset($_POST['delete_att']) && is_array($_POST['delete_att'])) {
			Backend::delete_files($_POST['delete_att']);
		}
		Backend::upload_files($task['task_id'], '0', 'usertaskfile');
		if (isset($_POST['delete_link']) && is_array($_POST['delete_link'])) {
			Backend::delete_links($_POST['delete_link']);
		}
		Backend::upload_links($task['task_id'], '0', 'userlink');

		$_SESSION['SUCCESS'] = L('taskupdated');
		# report minor/soft errors too that does not hindered saving task
		if(count($errors)>0){
			$_SESSION['ERRORS']=$errors;
		}
		Flyspray::redirect(createURL('details', $task['task_id']));
		break;

	/**
	 * closing a task
	 */
	case 'details.close':
		if (!$user->can_close_task($task)) {
			break;
		}

		if ($task['is_closed']) {
			break;
		}

		if (!Post::val('resolution_reason')) {
			Flyspray::show_error(L('noclosereason'));
			break;
		}

		// self duplicate check
		if (Post::val('resolution_reason') == RESOLUTION_DUPLICATE) {
			preg_match("/\b(?:FS#|bug )(\d+)\b/", Post::val('closure_comment', ''), $dupe_of);
			if (isset($dupe_of[1]) && $task['task_id'] == $dupe_of[1]) {
				Flyspray::show_error(L('circularduplicate'));
				break;
			}
		}

		Backend::close_task($task['task_id'], Post::val('resolution_reason'), Post::val('closure_comment', ''), Post::val('mark100', false));
		$_SESSION['SUCCESS'] = L('taskclosedmsg');
		# FIXME there are several pages using this form, details and pendingreq at least
		#Flyspray::redirect(createURL('details', $task['task_id']));
		break;

    case 'details.associatesubtask':
	if ( $task['task_id'] == Post::num('associate_subtask_id')) {
            Flyspray::show_error(L('selfsupertasknotallowed'));
            break;
        }
        $sql = $db->query('SELECT supertask_id, project_id FROM {tasks} WHERE task_id = ?',
            array(Post::num('associate_subtask_id')));

        $suptask = $db->fetchRow($sql);

        // check to see if the subtask exists.
        if (!$suptask) {
            Flyspray::show_error(L('subtasknotexist'));
            break;
        }

        // if the user has not the permission to view all tasks, check if the task
        // is in tasks allowed to see, otherwise tell that the task does not exist.
        if (!$user->perms('view_tasks')) {
            $taskcheck = Flyspray::getTaskDetails(Post::num('associate_subtask_id'));
            if (!$user->can_view_task($taskcheck)) {
                Flyspray::show_error(L('subtasknotexist'));
                break;
            }
        }

        // check to see if associated subtask is already the parent of this task
        if ($suptask['supertask_id'] == Post::num('associate_subtask_id')) {
            Flyspray::show_error(L('subtaskisparent'));
            break;
        }

        // check to see if associated subtask already has a parent task
        if ($suptask['supertask_id']) {
            Flyspray::show_error(L('subtaskalreadyhasparent'));
            break;
        }

        // check to see that both tasks belong to the same project
        if ($task['project_id'] != $suptask['project_id']) {
            Flyspray::show_error(L('musthavesameproject'));
            break;
        }

        //associate the subtask
        $db->query('UPDATE {tasks} SET supertask_id=? WHERE task_id=?',array( $task['task_id'], Post::num('associate_subtask_id')));
        Flyspray::logEvent($task['task_id'], 32, Post::num('associate_subtask_id'));
        Flyspray::logEvent(Post::num('associate_subtask_id'), 34, $task['task_id']);

        $_SESSION['SUCCESS'] = sprintf( L('associatedsubtask'), Post::num('associate_subtask_id') );
        break;


	case 'reopen':
		/**
		 * re-opening an task
		 */
		if (!$user->can_close_task($task)) {
			break;
		}

		// Get last %
		$old_percent = $db->query("SELECT old_value, new_value
			FROM {history}
			WHERE field_changed = 'percent_complete'
			AND task_id = ?
			AND old_value != '100'
			ORDER BY event_date DESC
			LIMIT 1",
			array($task['task_id'])
		);
		
		$old_percent = $db->fetchRow($old_percent);

		if (!isset($old_percent['old_value'])) {
			$old_percent['old_value']=0;
			$old_percent['new_value']=0;
		}

		$db->query("UPDATE {tasks}
			SET resolution_reason = 0, closure_comment = '', date_closed = 0,
			last_edited_time = ?, last_edited_by = ?, is_closed = 0, percent_complete = ?
			WHERE task_id = ?",
			array(time(), $user->id, intval($old_percent['old_value']), $task['task_id'])
		);

		Flyspray::logEvent($task['task_id'], 3, $old_percent['old_value'], $old_percent['new_value'], 'percent_complete');

		$notify->create(NOTIFY_TASK_REOPENED, $task['task_id'], null, null, NOTIFY_BOTH, $proj->prefs['lang_code']);

		// add comment of PM request to comment page if accepted
		$sql = $db->query('SELECT * FROM {admin_requests}
			WHERE task_id = ?
			AND request_type = ?
			AND resolved_by = 0',
			array($task['task_id'], 2)
		);
		$request = $db->fetchRow($sql);

		if ($request) {
			$db->query('
				INSERT INTO {comments} (task_id, date_added, last_edited_time, user_id, comment_text)
				VALUES ( ?, ?, ?, ?, ? )',
				array($task['task_id'], time(), time(), $request['submitted_by'], $request['reason_given'])
			);
			// delete existing PM request
			$db->query('UPDATE  {admin_requests}
				SET resolved_by = ?, time_resolved = ?
				WHERE  request_id = ?',
				array($user->id, time(), $request['request_id'])
			);
		}

		Flyspray::logEvent($task['task_id'], 13);

		$_SESSION['SUCCESS'] = L('taskreopenedmsg');
		# FIXME There are several pages using this form, details and pendingreq at least.
		#Flyspray::redirect(createURL('details', $task['task_id']));
		break;

	/**
	 * adding a comment
	 */
	case 'details.addcomment':
		if (!Backend::add_comment($task, Post::val('comment_text'))) {
			Flyspray::show_error(L('nocommententered'));
			break;
		}

		if (Post::val('notifyme') == '1') {
			// If the user wanted to watch this task for changes
			Backend::add_notification($user->id, $task['task_id']);
		}

		$_SESSION['SUCCESS'] = L('commentaddedmsg');
		Flyspray::redirect(createURL('details', $task['task_id']));
		break;

	/**
	 * effort tracking
	 */
	case 'details.efforttracking':

		require_once BASEDIR . '/includes/class.effort.php';
		$effort = new effort($task['task_id'], $user->id);

		if (Post::val('start_tracking')) {
			if ($effort->startTracking()) {
				$_SESSION['SUCCESS'] = L('efforttrackingstarted');
			} else {
				$_SESSION['ERROR'] = L('efforttrackingnotstarted');
			}
		}

		if (Post::val('stop_tracking')) {
			$effort->stopTracking();
			$_SESSION['SUCCESS'] = L('efforttrackingstopped');
		}

		if (Post::val('cancel_tracking')) {
			$effort->cancelTracking();
			$_SESSION['SUCCESS'] = L('efforttrackingcancelled');
		}

		if (Post::val('manual_effort')) {
			if ($effort->addEffort(Post::val('effort_to_add'), $proj, Post::val('effort_description'))) {
				$_SESSION['SUCCESS'] = L('efforttrackingadded');
			}
		}

		Flyspray::redirect(createURL('details', $task['task_id']).'#effort');
		break;

	/**
	 * sending a new user a confirmation code
	 */
	case 'register.sendcode':
		if (!$user->can_register()) {
			break;
		}

		$captchaerrors=array();
		if ($fs->prefs['captcha_securimage']) {
			$image = new Securimage();
			if (!Post::isAlnum('captcha_code') || !$image->check(Post::val('captcha_code'))) {
				$captchaerrors['invalidsecurimage']=1;
			}
		}

		if ($fs->prefs['captcha_recaptcha']) {
			require_once 'class.recaptcha.php';
			if( !recaptcha::verify()) {
				$captchaerrors['invalidrecaptcha']=1;
			}
		}

		if (count($captchaerrors)) {
			$_SESSION['ERRORS']=$captchaerrors;
			Flyspray::show_error(L('captchaerror'));
			break;
		}

		if (!Post::val('user_name') || !Post::val('real_name') || !Post::val('email_address')) {
			// If the form wasn't filled out correctly, show an error
			Flyspray::show_error(L('registererror'));
			break;
		}

		if ($fs->prefs['repeat_emailaddress'] && trim(Post::val('email_address')) != trim(Post::val('verify_email_address'))) {
			Flyspray::show_error(L('emailverificationwrong'));
			break;
		}

		$email = strtolower(trim(Post::val('email_address')));
		$jabber_id = strtolower(trim(Post::val('jabber_id')));

		// email is mandatory
		if (!$email || !Flyspray::check_email($email)) {
			Flyspray::show_error(L('novalidemail'));
			break;
		}

		// jabber_id is optional
		if ($jabber_id && !Jabber::check_jid($jabber_id)) {
			Flyspray::show_error(L('novalidjabber'));
			break;
		}

		$user_name = Backend::clean_username(Post::val('user_name'));

		// Limit length
		$real_name = substr(trim(Post::val('real_name')), 0, 100);
		// Remove doubled up spaces and control chars
		$real_name = preg_replace('![\x00-\x1f\s]+!u', ' ', $real_name);

		if (!$user_name || empty($user_name) || !$real_name) {
			Flyspray::show_error(L('entervalidusername'));
			break;
		}

		// Delete registration codes older than 24 hours
		$yesterday = time() - 86400;
		$db->query('DELETE FROM {registrations} WHERE reg_time < ?', array($yesterday));

		$sql = $db->query('SELECT COUNT(*) FROM {users} u, {registrations} r
			WHERE  u.user_name = ? OR r.user_name = ?',
			array($user_name, $user_name));
		if ($db->fetchOne($sql)) {
			Flyspray::show_error(L('usernametaken'));
			break;
		}

		$sql = $db->query("SELECT COUNT(*) FROM {users} WHERE
                           jabber_id = ? AND jabber_id != ''
                           OR email_address = ? AND email_address != ''",
			array($jabber_id, $email));
		if ($db->fetchOne($sql)) {
			Flyspray::show_error(L('emailtaken'));
			break;
		}

		// Generate a random bunch of numbers for the confirmation code and the confirmation url
		foreach (array('randval','magic_url') as $genrandom) {
			$$genrandom = md5(function_exists('openssl_random_pseudo_bytes') ?
				openssl_random_pseudo_bytes(32) :
				uniqid(mt_rand(), true));
		}

		$confirm_code = substr($randval, 0, 10);

		// send the email first
		$userconfirmation = array();
		$userconfirmation[$email] = array(
			'recipient' => $email, 
			'lang' => $fs->prefs['lang_code']
			);
		$recipients = array($userconfirmation);
		if($notify->create(
			NOTIFY_CONFIRMATION,
			null,
			array($baseurl, $magic_url, $user_name, $confirm_code),
			$recipients,
			NOTIFY_EMAIL)
		) {
			// email sent successfully, now update the database.
			$reg_values = array(
				time(),
				$confirm_code,
				$user_name,
				$real_name,
				$email,
				$jabber_id,
				Post::num('notify_type'),
				$magic_url,
				Post::num('time_zone')
			);
			// Insert everything into the database
			$query = $db->query("INSERT INTO  {registrations}
				(reg_time, confirm_code, user_name, real_name,
				email_address, jabber_id, notify_type,
				magic_url, time_zone)
				VALUES ( " . $db->fill_placeholders($reg_values) . ' )',
				$reg_values);

			if ($query) {
				$_SESSION['SUCCESS'] = L('codesent');
				Flyspray::redirect($baseurl);
			}
		} else {
			Flyspray::show_error(L('codenotsent'));
			break;
		}
		break;

	/**
	 * new user self-registration with a confirmation code
	 */
	case 'register.registeruser':
        if (!$user->can_register()) {
            break;
        }

        if (!Post::val('user_pass') || !Post::val('confirmation_code')) {
            Flyspray::show_error(L('formnotcomplete'));
            break;
        }

	if (strlen(Post::val('user_pass')) < MIN_PW_LENGTH) {
            Flyspray::show_error(L('passwordtoosmall'));
            break;
        }

        if ($fs->prefs['repeat_password'] && Post::val('user_pass') != Post::val('user_pass2')) {
            Flyspray::show_error(L('nomatchpass'));
            break;
        }

        // Check that the user entered the right confirmation code
        $sql = $db->query("SELECT * FROM {registrations} WHERE magic_url = ?",
                array(Post::val('magic_url')));
        $reg_details = $db->fetchRow($sql);

        if ($reg_details['confirm_code'] != trim(Post::val('confirmation_code'))) {
            Flyspray::show_error(L('confirmwrong'));
            break;
        }

        $profile_image = 'profile_image';
        $image_path = '';

        if (isset($_FILES[$profile_image])) {
            if (!empty($_FILES[$profile_image]['name'])) {
                $allowed = array('jpg', 'jpeg', 'gif', 'png');

                $image_name = $_FILES[$profile_image]['name'];
                $explode = explode('.', $image_name);
                $image_extn = strtolower(end($explode));
                $image_temp = $_FILES[$profile_image]['tmp_name'];

                if(in_array($image_extn, $allowed)) {
                    $avatar_name = substr(md5(time()), 0, 10).'.'.$image_extn;
                    $image_path = BASEDIR.'/avatars/'.$avatar_name;
                    move_uploaded_file($image_temp, $image_path);
                	resizeImage($avatar_name, $fs->prefs['max_avatar_size'], $fs->prefs['max_avatar_size']);
                } else {
                    Flyspray::show_error(L('incorrectfiletype'));
                    break;
                }
            }
        }

        $enabled = 1;
        if (!Backend::create_user($reg_details['user_name'],
                Post::val('user_pass'),
                $reg_details['real_name'],
                $reg_details['jabber_id'],
                $reg_details['email_address'],
                $reg_details['notify_type'], $reg_details['time_zone'], $fs->prefs['anon_group'], $enabled ,'', '', $image_path)) {
            Flyspray::show_error(L('usernametaken'));
            break;
        }

        $db->query('DELETE FROM {registrations} WHERE magic_url = ? AND confirm_code = ?',
                   array(Post::val('magic_url'), Post::val('confirmation_code')));


        $_SESSION['SUCCESS'] = L('accountcreated');
        // If everything is ok, add here a notify to both administrators and the user.
        // Otherwise, explain what wen wrong.

        define('NO_DO', true);
        break;

	/**
	 * new user self-registration without a confirmation code
	 */
	case 'register.newuser':
	case 'admin.newuser':
		if (!($user->perms('is_admin') || $user->can_self_register())) {
			break;
		}

		$captchaerrors=array();
		if (!($user->perms('is_admin')) && $fs->prefs['captcha_securimage']) {
			$image = new Securimage();
			if (!Post::isAlnum('captcha_code') || !$image->check(Post::val('captcha_code'))) {
				$captchaerrors['invalidsecurimage']=1;
			}
		}

		if (!($user->perms('is_admin')) && $fs->prefs['captcha_recaptcha']) {
			require_once 'class.recaptcha.php';
			if (!recaptcha::verify()) {
				$captchaerrors['invalidrecaptcha']=1;
			}
		}

		# if both captchatypes are configured, maybe show the user which one or both failed.
		if (count($captchaerrors)) {
			$_SESSION['ERRORS']=$captchaerrors;
			Flyspray::show_error(L('captchaerror'));
			break;
		}

		if (!Post::val('user_name') || !Post::val('real_name') || !Post::val('email_address')) {
			// If the form wasn't filled out correctly, show an error
			Flyspray::show_error(L('registererror'));
			break;
		}

		$email = strtolower(trim(Post::val('email_address')));
		
		if ($fs->prefs['repeat_emailaddress'] && $email != trim(Post::val('verify_email_address'))) {
			Flyspray::show_error(L('emailverificationwrong'));
			break;
		}

		// Check email format
		if (!$email || !Flyspray::check_email($email)) {
			Flyspray::show_error(L('novalidemail'));
			break;
		}

		if (strlen(Post::val('user_pass')) && (strlen(Post::val('user_pass')) < MIN_PW_LENGTH)) {
			Flyspray::show_error(L('passwordtoosmall'));
			break;
		}

		if ($fs->prefs['repeat_password'] && Post::val('user_pass') != Post::val('user_pass2')) {
			Flyspray::show_error(L('nomatchpass'));
			break;
		}

		if ($user->perms('is_admin')) {
			$group_in = Post::val('group_in');
		} else {
			$group_in = $fs->prefs['anon_group'];
		}

		if (!$user->perms('is_admin')) {
			$sql = $db->query("SELECT COUNT(*) FROM {users} WHERE
				jabber_id = ? AND jabber_id != ''
				OR email_address = ? AND email_address != ''",
			array(
				Post::val('jabber_id'),
				$email
			));

			if ($db->fetchOne($sql)) {
				Flyspray::show_error(L('emailtaken'));
				break;
			}
		}

		$enabled = 1;
		if($user->need_admin_approval()) {
			$enabled = 0;
		}
		$profile_image = 'profile_image';
		$image_path = '';

		if (isset($_FILES[$profile_image])) {
			if (!empty($_FILES[$profile_image]['name'])) {
				$allowed = array('jpg', 'jpeg', 'gif', 'png');

				$image_name = $_FILES[$profile_image]['name'];
				$explode = explode('.', $image_name);
				$image_extn = strtolower(end($explode));
				$image_temp = $_FILES[$profile_image]['tmp_name'];

				if (in_array($image_extn, $allowed)) {
					$avatar_name = substr(md5(time()), 0, 10).'.'.$image_extn;
					$image_path = BASEDIR.'/avatars/'.$avatar_name;
					move_uploaded_file($image_temp, $image_path);
					resizeImage($avatar_name, $fs->prefs['max_avatar_size'], $fs->prefs['max_avatar_size']);
				} else {
					Flyspray::show_error(L('incorrectfiletype'));
					break;
				}
			}
		}

		if (!Backend::create_user(Post::val('user_name'), Post::val('user_pass'),
			Post::val('real_name'), Post::val('jabber_id'),
			$email, Post::num('notify_type'),
			Post::num('time_zone'), $group_in, $enabled, '', '', $image_path)) {
			Flyspray::show_error(L('usernametaken'));
			break;
		}

		$_SESSION['SUCCESS'] = L('newusercreated');

		if (!$user->perms('is_admin')) {
			define('NO_DO', true);
		}
		break;

        // ##################
        // Admin based bulk registration of users
        // ##################
	case 'register.newuserbulk':
	case 'admin.newuserbulk':
		if (!($user->perms('is_admin'))) {
			break;
		}
		$group_in = Post::val('group_in');
		$error = '';
		$success = '';
		$noUsers = true;

		// For each user in post, add them
		for ($i = 0 ; $i < 10 ; $i++) {
			$user_name = Post::val('user_name' . $i);
			$real_name = Post::val('real_name' . $i);
			$email_address = Post::val('email_address' . $i);

			if ($user_name == '' || $real_name == '' || $email_address == '') {
				continue;
			} else {
				$noUsers = false;
			}
			$enabled = 1;

			// Avoid dups
			$sql = $db->query("SELECT COUNT(*) FROM {users} WHERE email_address = ?",
				array($email_address));

			if ($db->fetchOne($sql)) {
				$error .= "\n" . L('emailtakenbulk') . ": $email_address\n";
				continue;
			}

			if (!Backend::create_user(
				$user_name,
				Post::val('user_pass'),
				$real_name,
				'',
				$email_address,
				Post::num('notify_type'),
				Post::num('time_zone'),
				$group_in,
				$enabled,
				'',
				'',
				'')
			) {
				$error .= "\n" . L('usernametakenbulk') .": $user_name\n";
				continue;
			} else {
				$success .= ' '.$user_name.' ';
			}
		}

		if ($error != '') {
			Flyspray::show_error($error);
		} else if ($noUsers == true) {
			Flyspray::show_error(L('nouserstoadd'));
		} else {
			$_SESSION['SUCCESS'] = L('created').$success;
			if (!$user->perms('is_admin')) {
				define('NO_DO', true);
			}
		}
		break;

        // ##################
        // Bulk User Edit Form
        // ##################
	case 'admin.editallusers':

		if (!($user->perms('is_admin'))) {
			break;
		}
 
		if(isset($_POST['checkedUsers']) && is_array($_POST['checkedUsers'])) {
			$userids= $_POST['checkedUsers'];
		} else {
			break;
		}

		$users=array();

		foreach ($userids as $uid) {
			if( ctype_digit($uid) ) {
				if( $user->id == $uid ){
					Flyspray::show_error(L('nosuicide'));
				} else{
					$users[]=$uid;
				}
			} else{
				Flyspray::show_error(L('invalidinput'));
				break 2;
			}
		}

		if (count($users) == 0){
			Flyspray::show_error(L('nouserselected'));
			break;
		}

		// Make array of users to modify
		$ids = "(" . $users[0];
		for ($i = 1 ; $i < count($users) ; $i++) {
			$ids .= ", " . $users[$i];
		}
		$ids .= ")";

		// Grab the action
		if (isset($_POST['enable'])) {
			$sql = $db->query("UPDATE {users} SET account_enabled = 1 WHERE user_id IN $ids");
		} else if (isset($_POST['disable'])) {
			$sql = $db->query("UPDATE {users} SET account_enabled = 0 WHERE user_id IN $ids");
		} else if (isset($_POST['delete'])) {
			//$sql = $db->query("DELETE FROM {users} WHERE user_id IN $ids");
			foreach ($users as $uid) {
				Backend::delete_user($uid);
			}
		}

		/** Show success message and exit
		 * @todo show better success message: action - enabled, disabled deleted and how many users affected.
		 */
		$_SESSION['SUCCESS'] = L('usersupdated');
		break;

        // ##################
        //  adding a new group
        // ##################
    case 'pm.newgroup':
    case 'admin.newgroup':
        if (!$user->perms('manage_project')) {
            break;
        }

        if (!Post::val('group_name')) {
            Flyspray::show_error(L('groupanddesc'));
            break;
        } else {
            // Check to see if the group name is available
            $sql = $db->query("SELECT  COUNT(*)
                                 FROM  {groups}
                                WHERE  group_name = ? AND project_id = ?",
            array(Post::val('group_name'), $proj->id));

            if ($db->fetchOne($sql)) {
                Flyspray::show_error(L('groupnametaken'));
                break;
            } else {
                $cols = array('group_name', 'group_desc', 'manage_project', 'edit_own_comments',
                        'view_tasks', 'open_new_tasks', 'modify_own_tasks', 'add_votes',
                        'modify_all_tasks', 'view_comments', 'add_comments', 'edit_assignments',
                        'edit_comments', 'delete_comments', 'create_attachments',
                        'delete_attachments', 'view_history', 'close_own_tasks',
                        'close_other_tasks', 'assign_to_self', 'show_as_assignees',
                        'assign_others_to_self', 'add_to_assignees', 'view_reports', 'group_open',
                        'view_estimated_effort', 'track_effort', 'view_current_effort_done',
                        'add_multiple_tasks', 'view_roadmap', 'view_own_tasks', 'view_groups_tasks');

                $params = array_map('Post_to0',$cols);
                array_unshift($params, $proj->id);

                $db->query("INSERT INTO  {groups} (project_id, ". join(',', $cols).")
                                 VALUES  (". $db->fill_placeholders($cols, 1) . ')', $params);

                $_SESSION['SUCCESS'] = L('newgroupadded');
            }
        }

        break;

        // ##################
        //  Update the global application preferences
        // ##################
    case 'globaloptions':
        if (!$user->perms('is_admin')) {
            break;
        }

		$errors=array();

		$settings = array('jabber_server', 'jabber_port', 'jabber_username', 'notify_registration',
		'jabber_password', 'anon_group', 'user_notify', 'admin_email', 'email_ssl', 'email_tls',
		'lang_code', 'gravatars', 'hide_emails', 'spam_proof', 'default_project', 'default_entry',
		'dateformat','dateformat_extended',
		'jabber_ssl', 'anon_reg', 'global_theme', 'smtp_server', 'page_title',
		'smtp_user', 'smtp_pass', 'funky_urls', 'reminder_daemon','cache_feeds', 'intro_message',
		'disable_lostpw','disable_changepw','days_before_alert', 'emailNoHTML', 'need_approval', 'pages_welcome_msg',
		'active_oauths', 'only_oauth_reg', 'enable_avatars', 'max_avatar_size', 'default_order_by',
		'max_vote_per_day', 'votes_per_project', 'url_rewriting',
		'custom_style', 'general_integration', 'footer_integration',
		'repeat_password', 'repeat_emailaddress', 'massops', 'relnofollow');

		if(!isset($fs->prefs['massops'])){
			$db->query("INSERT INTO {prefs} (pref_name,pref_value) VALUES('massops',0)");
                }
		if(!isset($fs->prefs['relnofollow'])){
                        $db->query("INSERT INTO {prefs} (pref_name,pref_value) VALUES('relnofollow',1)");
                }

		# candid for a plugin, so separate them for the future.
		$settings[]='captcha_securimage';
		if(!isset($fs->prefs['captcha_securimage'])){
			$db->query("INSERT INTO {prefs} (pref_name,pref_value) VALUES('captcha_securimage',0)");
		}

		# candid for a plugin
		$settings[]='captcha_recaptcha';
		$settings[]='captcha_recaptcha_sitekey';
		$settings[]='captcha_recaptcha_secret';
		if(!isset($fs->prefs['captcha_recaptcha'])){
			$db->query("INSERT INTO {prefs} (pref_name,pref_value) VALUES('captcha_recaptcha',0),('captcha_recaptcha_sitekey',''),('captcha_recaptcha_secret','')");
		}

        if(Post::val('need_approval') == '1' && Post::val('spam_proof')){
            unset($_POST['spam_proof']); // if self register request admin to approve, disable spam_proof
        	// if you think different, modify functions in class.user.php directing different regiser tpl
        }
	if (Post::val('url_rewriting') == '1' && !$fs->prefs['url_rewriting']) {
		# Setenv can't be used to set the env variable in .htaccess, because apache module setenv is often disabled on hostings and brings server error 500.
		# First check if htaccess is turned on
		#if (!array_key_exists('HTTP_HTACCESS_ENABLED', $_SERVER)) {
		#	Flyspray::show_error(L('enablehtaccess'));
		#	break;
		#}

		# Make sure mod_rewrite is enabled by checking a env var defined as HTTP_MOD_REWRITE in the .htaccess .
		# It is possible to be converted to REDIRECT_HTTP_MOD_REWRITE . It's sound weired, but that's the case here.
		if ( !array_key_exists('HTTP_MOD_REWRITE', $_SERVER) && !array_key_exists('REDIRECT_HTTP_MOD_REWRITE' , $_SERVER) ) {
			#print_r($_SERVER);die();
			Flyspray::show_error(L('nomodrewrite'));
			break;
		}
	}

	if( substr(Post::val('custom_style'), -4) != '.css'){
		$_POST['custom_style']='';
	}

	# TODO validation
	if( Post::val('default_order_by2') !='' && Post::val('default_order_by2') !='n'){
		$_POST['default_order_by']=$_POST['default_order_by'].' '.$_POST['default_order_by_dir'].', '.$_POST['default_order_by2'].' '.$_POST['default_order_by_dir2'];
	} else{
		$_POST['default_order_by']=$_POST['default_order_by'].' '.$_POST['default_order_by_dir'];
	}


        foreach ($settings as $setting) {
            $db->query('UPDATE {prefs} SET pref_value = ? WHERE pref_name = ?',
                    array(Post::val($setting, 0), $setting));
            // Update prefs for following scripts
            $fs->prefs[$setting] = Post::val($setting, 0);
        }

        // Process the list of groups into a format we can store
        $viscols = trim(Post::val('visible_columns'));
        $db->query("UPDATE  {prefs} SET pref_value = ?
                     WHERE  pref_name = 'visible_columns'",
        array($viscols));
        $fs->prefs['visible_columns'] = $viscols;

        $visfields = trim(Post::val('visible_fields'));
        $db->query("UPDATE  {prefs} SET pref_value = ?
                     WHERE  pref_name = 'visible_fields'",
        array($visfields));
        $fs->prefs['visible_fields'] = $visfields;

		//save logo
		if($_FILES['logo']['error'] == 0){
			if( in_array(exif_imagetype($_FILES['logo']['tmp_name']), array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG)) ) {
				$logofilename=strtolower(basename($_FILES['logo']['name']));
				$logoexplode = explode('.', $logofilename);
				$logoextension = strtolower(end($logoexplode));
				$allowedextensions = array('gif', 'jpg', 'jpeg', 'png');

				if(in_array($logoextension, $allowedextensions)){
					move_uploaded_file($_FILES['logo']['tmp_name'], './' . $logofilename);
					$sql = $db->query("SELECT * FROM {prefs} WHERE pref_name='logo'");
					if(!$db->fetchOne($sql)){
						$db->query("INSERT INTO {prefs} (pref_name) VALUES('logo')");
					}
					$db->query("UPDATE {prefs} SET pref_value = ? WHERE pref_name='logo'", $logofilename);
				} else{
					$errors['invalidfileextension']=1;
				}
			}
		}
		//saved logo

        $_SESSION['SUCCESS'] = L('optionssaved');
		if(count($errors)>0){
			$_SESSION['ERRORS']=$errors;
		}

        break;

        // ##################
        // adding a new project
        // ##################
    case 'admin.newproject':
        if (!$user->perms('is_admin')) {
            break;
        }

        if (!Post::val('project_title')) {
            Flyspray::show_error(L('emptytitle'));
            break;
        }

        $viscols =    $fs->prefs['visible_columns']
                    ? $fs->prefs['visible_columns']
                    : 'id tasktype priority severity summary status dueversion progress';

        $visfields =  $fs->prefs['visible_fields']
                    ? $fs->prefs['visible_fields']
                    : 'id tasktype priority severity summary status dueversion progress';


		// 3 per row for better overview
		$db->query('
			INSERT INTO {projects} (
				project_title, theme_style, intro_message,
				others_view, others_viewroadmap, anon_open,
				project_is_active, visible_columns, visible_fields,
				lang_code, notify_email, notify_jabber,
				notify_reply, disp_intro, default_task 
			)
			VALUES (
				?, ?, ?,
				?, ?, ?,
				1, ?, ?,
				?, ?, ?, 
				?, ?, ?
			)',
			array(
				Post::val('project_title'), Post::val('theme_style'), Post::val('intro_message'),
				Post::num('others_view', 0), Post::num('others_viewroadmap', 0), Post::num('anon_open', 0),
				$viscols, $visfields,
				Post::val('lang_code', 'en'), '', '',
				'', Post::num('disp_intro'), ''
			)
        );

        // $sql = $db->query('SELECT project_id FROM {projects} ORDER BY project_id DESC', false, 1);
        // $pid = $db->fetchOne($sql);
        $pid = $db->insert_ID();

        $cols = array( 'manage_project', 'view_tasks', 'open_new_tasks',
                'modify_own_tasks', 'modify_all_tasks', 'view_comments',
                'add_comments', 'edit_comments', 'delete_comments', 'show_as_assignees',
                'create_attachments', 'delete_attachments', 'view_history', 'add_votes',
                'close_own_tasks', 'close_other_tasks', 'assign_to_self', 'edit_own_comments',
                'assign_others_to_self', 'add_to_assignees', 'view_reports', 'group_open',
                'view_estimated_effort', 'view_current_effort_done', 'track_effort',
                'add_multiple_tasks', 'view_roadmap', 'view_own_tasks', 'view_groups_tasks',
                'edit_assignments');
        $args = array_fill(0, count($cols), '1');
        array_unshift($args, 'Project Managers',
                'Permission to do anything related to this project.',
                intval($pid));

        $db->query("INSERT INTO  {groups}
                                 ( group_name, group_desc, project_id,
                                   ".join(',', $cols).")
                         VALUES  ( ". $db->fill_placeholders($cols, 3) .")", $args);

        $db->query("INSERT INTO  {list_category}
                                 ( project_id, category_name,
                                   show_in_list, category_owner, lft, rgt)
                         VALUES  ( ?, ?, 1, 0, 1, 4)", array($pid, 'root'));

        $db->query("INSERT INTO  {list_category}
                                 ( project_id, category_name,
                                   show_in_list, category_owner, lft, rgt )
                         VALUES  ( ?, ?, 1, 0, 2, 3)", array($pid, 'Backend / Core'));

        $db->query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, 1, 1)", array($pid, 'All'));

        $db->query("INSERT INTO  {list_version}
                                 ( project_id, version_name, list_position,
                                   show_in_list, version_tense )
                         VALUES  (?, ?, 1, 1, 2)", array($pid, '1.0'));

        $_SESSION['SUCCESS'] = L('projectcreated');
        Flyspray::redirect(createURL('pm', 'prefs', $pid));
        break;

	// ##################
	// updating project preferences
	// ##################
	case 'pm.updateproject':
		if (!$user->perms('manage_project')) {
			break;
		}

		if (Post::val('delete_project')) {
			if (Backend::delete_project($proj->id, Post::val('move_to'))) {
				$_SESSION['SUCCESS'] = L('projectdeleted');
			} else {
				$_SESSION['ERROR'] = L('projectnotdeleted');
			}

			if (Post::val('move_to')) {
				Flyspray::redirect(createURL('pm', 'prefs', Post::val('move_to')));
			} else {
				Flyspray::redirect($baseurl);
			}
		}

		if (!Post::val('project_title')) {
			Flyspray::show_error(L('emptytitle'));
			break;
		}

		$cols = array(
			'project_title',
			'theme_style',
			'lang_code',
			'default_task',
			'default_entry',
			'intro_message',
			'notify_email',
			'notify_jabber',
			'notify_subject',
			'notify_reply',
			'feed_description',
			'feed_img_url',
			'default_due_version',
			'use_effort_tracking',
			'pages_intro_msg',
			'estimated_effort_format',
			'current_effort_done_format'
		);
		$args = array_map('Post_to0', $cols);
		$cols = array_merge($cols, $ints = array(
			'project_is_active',
			'others_view',
			'others_viewroadmap',
			'anon_open',
			'comment_closed',
			'auto_assign',
			'freetagging',
			'use_tags',
			'use_gantt',
			'use_kanban'
			)
		);
		$args = array_merge($args, array_map(array('Post', 'num'), $ints));

		$cols[] = 'notify_types';
		$notify_types = array();
		if (isset($_POST['notify_types']) && is_array($_POST['notify_types'])) {
			foreach ($_POST['notify_types'] as $notify_type_id) {
				if (is_numeric($notify_type_id)) {
					$notify_types[] = $notify_type_id;
				}
			}
		}
		$args[] = implode(' ', $notify_types);
	
		$cols[] = 'last_updated';
		$args[] = time();
		$cols[] = 'disp_intro';
		$args[] = Post::num('disp_intro');
		$cols[] = 'default_cat_owner';
		$args[] = Flyspray::userNameToId(Post::val('default_cat_owner'));
		$cols[] = 'custom_style';
		$args[] = Post::val('custom_style');

		// Convert to seconds
		if (Post::val('hours_per_manday')) {
			$args[] = effort::editStringToSeconds(Post::val('hours_per_manday'), $proj->prefs['hours_per_manday'], $proj->prefs['estimated_effort_format']);
			$cols[] = 'hours_per_manday';
		}

		# TODO validation
		if (Post::val('default_order_by2') !='') {
			$_POST['default_order_by']=$_POST['default_order_by'].' '.$_POST['default_order_by_dir'].', '.$_POST['default_order_by2'].' '.$_POST['default_order_by_dir2'];
		} else {
			$_POST['default_order_by']=$_POST['default_order_by'].' '.$_POST['default_order_by_dir'];
		}
		$cols[] = 'default_order_by';
		$args[] = $_POST['default_order_by'];

		$args[] = $proj->id;

		$update = $db->query("UPDATE {projects}
			SET ".join('=?, ', $cols)."=?
			WHERE project_id = ?",
			$args);

		$update = $db->query('UPDATE {projects}
			SET visible_columns = ?
			WHERE project_id = ?',
			array(trim(Post::val('visible_columns')), $proj->id));

		$update = $db->query('UPDATE {projects}
			SET visible_fields = ?
			WHERE project_id = ?',
			array(trim(Post::val('visible_fields')), $proj->id));

		// Update project prefs for following scripts
		$proj = new Project($proj->id);
		$_SESSION['SUCCESS'] = L('projectupdated');
		Flyspray::redirect(createURL('pm', 'prefs', $proj->id));
		break;
        // ##################
        // modifying user details/profile
        // ##################
    case 'admin.edituser':
    case 'myprofile.edituser':
        if (Post::val('delete_user')) {
            // There probably is a bug here somewhere but I just can't find it just now.
            // Anyway, I get the message also when just editing my details.
            if ($user->id == (int)Post::val('user_id') && $user->perms('is_admin')) {
                Flyspray::show_error(L('nosuicide'));
                break;
            }
            else {
                // check that he is not the last user
                $sql = $db->query('SELECT count(*) FROM {users}');
                if ($db->fetchOne($sql) > 1) {
                    Backend::delete_user(Post::val('user_id'));
                    $_SESSION['SUCCESS'] = L('userdeleted');
                    Flyspray::redirect(createURL('admin', 'groups'));
                } else {
                    Flyspray::show_error(L('lastuser'));
                    break;
                }
            }
        }

        if (!Post::val('onlypmgroup')):
            if ($user->perms('is_admin') || $user->id == Post::val('user_id')): // only admin or user himself can change

                if (!Post::val('real_name') || (!Post::val('email_address') && !Post::val('jabber_id'))) {
                    Flyspray::show_error(L('realandnotify'));
                    break;
                }

                // Check email format
                if (!Post::val('email_address') || !Flyspray::check_email(Post::val('email_address')))
                {
                    Flyspray::show_error(L('novalidemail'));
                    break;
                }

                /**
                 * 'project': User prefers using 'project language setting' instead of 'user language setting'. Project language itself has fallback to 'global language setting'.
                 */
                if (!preg_match('/^(project|[a-z]{2,3}(_[a-z]{2,3})?)$/', Post::val('lang_code', 'en'))) {
                        Flyspray::show_error(L('invalidlanguagecode'));
                        break;
                }

                # current CleanFS template skips oldpass input requirement for admin accounts: if someone is able to catch an admin session he could simply create another admin acc for example.
                #if ( (!$user->perms('is_admin') || $user->id == Post::val('user_id')) && !Post::val('oldpass')
                if ( !$user->perms('is_admin') && !Post::val('oldpass') && (Post::val('changepass') || Post::val('confirmpass')) ) {
                    Flyspray::show_error(L('nooldpass'));
                    break;
                }

                if ($user->infos['oauth_uid'] && Post::val('changepass')) {
                    Flyspray::show_error(sprintf(L('oauthreqpass'), ucfirst($user->infos['oauth_provider'])));
                    break;
                }

                if (Post::val('changepass')) {
                    if ($fs->prefs['repeat_password'] && Post::val('changepass') != Post::val('confirmpass')) {
                        Flyspray::show_error(L('passnomatch'));
                        break;
                    }
					if (Post::val('oldpass')) {
						$sql = $db->query('SELECT user_pass FROM {users} WHERE user_id = ?', array(Post::val('user_id')));
						$oldpass =  $db->fetchRow($sql);

						$pwtest=false;
						if(strlen($oldpass['user_pass'])==32){
							$pwtest=hash_equals($oldpass['user_pass'], md5(Post::val('oldpass')));
						}elseif(strlen($oldpass['user_pass'])==40){
							$pwtest=hash_equals($oldpass['user_pass'], sha1(Post::val('oldpass')));
						}elseif(strlen($oldpass['user_pass'])==128){
							$pwtest=hash_equals($oldpass['user_pass'], hash('sha512',Post::val('oldpass')));
						}else{
							$pwtest=password_verify(Post::val('oldpass'), $oldpass['user_pass']);
						}

						if (!$pwtest){
							Flyspray::show_error(L('oldpasswrong'));
							break;
						}
					}
                    $new_hash = Flyspray::cryptPassword(Post::val('changepass'));
                    $db->query('UPDATE {users} SET user_pass = ? WHERE user_id = ?',
                            array($new_hash, Post::val('user_id')));

                    // If the user is changing their password, better update their cookie hash
                    if ($user->id == Post::val('user_id')) {
                        Flyspray::setCookie('flyspray_passhash',
                                crypt($new_hash, $conf['general']['cookiesalt']), time()+3600*24*30,null,null,null,true);
                    }
                }
                $jabId = Post::val('jabber_id');
                if (!empty($jabId) && Post::val('old_jabber_id') != $jabId) {
                    Notifications::JabberRequestAuth(Post::val('jabber_id'));
                }

                $db->query('UPDATE {users}
                       SET  real_name = ?, email_address = ?, notify_own = ?,
                            jabber_id = ?, notify_type = ?,
                            dateformat = ?, dateformat_extended = ?,
                            tasks_perpage = ?, time_zone = ?, lang_code = ?,
                            hide_my_email = ?, notify_online = ?
                     WHERE  user_id = ?',
                array(Post::val('real_name'), Post::val('email_address'), Post::num('notify_own', 0),
                    Post::val('jabber_id', ''), Post::num('notify_type'),
                    Post::val('dateformat', 0), Post::val('dateformat_extended', 0),
                    Post::num('tasks_perpage'), Post::num('time_zone'), Post::val('lang_code', 'en'),
                    Post::num('hide_my_email', 0), Post::num('notify_online', 0), Post::num('user_id')));

                # 20150307 peterdd: Now we must reload translations, because the user maybe changed his language preferences!
                # first reload user info
                $user=new User($user->id);
                load_translations();

                $profile_image = 'profile_image';

                if(isset($_FILES[$profile_image])) {
                    if(!empty($_FILES[$profile_image]['name'])) {
                        $allowed = array('jpg', 'jpeg', 'gif', 'png');

                        $image_name = $_FILES[$profile_image]['name'];
                        $explode = explode('.', $image_name);
                        $image_extn = strtolower(end($explode));
                        $image_temp = $_FILES[$profile_image]['tmp_name'];

                        if(in_array($image_extn, $allowed)) {
                            $sql = $db->query('SELECT profile_image FROM {users} WHERE user_id = ?', array(Post::val('user_id')));
                            $avatar_oldname = $db->fetchRow($sql);

                            if (is_file(BASEDIR.'/avatars/'.$avatar_oldname['profile_image']))
                                unlink(BASEDIR.'/avatars/'.$avatar_oldname['profile_image']);

                            $avatar_name = substr(md5(time()), 0, 10).'.'.$image_extn;
                            $image_path = BASEDIR.'/avatars/'.$avatar_name;
                            move_uploaded_file($image_temp, $image_path);
                        	resizeImage($avatar_name, $fs->prefs['max_avatar_size'], $fs->prefs['max_avatar_size']);
                            $db->query('UPDATE {users} SET profile_image = ? WHERE user_id = ?',
                            	array($avatar_name, Post::num('user_id')));
                        } else {
                            Flyspray::show_error(L('incorrectfiletype'));
                            break;
                        }
                    }
                }

                endif; // end only admin or user himself can change

            if ($user->perms('is_admin')) {
                if($user->id == (int)Post::val('user_id')) {
                    if (Post::val('account_enabled', 0) <= 0 || Post::val('old_global_id') != 1) {
                        Flyspray::show_error(L('nosuicide'));
                        break;
                    }
                } else {
                    $db->query('UPDATE {users} SET account_enabled = ?  WHERE user_id = ?',
                        array(Post::val('account_enabled', 0), Post::val('user_id')));
                    $db->query('UPDATE {users_in_groups} SET group_id = ?
                         WHERE group_id = ? AND user_id = ?',
                        array(Post::val('group_in'), Post::val('old_global_id'), Post::val('user_id')));
                }
            }

            endif; // end non project group changes

        if ($user->perms('manage_project') && !is_null(Post::val('project_group_in')) && Post::val('project_group_in') != Post::val('old_group_id')) {
            $db->query('DELETE FROM {users_in_groups} WHERE group_id = ? AND user_id = ?',
                         array(Post::val('old_group_id'), Post::val('user_id')));
            if (Post::val('project_group_in')) {
                $db->query('INSERT INTO {users_in_groups} (group_id, user_id) VALUES(?, ?)',
                           array(Post::val('project_group_in'), Post::val('user_id')));
            }
        }

        $_SESSION['SUCCESS'] = L('userupdated');
        if ($action === 'myprofile.edituser') {
                Flyspray::redirect(createURL('myprofile'));
        } elseif ($action === 'admin.edituser' && Post::val('area') === 'users') {
                Flyspray::redirect(createURL('edituser', Post::val('user_id')));
        } else {
                Flyspray::redirect(createURL('user', Post::val('user_id')));
        }
        break;
        // ##################
        // approving a new user registration
        // ##################
    case 'approve.user':
        if($user->perms('is_admin')) {
            $db->query('UPDATE {users} SET account_enabled = ?  WHERE user_id = ?',
                    array(1, Post::val('user_id')));

            $db->query('UPDATE  {admin_requests}
                       SET  resolved_by = ?, time_resolved = ?
                     WHERE  submitted_by = ? AND request_type = ?',
            array($user->id, time(), Post::val('user_id'), 3));
            // Missing event constant, can't log yet...
            // Missing notification constant, can't notify yet...
            // Notification constant added, write the code for sending that message...

        }
        break;
        // ##################
        // updating a group definition
        // ##################
    case 'pm.editgroup':
    case 'admin.editgroup':
        if (!$user->perms('manage_project')) {
            break;
        }

        if (!Post::val('group_name')) {
            Flyspray::show_error(L('groupanddesc'));
            break;
        }

        $cols = array('group_name', 'group_desc');

        // Add a user to a group
        if ($uid = Post::val('uid')) {
            $uids = preg_split('/[,;]+/', $uid, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($uids as $uid) {
                $uid = Flyspray::usernameToId($uid);
                if (!$uid) {
                    continue;
                }

                // If user is already a member of one of the project's groups, **move** (not add) him to the new group
                $sql = $db->query('SELECT g.group_id
                                     FROM {users_in_groups} uig, {groups} g
                                    WHERE g.group_id = uig.group_id AND uig.user_id = ? AND project_id = ?',
                array($uid, $proj->id));
                if ($db->countRows($sql)) {
                    $oldid = $db->fetchOne($sql);
                    $db->query('UPDATE {users_in_groups} SET group_id = ? WHERE user_id = ? AND group_id = ?',
                                array(Post::val('group_id'), $uid, $oldid));
                } else {
                    $db->query('INSERT INTO {users_in_groups} (group_id, user_id) VALUES(?, ?)',
                                array(Post::val('group_id'), $uid));
                }
            }
        }

        if (Post::val('delete_group') && Post::val('group_id') != '1') {
            $db->query('DELETE FROM {groups} WHERE group_id = ?', Post::val('group_id'));

            if (Post::val('move_to')) {
                $db->query('UPDATE {users_in_groups} SET group_id = ? WHERE group_id = ?',
                            array(Post::val('move_to'), Post::val('group_id')));
            }

            $_SESSION['SUCCESS'] = L('groupupdated');
            Flyspray::redirect(createURL( (($proj->id) ? 'pm' : 'admin'), 'groups', $proj->id));
        }
        // Allow all groups to update permissions except for global Admin
        if (Post::val('group_id') != '1') {
            $cols = array_merge($cols,
            array('manage_project', 'view_tasks', 'edit_own_comments',
              'open_new_tasks', 'modify_own_tasks', 'modify_all_tasks',
              'view_comments', 'add_comments', 'edit_comments', 'delete_comments',
              'create_attachments', 'delete_attachments', 'show_as_assignees',
              'view_history', 'close_own_tasks', 'close_other_tasks', 'edit_assignments',
              'assign_to_self', 'assign_others_to_self', 'add_to_assignees', 'view_reports',
              'add_votes', 'group_open', 'view_estimated_effort', 'track_effort',
              'view_current_effort_done', 'add_multiple_tasks', 'view_roadmap',
              'view_own_tasks', 'view_groups_tasks'));
        }

        $args = array_map('Post_to0', $cols);
        $args[] = Post::val('group_id');
        $args[] = $proj->id;

        $db->query("UPDATE  {groups}
                       SET  ".join('=?,', $cols)."=?
                     WHERE  group_id = ? AND project_id = ?", $args);

        $_SESSION['SUCCESS'] = L('groupupdated');
        break;

	/**
	 * updating a list
	 */
	case 'update_list':
		if (!$user->perms('manage_project') || !isset($list_table_name)) {
			break;
		}

		if (isset($_POST['list_name']) && is_array($_POST['list_name'])) {
			$listnames = array_filter($_POST['list_name'], function($val, $key) { return (is_int($key) && is_string($val));}, ARRAY_FILTER_USE_BOTH);
		} else {
			break;
		}

		if (isset($_POST['list_position']) && is_array($_POST['list_position'])) {
			$listposition = array_filter($_POST['list_position'], function($val, $key) { return (is_int($key) && is_numeric($val));}, ARRAY_FILTER_USE_BOTH);
		} else {
			$listposition = array();
		}

		if (isset($_POST['show_in_list']) && is_array($_POST['show_in_list'])) {
			$listshow = array_filter($_POST['show_in_list'], function($val, $key) { return (is_int($key) && is_numeric($val));}, ARRAY_FILTER_USE_BOTH);
		} else {
			$listshow = array();
		}

		if (isset($_POST['delete']) && is_array($_POST['delete'])) {
			$listdelete = array_filter($_POST['delete'], function($val, $key) { return (is_int($key) && is_numeric($val));}, ARRAY_FILTER_USE_BOTH);
		} else {
			$listdelete = array();
		}

		if ($lt === 'tag') {
			if (isset($_POST['list_class']) && is_array($_POST['list_class'])) {
				$listclass = array_filter($_POST['list_class'], function($val, $key) { return (is_int($key) && is_string($val));}, ARRAY_FILTER_USE_BOTH);
			} else {
				$listclass = array();
			}
		}

		if ($lt === 'version') {
			if (isset($_POST['version_tense']) && is_array($_POST['version_tense'])) {
				$listtense = array_filter($_POST['version_tense'], function($val, $key) { return (is_int($key) && is_string($val));}, ARRAY_FILTER_USE_BOTH);
			} else {
				$listtense = array();
			}
		}

		$updated = 0;
		$deleted = 0;
		foreach ($listnames as $id => $listname) {
			if ($listname != '') {
				# fallback position for entry if wasn't valid
				if (!isset($listposition[$id])) {
					$listposition[$id] = 1;
				}
				if (!isset($listshow[$id])) {
					$listshow[$id] = 0;
				}

				$check = $db->query("SELECT COUNT(*)
					FROM $list_table_name
					WHERE (project_id = 0 OR project_id = ?)
					AND $list_column_name = ?
					AND $list_id <> ?",
					array($proj->id, $listnames[$id], $id)
				);
				$itemexists = $db->fetchOne($check);

				if ($itemexists) {
					Flyspray::show_error(sprintf(L('itemexists'), $listnames[$id]));
					# TODO maybe show count of updated entries before this name collision occured ..
					return;
				}

				if ($lt === 'tag'){
					# skip updating an entry if no valid class string submitted
					if (isset($listclass[$id])) {
						$update = $db->query("UPDATE $list_table_name
						SET $list_column_name=?, list_position=?, show_in_list=?, class=?
						WHERE $list_id=? AND project_id=?",
						array($listnames[$id], intval($listposition[$id]), intval($listshow[$id]), $listclass[$id], $id, $proj->id)
						);
						$updated += $db->affectedRows();
					}
				} elseif ($lt === 'version') {
					# skip updating an entry if no valid tense submitted
					if (isset($listtense[$id])) {
						$update = $db->query("UPDATE $list_table_name
						SET $list_column_name=?, list_position=?, show_in_list=?, version_tense=?
						WHERE $list_id = ? AND project_id = ?",
						array($listnames[$id], intval($listposition[$id]), intval($listshow[$id]), intval($listtense[$id]), $id, $proj->id)
						);
						$updated += $db->affectedRows();
					}
				} else {
					$update = $db->query("UPDATE $list_table_name
						SET $list_column_name=?, list_position=?, show_in_list=?
						WHERE $list_id=? AND project_id=?",
						array($listnames[$id], intval($listposition[$id]), intval($listshow[$id]), $id, $proj->id)
					);
					$updated += $db->affectedRows();
				}
			} else {
				Flyspray::show_error(L('fieldsmissing'));
			}
		}

		if (is_array($listdelete) && count($listdelete)) {
			$deleteids = "$list_id = " . join(" OR $list_id =", array_map('intval', array_keys($listdelete)));
			$db->query("DELETE FROM $list_table_name WHERE project_id = ? AND ($deleteids)", array($proj->id));
			$deleted = $db->affectedRows();
		}

		# TODO tell user $updated and $deleted count
		$_SESSION['SUCCESS'] = L('listupdated');
		break;

        // ##################
        // adding a list item
        // ##################
    case 'pm.add_to_list':
    case 'admin.add_to_list':
        if (!$user->perms('manage_project') || !isset($list_table_name)) {
            break;
        }

        if (!Post::val('list_name')) {
            Flyspray::show_error(L('fillallfields'));
            break;
        }

        $position = Post::num('list_position');
        if (!$position) {
            $position = intval($db->fetchOne($db->query("SELECT max(list_position)+1
                                                    FROM $list_table_name
                                                   WHERE project_id = ?",
            array($proj->id))));
        }

        $check = $db->query("SELECT COUNT(*)
                               FROM $list_table_name
                              WHERE (project_id = 0 OR project_id = ?)
                                AND $list_column_name = ?",
                            array($proj->id, Post::val('list_name')));
        $itemexists = $db->fetchOne($check);

        if ($itemexists) {
            Flyspray::show_error(sprintf(L('itemexists'), Post::val('list_name')));
            return;
        }

        $db->query("INSERT INTO  $list_table_name
                                 (project_id, $list_column_name, list_position, show_in_list)
                         VALUES  (?, ?, ?, ?)",
        array($proj->id, Post::val('list_name'), $position, '1'));

        $_SESSION['SUCCESS'] = L('listitemadded');
        break;

        // ##################
        // adding a version list item
        // ##################
    case 'pm.add_to_version_list':
    case 'admin.add_to_version_list':
        if (!$user->perms('manage_project') || !isset($list_table_name)) {
            break;
        }

        if (!Post::val('list_name')) {
            Flyspray::show_error(L('fillallfields'));
            break;
        }

        $position = Post::num('list_position');
        if (!$position) {
            $position = $db->fetchOne($db->query("SELECT max(list_position)+1
                                                    FROM $list_table_name
                                                   WHERE project_id = ?",
            array($proj->id)));
        }

        $check = $db->query("SELECT COUNT(*)
                               FROM $list_table_name
                              WHERE (project_id = 0 OR project_id = ?)
                                AND $list_column_name = ?",
                            array($proj->id, Post::val('list_name')));
        $itemexists = $db->fetchOne($check);

        if ($itemexists) {
            Flyspray::show_error(sprintf(L('itemexists'), Post::val('list_name')));
            return;
        }

        $db->query("INSERT INTO  $list_table_name
                                (project_id, $list_column_name, list_position, show_in_list, version_tense)
                        VALUES  (?, ?, ?, ?, ?)",
        array($proj->id, Post::val('list_name'),
            intval($position), '1', Post::val('version_tense')));

        $_SESSION['SUCCESS'] = L('listitemadded');
        break;

	/**
	 * updating the category list
	 */
	case 'update_category':
		if (!$user->perms('manage_project')) {
			break;
		}

		# 'Nested Set' structure updates relies on correctly sent tree structure from client.
		# Netherless we should check before any sql update done.
		if (isset($_POST['list_name']) && is_array($_POST['list_name'])) {
			foreach ($_POST['list_name'] as $key => $val) {
				if (!is_int($key)) {
					break 2;
				};
				if (!is_string($val)) {
					break 2;
				};
				if (!isset($_POST['lft'][$key]) || !isset($_POST['rgt'][$key])) {
					break 2;
				}
				if (!is_numeric($_POST['lft'][$key]) || !is_numeric($_POST['rgt'][$key])) {
					break 2;
				}
			}
			$listnames = $_POST['list_name'];
			$listlft = $_POST['lft'];
			$listrgt = $_POST['rgt'];
		} else {
			break;
		}

		if (isset($_POST['show_in_list']) && is_array($_POST['show_in_list'])) {
			$listshow = array_filter($_POST['show_in_list'], function($val, $key) { return (is_int($key) && is_numeric($val));}, ARRAY_FILTER_USE_BOTH);
		} else {
			$listshow = array();
		}

		if (isset($_POST['delete']) && is_array($_POST['delete'])) {
			$listdelete = array_filter($_POST['delete'], function($val, $key) { return (is_int($key) && is_numeric($val));}, ARRAY_FILTER_USE_BOTH);
		} else {
			$listdelete = array();
		}

		if (isset($_POST['category_owner']) && is_array($_POST['category_owner'])) {
			$listowners = array_filter($_POST['category_owner'], function($val, $key) { return (is_int($key) && is_string($val));}, ARRAY_FILTER_USE_BOTH);
		} else {
			$listowners = array();
		}

		foreach ($listnames as $id => $listname) {
			if ($listname != '') {
				if (!isset($listshow[$id])) {
					$listshow[$id] = 0;
				}

				// Check for duplicates on the same sub-level under same parent category.
				// First, we'll have to find the right parent for the current category.
				$sql = $db->query('
					SELECT *
					FROM {list_category}
					WHERE project_id = ?
					AND lft < ?
					AND rgt > ?
					AND lft = (
						SELECT MAX(lft)
						FROM {list_category}
						WHERE lft < ?
						AND rgt > ?
					)',
					array(
						$proj->id,
						intval($listlft[$id]),
						intval($listrgt[$id]),
						intval($listlft[$id]),
						intval($listrgt[$id])
					)
				);
				$parent = $db->fetchRow($sql);

				$check = $db->query('
					SELECT COUNT(*)
					FROM {list_category} c
					WHERE project_id = ?
					AND category_name = ?
					AND lft > ?
					AND rgt < ?
					AND category_id <> ?
					AND NOT EXISTS (
						SELECT *
						FROM {list_category}
						WHERE project_id = ?
						AND lft > ?
						AND rgt < ?
						AND lft < c.lft
						AND rgt > c.rgt
					)',
					array(
						$proj->id,
						$listname,
						$parent['lft'],
						$parent['rgt'],
						intval($id),
						$proj->id,
						$parent['lft'],
						$parent['rgt']
					)
				);
				$itemexists = $db->fetchOne($check);

				#echo "<pre>" . $parent['category_name'] . "," . $listname . ", " . intval($id) . ", " . intval($listlft[$id]) . ", " . intval($listrgt[$id]) . ", " . $itemexists ."</pre>";

				if ($itemexists) {
					Flyspray::show_error(sprintf(L('categoryitemexists'), $listname, $parent['category_name']));
					return;
				}

				$update = $db->query('
					UPDATE {list_category}
					SET
						category_name = ?,
						show_in_list = ?,
						category_owner = ?,
						lft = ?,
						rgt = ?
					WHERE category_id = ?
					AND project_id = ?',
					array(
						$listname,
						intval($listshow[$id]),
						Flyspray::userNameToId($listowners[$id]),
						intval($listlft[$id]),
						intval($listrgt[$id]),
						intval($id),
						$proj->id
					)
				);

				// Correct visibility for sub categories
				if ($listshow[$id] == 0) {
					foreach ($listnames as $key => $value) {
						if ($listlft[$key] > $listlft[$id] && $listrgt[$key] < $listrgt[$id]) {
							$listshow[$key] = 0;
						}
					}
				}
			} else {
				Flyspray::show_error(L('fieldsmissing'));
			}
		}

		if (is_array($listdelete) && count($listdelete)) {
			$deleteids = "$list_id = " . join(" OR $list_id =", array_map('intval', array_keys($listdelete)));
			$db->query("DELETE FROM {list_category} WHERE project_id = ? AND ($deleteids)", array($proj->id));
		}

		$_SESSION['SUCCESS'] = L('listupdated');
		break;

        // ##################
        // adding a category list item
        // ##################
    case 'pm.add_category':
    case 'admin.add_category':
        if (!$user->perms('manage_project')) {
            break;
        }

        if (!Post::val('list_name')) {
            Flyspray::show_error(L('fillallfields'));
            break;
        }

        // Get right value of last node
        // Need also left value of parent for duplicate check and category name for errormessage.
        $sql = $db->query('SELECT rgt, lft, category_name FROM {list_category} WHERE category_id = ?', array(Post::val('parent_id', -1)));
        $parent = $db->fetchRow($sql);
        $right = $parent['rgt'];
        $left = $parent['lft'];

        // echo "<pre>Parent: " . Post::val('parent_id', -1) . ", left: $left, right: $right</pre>";

        // If parent has subcategories, check for possible duplicates
        // on the same sub-level and under the same parent.
        if ($left + 1 != $right) {
            $check = $db->query('SELECT COUNT(*)
                                  FROM {list_category} c
                                 WHERE project_id = ? AND category_name = ? AND lft > ? AND rgt < ?
                        AND NOT EXISTS (SELECT *
                                          FROM {list_category}
                                         WHERE project_id = ?
                                           AND lft > ? AND rgt < ?
                                           AND lft < c.lft AND rgt > c.rgt)',
                                array($proj->id, Post::val('list_name'), $left, $right, $proj->id, $left, $right));
            $itemexists = $db->fetchOne($check);

            if ($itemexists) {
                Flyspray::show_error(sprintf(L('categoryitemexists'), Post::val('list_name'), $parent['category_name']));
                return;
            }
        }

        $db->query('UPDATE {list_category} SET rgt=rgt+2 WHERE rgt >= ? AND project_id = ?', array($right, $proj->id));
        $db->query('UPDATE {list_category} SET lft=lft+2 WHERE lft >= ? AND project_id = ?', array($right, $proj->id));

        $db->query("INSERT INTO  {list_category}
                                 ( project_id, category_name, show_in_list, category_owner, lft, rgt )
                         VALUES  (?, ?, 1, ?, ?, ?)",
        array($proj->id, Post::val('list_name'),
              Post::val('category_owner', 0) == '' ? '0' : Flyspray::usernameToId(Post::val('category_owner', 0)), $right, $right+1));

        $_SESSION['SUCCESS'] = L('listitemadded');
        break;

        // ##################
        // adding a related task entry
        // ##################
    case 'details.add_related':
        if (!$user->can_edit_task($task)) {
            Flyspray::show_error(L('nopermission'));//TODO: create a better error message
            break;
        }

        // if the user has not the permission to view all tasks, check if the task
        // is in tasks allowed to see, otherwise tell that the task does not exist.
        if (!$user->perms('view_tasks')) {
            $taskcheck = Flyspray::getTaskDetails(Post::val('related_task'));
            if (!$user->can_view_task($taskcheck)) {
                Flyspray::show_error(L('relatedinvalid'));
                break;
            }
        }

        $sql = $db->query('SELECT  project_id
                             FROM  {tasks}
                            WHERE  task_id = ?',
        array(Post::val('related_task')));
        if (!$db->countRows($sql)) {
            Flyspray::show_error(L('relatedinvalid'));
            break;
        }

        $sql = $db->query("SELECT related_id
                             FROM {related}
                            WHERE this_task = ? AND related_task = ?
                                  OR
                                  related_task = ? AND this_task = ?",
        array($task['task_id'], Post::val('related_task'),
              $task['task_id'], Post::val('related_task')));

        if ($db->countRows($sql)) {
            Flyspray::show_error(L('relatederror'));
            break;
        }

        $db->query("INSERT INTO {related} (this_task, related_task) VALUES(?,?)",
                array($task['task_id'], Post::val('related_task')));

        Flyspray::logEvent($task['task_id'], 11, Post::val('related_task'));
        Flyspray::logEvent(Post::val('related_task'), 15, $task['task_id']);
        $notify->create(NOTIFY_REL_ADDED, $task['task_id'], Post::val('related_task'), null, NOTIFY_BOTH, $proj->prefs['lang_code']);

        $_SESSION['SUCCESS'] = L('relatedaddedmsg');
        break;

        // ##################
        // Removing a related task entry
        // ##################
    case 'remove_related':
        if (!$user->can_edit_task($task)) {
            Flyspray::show_error(L('nopermission'));//TODO: create a better error message
            break;
        }
        if (!is_array(Post::val('related_id'))) {
            Flyspray::show_error(L('formnotcomplete'));
            break;
        }

        foreach (Post::val('related_id') as $related) {
            $sql = $db->query('SELECT this_task, related_task FROM {related} WHERE related_id = ?',
                              array($related));
            $db->query('DELETE FROM {related} WHERE related_id = ? AND (this_task = ? OR related_task = ?)',
                        array($related, $task['task_id'], $task['task_id']));
            if ($db->affectedRows()) {
                $related_task = $db->fetchRow($sql);
                $related_task = ($related_task['this_task'] == $task['task_id']) ? $related_task['related_task'] : $task['task_id'];
                Flyspray::logEvent($task['task_id'], 12, $related_task);
                Flyspray::logEvent($related_task, 16, $task['task_id']);
                $_SESSION['SUCCESS'] = L('relatedremoved');
            }
        }

        break;

        // ##################
        // adding a user to the notification list
        // ##################
    case 'details.add_notification':
        if (Req::val('user_id')) {
            $userId = Req::val('user_id');
        } else {
            $userId = Flyspray::usernameToId(Req::val('user_name'));
        }
        if (!Backend::add_notification($userId, Req::val('ids'))) {
            Flyspray::show_error(L('couldnotaddusernotif'));
            break;
        }

        // TODO: Log event in a later version.

        $_SESSION['SUCCESS'] = L('notifyadded');
        Flyspray::redirect(createURL('details', $task['task_id']).'#notify');
        break;

        // ##################
        // removing a notification entry
        // ##################
    case 'remove_notification':
        Backend::remove_notification(Req::val('user_id'), Req::val('ids'));

        // TODO: Log event in a later version.

        $_SESSION['SUCCESS'] = L('notifyremoved');
        # if on details page we should redirect to details with a GET
        # but what if the request comes from another page (like myprofile for instance maybe in future)
        Flyspray::redirect(createURL('details', $task['task_id']).'#notify');
        break;

        // ##################
        // editing a comment
        // ##################
	case 'editcomment':
		if (!($user->perms('edit_comments') || $user->perms('edit_own_comments'))) {
			break;
		}

		$where = '';

		$comment_text=Post::val('comment_text');
		$previous_text=Post::val('previous_text');

		# dokuwiki syntax plugin filters on output
		if ($conf['general']['syntax_plugin'] != 'dokuwiki') {
			$purifierconfig = HTMLPurifier_Config::createDefault();
			$purifierconfig->set('CSS.AllowedProperties', array());
			if ($fs->prefs['relnofollow']) {
				$purifierconfig->set('HTML.Nofollow', true);
			}
			$purifier = new HTMLPurifier($purifierconfig);
			$comment_text = $purifier->purify($comment_text);
			$previous_text= $purifier->purify($comment_text);
		}

		$params = array($comment_text, time(), Post::val('comment_id'), $task['task_id']);

		if ($user->perms('edit_own_comments') && !$user->perms('edit_comments')) {
			$where = ' AND user_id = ?';
			array_push($params, $user->id);
		}

		$db->query("UPDATE {comments}
			SET comment_text = ?, last_edited_time = ?
			WHERE comment_id = ?
			AND task_id = ?
			$where", $params);
		$db->query("DELETE FROM {cache} WHERE  topic = ? AND type = ?", array(Post::val('comment_id'), 'comm'));

		Flyspray::logEvent($task['task_id'], 5, $comment_text, $previous_text, Post::val('comment_id'));

		Backend::upload_files($task['task_id'], Post::val('comment_id'));
		if (isset($_POST['delete_att']) && is_array($_POST['delete_att'])) {
			Backend::delete_files($_POST['delete_att']);
		}
		Backend::upload_links($task['task_id'], Post::val('comment_id'));
		if (isset($_POST['delete_link']) && is_array($_POST['delete_link'])) {
			Backend::delete_links($_POST['delete_link']);
		}

		$_SESSION['SUCCESS'] = L('editcommentsaved');
		break;

        // ##################
        // deleting a comment
        // ##################
    case 'details.deletecomment':
        if (!$user->perms('delete_comments')) {
            break;
        }

        $result = $db->query('SELECT task_id, comment_text, user_id, date_added
                                FROM {comments}
                               WHERE comment_id = ?',
        array(Post::val('comment_id')));
        $comment = $db->fetchRow($result);

        // Check for files attached to this comment
        $check_attachments = $db->query('SELECT  *
                                           FROM  {attachments}
                                          WHERE  comment_id = ?',
        array(Post::val('comment_id')));

        if ($db->countRows($check_attachments) && !$user->perms('delete_attachments')) {
            Flyspray::show_error(L('commentattachperms'));
            break;
        }

        $db->query("DELETE FROM {comments} WHERE comment_id = ? AND task_id = ?",
                   array(Post::val('comment_id'), $task['task_id']));

        if ($db->affectedRows()) {
		# uses history.new_value for storing deleted comment creator user_id
		# uses history.field_changed for storing deleted comment date_added
		Flyspray::logEvent($task['task_id'], 6, $comment['user_id'], $comment['comment_text'], $comment['date_added']);
        }

        while ($attachment = $db->fetchRow($check_attachments)) {
            $db->query("DELETE from {attachments} WHERE attachment_id = ?",
                    array($attachment['attachment_id']));

            @unlink(BASEDIR .'/attachments/' . $attachment['file_name']);

            Flyspray::logEvent($attachment['task_id'], 8, $attachment['orig_name']);
        }

        $_SESSION['SUCCESS'] = L('commentdeletedmsg');
        break;

        // ##################
        // adding a reminder
        // ##################
    case 'details.addreminder':

	$errors = array();
	// TODO Naming of the vars of this form is terrible, fix in later (1.1?) version.

	// repeats
	if (!is_string($_POST['timeamount1']) or intval($_POST['timeamount1'])<1) {
		$errors['addreminder_minimalrepeaterror'] = 1;
	}

	// at least 1 hour (3600sec) minimal interval submitted
	if (!is_string($_POST['timetype1']) or intval($_POST['timetype1'])<3600) {
		$errors['addreminder_minimalintervalerror'] = 1;
	}

	// startdate
	if (!is_string($_POST['timeamount2'])) {
		$errors['addreminder_starterror'] = 1;
	}

	if (!is_string($_POST['to_user_id'])) {
		$errors['addreminder_datetimeerror'] = 1;
	}

	if (count($errors)>0) {
		$_SESSION['ERRORS'] = $errors; # $_SESSION['ERROR'] is very limited, holds only one string and often just overwritten
		$_SESSION['ERROR'] = L('invalidinput');
		# pro and contra http 303 redirect here:
		# - good: browser back button works, browser history.
		# -  bad: form inputs of user not preserved (at the moment). Annoying if user wrote a long description and then the form submit gets denied because of other reasons.
		#Flyspray::redirect(createURL('details', $task['task_id']));
		break;
	}

	$to_user_id = Flyspray::usernameToId(Post::val('to_user_id'));
	$start_time = Flyspray::strtotime(Post::val('timeamount2', 0));
	$how_often = intval(Post::val('timeamount1', 1)) * Post::val('timetype1');

	if (!Backend::add_reminder($task['task_id'], Post::val('reminder_message'), $how_often, $start_time, $to_user_id)) {
		Flyspray::show_error(L('usernotexist'));
		break;
	}

	// log event is written by Backend::add_reminder()
	$_SESSION['SUCCESS'] = L('reminderaddedmsg');
	// Do we need to jump to the reminder tab/anchor #remind on task detail page? error and success messages are shown currently at the top. (may change)
	// redirect on success after POST so browser backbutton works.
	Flyspray::redirect(createURL('details', $task['task_id']));
	break;
	
        // ##################
        // removing a reminder
        // ##################
    case 'deletereminder':
        if (!$user->perms('manage_project') || !is_array($_POST['reminder_id'])) {
            break;
        }

		$errors = 0;
		foreach ($_POST['reminder_id'] as $reminder_id) {
			if (!is_string($reminder_id) or !is_numeric($reminder_id) or $reminder_id<1) {
				$errors++;
			}
		}

		if ($errors > 0) {
			$_SESSION['ERROR'] = L('invalidinput');
			break;
		}

		foreach ($_POST['reminder_id'] as $reminder_id) {
			$sql = $db->query('SELECT to_user_id FROM {reminders} WHERE reminder_id = ?',
				array($reminder_id));
			$reminder = $db->fetchOne($sql);
			$db->query('DELETE FROM {reminders} WHERE reminder_id = ? AND task_id = ?',
				array($reminder_id, $task['task_id']));
			if ($db && $db->affectedRows()) {
				Flyspray::logEvent($task['task_id'], 18, $reminder);
			}
		}

		$_SESSION['SUCCESS'] = L('reminderdeletedmsg');
		break;

        // ##################
        // change a bunch of users' groups
        // ##################
    case 'movetogroup':
        // Check that both groups belong to the same project
        $sql = $db->query('SELECT project_id FROM {groups} WHERE group_id = ? OR group_id = ?',
                          array(Post::val('switch_to_group'), Post::val('old_group')));
        $old_pr = $db->fetchOne($sql);
        $new_pr = $db->fetchOne($sql);
        if ($proj->id != $old_pr || ($new_pr && $new_pr != $proj->id)) {
            break;
        }

        if (!$user->perms('manage_project', $old_pr) || !is_array(Post::val('users'))) {
            break;
        }

	foreach (Post::val('users') as $user_id => $val) {
                if($user->id!=$user_id || $proj->id!=0){
			if (Post::val('switch_to_group') == '0') {
				$db->query('DELETE FROM {users_in_groups} WHERE user_id=? AND group_id=?',
					array($user_id, Post::val('old_group'))
				);
			} else {
				# special case: user exists in multiple global groups (shouldn't, but happened)
				# avoids duplicate entry error
				if($old_pr==0){
					$sql = $db->query('SELECT group_id FROM {users_in_groups} WHERE user_id = ? AND group_id = ?',
						array($user_id, Post::val('switch_to_group'))
					);
					$uigexists = $db->fetchOne($sql);
					if($uigexists > 0){
						$db->query('DELETE FROM {users_in_groups} WHERE user_id=? AND group_id=?',
							array($user_id, Post::val('old_group'))
						);
					}
				}

				$db->query('UPDATE {users_in_groups} SET group_id=? WHERE user_id=? AND group_id=?',
					array(Post::val('switch_to_group'), $user_id, Post::val('old_group'))
				);
			}
		} else {
			Flyspray::show_error(L('nosuicide'));
		}
	}

        // TODO: Log event in a later version.

        $_SESSION['SUCCESS'] = L('groupswitchupdated');
        break;

        // ##################
        // taking ownership
        // ##################
    case 'takeownership':
        Backend::assign_to_me($user->id, Req::val('ids'));

        // TODO: Log event in a later version.

        $_SESSION['SUCCESS'] = L('takenownershipmsg');
        break;

        // ##################
        // add to assignees list
        // ##################
    case 'addtoassignees':
        Backend::add_to_assignees($user->id, Req::val('ids'));

        // TODO: Log event in a later version.

        $_SESSION['SUCCESS'] = L('addedtoassignees');
        break;

        // ##################
        // admin request
        // ##################
    case 'requestclose':
    case 'requestreopen':
        if ($action == 'requestclose') {
            Flyspray::adminRequest(1, $proj->id, $task['task_id'], $user->id, Post::val('reason_given'));
            Flyspray::logEvent($task['task_id'], 20, Post::val('reason_given'));
        } elseif ($action == 'requestreopen') {
            Flyspray::adminRequest(2, $proj->id, $task['task_id'], $user->id, Post::val('reason_given'));
            Flyspray::logEvent($task['task_id'], 21, Post::val('reason_given'));
            Backend::add_notification($user->id, $task['task_id']);
        }

        // Now, get the project managers' details for this project
        $sql = $db->query("SELECT  u.user_id
                             FROM  {users} u
                        LEFT JOIN  {users_in_groups} uig ON u.user_id = uig.user_id
                        LEFT JOIN  {groups} g ON uig.group_id = g.group_id
                            WHERE  g.project_id = ? AND g.manage_project = '1'",
        array($proj->id));

        $pms = $db->fetchCol($sql);
        if (count($pms)) {
            // Call the functions to create the address arrays, and send notifications
        $notify->create(NOTIFY_PM_REQUEST, $task['task_id'], null, $notify->specificAddresses($pms), NOTIFY_BOTH, $proj->prefs['lang_code']);
        }

        $_SESSION['SUCCESS'] = L('adminrequestmade');
        break;

        // ##################
        // denying a PM request
        // ##################
    case 'denypmreq':
        $result = $db->query("SELECT  task_id, project_id
                                FROM  {admin_requests}
                               WHERE  request_id = ?",
        array(Req::val('req_id')));
        $req_details = $db->fetchRow($result);

        if (!$user->perms('manage_project', $req_details['project_id'])) {
            break;
        }

        // Mark the PM request as 'resolved'
        $db->query("UPDATE  {admin_requests}
                       SET  resolved_by = ?, time_resolved = ?, deny_reason = ?
                     WHERE  request_id = ?",
        array($user->id, time(), Req::val('deny_reason'), Req::val('req_id')));

        Flyspray::logEvent($req_details['task_id'], 28, Req::val('deny_reason'));
        $notify->create(NOTIFY_PM_DENY_REQUEST, $req_details['task_id'], Req::val('deny_reason'), null, NOTIFY_BOTH, $proj->prefs['lang_code']);

        $_SESSION['SUCCESS'] = L('pmreqdeniedmsg');
        break;

        // ##################
        // deny a new user request
        // ##################
    case 'denyuserreq':
        if($user->perms('is_admin')) {
            $db->query("UPDATE  {admin_requests}
                       SET  resolved_by = ?, time_resolved = ?, deny_reason = ?
                     WHERE  request_id = ?",
            array($user->id, time(), Req::val('deny_reason'), Req::val('req_id')));
            // Wrong event constant
            Flyspray::logEvent(0, 28, Req::val('deny_reason'));//nee a new event number. need notification. fix smtp first
            // Missing notification constant, can't notify yet...
            $_SESSION['SUCCESS'] = "New user register request denied";
        }
        break;

        // ##################
        // adding a dependency
        // ##################
    case 'details.newdep':
        if (!$user->can_edit_task($task)) {
            Flyspray::show_error(L('nopermission'));//TODO: create a better error message
            break;
        }

        if (!Post::val('dep_task_id')) {
            Flyspray::show_error(L('formnotcomplete'));
            break;
        }

        // TODO: do the checks in some other order. Think about possibility
        // to combine many of the checks used to to see if a task exists,
        // if it's something user is allowed to know about etc to just one
        // function taking the necessary arguments and could be used in
        // several other places too.

        // if the user has not the permission to view all tasks, check if the task
        // is in tasks allowed to see, otherwise tell that the task does not exist.
        if (!$user->perms('view_tasks')) {
            $taskcheck = Flyspray::getTaskDetails(Post::val('dep_task_id'));
            if (!$user->can_view_task($taskcheck)) {
                Flyspray::show_error(L('dependaddfailed'));
                break;
            }
        }

        // First check that the user hasn't tried to add this twice
        $sql1 = $db->query('SELECT  COUNT(*) FROM {dependencies}
                             WHERE  task_id = ? AND dep_task_id = ?',
        array($task['task_id'], Post::val('dep_task_id')));

        // or that they are trying to reverse-depend the same task, creating a mutual-block
        $sql2 = $db->query('SELECT  COUNT(*) FROM {dependencies}
                             WHERE  task_id = ? AND dep_task_id = ?',
        array(Post::val('dep_task_id'), $task['task_id']));

        // Check that the dependency actually exists!
        $sql3 = $db->query('SELECT COUNT(*) FROM {tasks} WHERE task_id = ?',
                array(Post::val('dep_task_id')));

        if ($db->fetchOne($sql1) || $db->fetchOne($sql2) || !$db->fetchOne($sql3)
            // Check that the user hasn't tried to add the same task as a dependency
            || Post::val('task_id') == Post::val('dep_task_id'))
        {
            Flyspray::show_error(L('dependaddfailed'));
            break;
        }
        $notify->create(NOTIFY_DEP_ADDED, $task['task_id'], Post::val('dep_task_id'), null, NOTIFY_BOTH, $proj->prefs['lang_code']);
        $notify->create(NOTIFY_REV_DEP, Post::val('dep_task_id'), $task['task_id'], null, NOTIFY_BOTH, $proj->prefs['lang_code']);

        // Log this event to the task history, both ways
        Flyspray::logEvent($task['task_id'], 22, Post::val('dep_task_id'));
        Flyspray::logEvent(Post::val('dep_task_id'), 23, $task['task_id']);

        $db->query('INSERT INTO  {dependencies} (task_id, dep_task_id)
                         VALUES  (?,?)',
        array($task['task_id'], Post::val('dep_task_id')));

        $_SESSION['SUCCESS'] = L('dependadded');
        break;

        // ##################
        // removing a subtask
        // ##################
    case 'removesubtask':

        //check if the user has permissions to remove the subtask
        if (!$user->can_edit_task($task)) {
            Flyspray::show_error(L('nopermission'));//TODO: create a better error message
            break;
        }

        //set the subtask supertask_id to 0 removing parent child relationship
        $db->query("UPDATE {tasks} SET supertask_id=0 WHERE task_id = ?",
                   array(Post::val('subtaskid')));

        //write event log
        Flyspray::logEvent(Get::val('task_id'), 33, Post::val('subtaskid'));
        //post success message to the user
        $_SESSION['SUCCESS'] = L('subtaskremovedmsg');
        //redirect the user back to the right task
        Flyspray::redirect(createURL('details', Get::val('task_id')));
        break;

        // ##################
        // removing a dependency
        // ##################
    case 'removedep':
        if (!$user->can_edit_task($task)) {
            Flyspray::show_error(L('nopermission'));//TODO: create a better error message
            break;
        }

        $result = $db->query('SELECT  * FROM {dependencies}
                               WHERE  depend_id = ?',
        array(Post::val('depend_id')));
        $dep_info = $db->fetchRow($result);

        $db->query('DELETE FROM {dependencies} WHERE depend_id = ? AND task_id = ?',
                    array(Post::val('depend_id'), $task['task_id']));

        if ($db->affectedRows()) {
            $notify->create(NOTIFY_DEP_REMOVED, $dep_info['task_id'], $dep_info['dep_task_id'], null, NOTIFY_BOTH, $proj->prefs['lang_code']);
            $notify->create(NOTIFY_REV_DEP_REMOVED, $dep_info['dep_task_id'], $dep_info['task_id'], null, NOTIFY_BOTH, $proj->prefs['lang_code']);

            Flyspray::logEvent($dep_info['task_id'], 24, $dep_info['dep_task_id']);
            Flyspray::logEvent($dep_info['dep_task_id'], 25, $dep_info['task_id']);

            $_SESSION['SUCCESS'] = L('depremovedmsg');
        } else {
            Flyspray::show_error(L('erroronform'));
        }

        //redirect the user back to the right task
        Flyspray::redirect(createURL('details', Post::val('return_task_id')));
        break;

        // ##################
        // user requesting a password change
        // ##################
    case 'lostpw.sendmagic':
        // Check that the username exists
        $sql = $db->query('SELECT * FROM {users} WHERE user_name = ?',
                          array(Post::val('user_name')));

        // If the username doesn't exist, throw an error
        if (!$db->countRows($sql)) {
            Flyspray::show_error(L('usernotexist'));
            break;
        }

        $user_details = $db->fetchRow($sql);

        if ($user_details['oauth_provider']) {
            Flyspray::show_error(sprintf(L('oauthreqpass'), ucfirst($user_details['oauth_provider'])));
            Flyspray::redirect($baseurl);
            break;
        }

        //no microtime(), time,even with microseconds is predictable ;-)
        $magic_url    = md5(function_exists('openssl_random_pseudo_bytes') ?
                              openssl_random_pseudo_bytes(32) :
                              uniqid(mt_rand(), true));


        // Insert the random "magic url" into the user's profile
        $db->query('UPDATE {users}
                       SET magic_url = ?
                     WHERE user_id = ?',
        array($magic_url, $user_details['user_id']));

        if(count($user_details)) {
            $notify->create(NOTIFY_PW_CHANGE, null, array($baseurl, $magic_url), $notify->specificAddresses(array($user_details['user_id']), NOTIFY_EMAIL));
        }

        // TODO: Log event in a later version.

        $_SESSION['SUCCESS'] = L('magicurlsent');
        break;

        // ##################
        // Change the user's password
        // ##################
    case 'lostpw.chpass':
        // Check that the user submitted both the fields, and they are the same
        if (!Post::val('pass1') || strlen(trim(Post::val('magic_url'))) !== 32) {
            Flyspray::show_error(L('erroronform'));
            break;
        }

        if ($fs->prefs['repeat_password'] && Post::val('pass1') != Post::val('pass2')) {
            Flyspray::show_error(L('passnomatch'));
            break;
        }

        $new_pass_hash = Flyspray::cryptPassword(Post::val('pass1'));
        $db->query("UPDATE  {users} SET user_pass = ?, magic_url = ''
                     WHERE  magic_url = ?",
        array($new_pass_hash, Post::val('magic_url')));

        // TODO: Log event in a later version.

        $_SESSION['SUCCESS'] = L('passchanged');
        Flyspray::redirect($baseurl);
        break;

        // ##################
        // making a task private
        // ##################
    case 'makeprivate':
        // TODO: Have to think about this one a bit more. Are project manager
        // rights really needed for making a task a private? Are there some
        // other conditions that would permit it? Also making it back to public.
        if (!$user->perms('manage_project')) {
            break;
        }

        $db->query('UPDATE  {tasks}
                       SET  mark_private = 1
                     WHERE  task_id = ?', array($task['task_id']));

        Flyspray::logEvent($task['task_id'], 3, 1, 0, 'mark_private');

        $_SESSION['SUCCESS'] = L('taskmadeprivatemsg');
        break;

        // ##################
        // making a task public
        // ##################
    case 'makepublic':
        if (!$user->perms('manage_project')) {
            break;
        }

        $db->query('UPDATE  {tasks}
                       SET  mark_private = 0
                     WHERE  task_id = ?', array($task['task_id']));

        Flyspray::logEvent($task['task_id'], 3, 0, 1, 'mark_private');

        $_SESSION['SUCCESS'] = L('taskmadepublicmsg');
        break;

        // ##################
        // Adding a vote for a task
        // ##################
    case 'details.addvote':
        if (Backend::add_vote($user->id, $task['task_id'])) {
            $_SESSION['SUCCESS'] = L('voterecorded');
        } else {
            Flyspray::show_error(L('votefailed'));
            break;
        }
        // TODO: Log event in a later version.
        break;


	// ##################
	// Removing a vote for a task
	// ##################
	# used to remove a vote from myprofile page
	case 'removevote':
	# peterdd: I found no details.removevote action in source, so details.removevote is not used, but was planned on the task details page or in the old blue theme?
	case 'details.removevote':
		if (Backend::remove_vote($user->id, $task['task_id'])) {
			$_SESSION['SUCCESS'] = L('voteremoved');
		} else {
			Flyspray::show_error(L('voteremovefailed'));
			break;
		}
		// TODO: Log event in a later version, but also see if maybe done here Backend::remove_vote()...
	break;


        // ##################
        // set supertask id
        // ##################
    case 'details.setparent':
        if (!$user->can_edit_task($task)) {
            Flyspray::show_error(L('nopermission'));//TODO: create a better error message
            break;
        }

        if (!Post::val('supertask_id')) {
            Flyspray::show_error(L('formnotcomplete'));
            break;
        }

        // check that supertask_id is not same as task_id
        // preventing it from referring to itself
        if (Post::val('task_id') == Post::val('supertask_id')) {
            Flyspray::show_error(L('selfsupertasknotallowed'));
            break;
        }

	// Check that the supertask_id looks like unsigned integer
	if ( !preg_match("/^[1-9][0-9]{0,8}$/", Post::val('supertask_id')) ) {
		Flyspray::show_error(L('invalidsupertaskid'));
		break;
	}

	$sql = $db->query('SELECT project_id FROM {tasks} WHERE task_id = ?', array(Post::val('supertask_id')) );
	// check that supertask_id is a valid task id
	$parent = $db->fetchRow($sql);
	if (!$parent) {
		Flyspray::show_error(L('invalidsupertaskid'));
		break;
	}

	// if the user has not the permission to view all tasks, check if the task
	// is in tasks allowed to see, otherwise tell that the task does not exist.
	if (!$user->perms('view_tasks')) {
            $taskcheck = Flyspray::getTaskDetails(Post::val('supertask_id'));
            if (!$user->can_view_task($taskcheck)) {
                Flyspray::show_error(L('invalidsupertaskid'));
                break;
            }
	}

        // check to see that both tasks belong to the same project
        if ($task['project_id'] != $parent['project_id']) {
            Flyspray::show_error(L('musthavesameproject'));
            break;
        }

        // finally looks like all the checks are valid so update the supertask_id for the current task
        $db->query('UPDATE  {tasks}
                       SET  supertask_id = ?
                     WHERE  task_id = ?',
        array(Post::val('supertask_id'),Post::val('task_id')));

        // If task already had a different parent, then log removal too
        if ($task['supertask_id']) {
            Flyspray::logEvent($task['supertask_id'], 33, Post::val('task_id'));
            Flyspray::logEvent(Post::val('task_id'), 35, $task['supertask_id']);
        }

        // Log the events in the task history
        Flyspray::logEvent(Post::val('supertask_id'), 32, Post::val('task_id'));
        Flyspray::logEvent(Post::val('task_id'), 34, Post::val('supertask_id'));

        // set success message
        $_SESSION['SUCCESS'] = L('supertaskmodified');

        break;
    case 'notifications.remove':
        if(!isset($_POST['message_id'])) {
            // Flyspray::show_error(L('summaryanddetails'));
            break;
        }

        if (!is_array($_POST['message_id'])) {
            // Flyspray::show_error(L('summaryanddetails'));
            break;
        }
        if (!count($_POST['message_id'])) {
            // Nothing to do.
            break;
        }

        $validids = array();
        foreach ($_POST['message_id'] as $id) {
            if (is_numeric($id)) {
                if (settype($id, 'int') && $id > 0) {
                    $validids[] = $id;
                }
            }
        }

        if (!count($validids)) {
            // Nothing to do.
            break;
        }

        Notifications::NotificationsHaveBeenRead($validids);
        break;

	case 'admin.xmppcleanup':
		if (!$user->perms('is_admin')) {
			break;
		}

		if (isset($_POST['xmppcleanup']) && is_string($_POST['xmppcleanup'])) {
			if ($_POST['xmppcleanup'] === 'year') {
				if ($db->dbtype == 'pgsql') {
					// recipient_id is chronologic
					$recipientres = $db->query("SELECT recipient_id
						FROM {notification_recipients} r
						JOIN {notification_messages} m ON r.message_id=m.message_id
						WHERE r.notify_method='j'
						AND to_timestamp(time_created) < (CURRENT_TIMESTAMP - INTERVAL '1 year')
						ORDER BY recipient_id DESC LIMIT 1");
					$recipientrow=$db->fetchRow($recipientres);
				} else {
					// recipient_id is chronologic
					$recipientres = $db->query("SELECT recipient_id
						FROM {notification_recipients} r
						JOIN {notification_messages} m ON r.message_id=m.message_id
						WHERE r.notify_method='j'
						AND FROM_UNIXTIME(time_created) < (CURRENT_TIMESTAMP - INTERVAL 1 year)
						ORDER BY recipient_id DESC LIMIT 1");
					$recipientrow = $db->fetchRow($recipientres);
				}

				if ($recipientrow) {
					$db->query("DELETE FROM {notification_recipients} WHERE notify_method='j' AND recipient_id <=?", array($recipientrow[0]));
					$deleted = $db->affectedRows();
					$_SESSION['SUCCESS'] = $deleted == 1 ? '1 deleted unsent xmpp notification.': 'Deleted '.$deleted.' unsent xmpp notifications.';
					Flyspray::redirect(createURL('admin', 'checks'));
				}
				// TODO delete also the related notification_messages, but only I if that have no other recipient method entries (like notify_method='o' for 'online')  
			}
		}
		Flyspray::redirect(createURL('admin', 'checks'));
		break;

case 'task.bulkupdate':

	# TODO check if the user has the right to do each action on each task id he send with the form!
	# TODO check if tasks have open subtasks before closing
	# TODO SQL Transactions with rollback function if something went wrong in the middle of bulk action
	# disabled by default and if currently allowed only for admins until proper checks are done

	if (isset($fs->prefs['massops']) && $fs->prefs['massops']==1 && $user->perms('is_admin')){

		// TODO: Log events in a later version.

		$task_ids=filter_var($_POST['ids'], FILTER_VALIDATE_INT, FILTER_FORCE_ARRAY);

		if (Post::val('updateselectedtasks') == 'true') {

			// process quick actions
			switch(Post::val('bulk_quick_action')){
			case 'bulk_take_ownership':
				Backend::assign_to_me(Post::val('user_id'), Post::val('ids'));
				break;
			case 'bulk_start_watching':
				Backend::add_notification(Post::val('user_id'), Post::val('ids'));
				break;
			case 'bulk_stop_watching':
				Backend::remove_notification(Post::val('user_id'), Post::val('ids'));
				break;
			}

			$updateresult=Backend::updateTasks($_POST);
			break;

		} else {
			// bulk close
			if (!Post::val('resolution_reason')) {
				Flyspray::show_error(L('noclosereason'));
				break;
			}

			foreach ($task_ids as $task_id) {
				$task = Flyspray::getTaskDetails($task_id);
				if (!$user->can_close_task($task)) {
					continue;
				}

				if ($task['is_closed']) {
					continue;
				}

				Backend::close_task($task_id, Post::val('resolution_reason'), Post::val('closure_comment', ''), Post::val('mark100', false));
			}
			$_SESSION['SUCCESS'] = L('taskclosedmsg');
			break;
		}

	} # end if massopsenabled
	else{
		Flyspray::show_error(L('massopsdisabled'));
	}

} // end switch
