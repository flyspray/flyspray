<?php
/**
 * Flyspray
 *
 * Backend class
 *
 * This script contains reusable functions we use to modify
 * various things in the Flyspray database tables.
 *
 * @license http://opensource.org/licenses/lgpl-license.php Lesser GNU Public License
 * @package flyspray
 * @author Tony Collins, Florian Schmitz
 */

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

abstract class Backend
{
    /**
     * Adds the user $user_id to the notifications list of $tasks
     * @param integer $user_id
     * @param array $tasks
     * @param bool $do Force execution independent of user permissions
     * @access public
     * @return bool
     * @version 1.0
     */
    public static function add_notification($user_id, $tasks, $do = false)
    {
        global $db, $user;

        settype($tasks, 'array');

        $user_id = Flyspray::ValidUserId($user_id);

        if (!$user_id || !count($tasks)) {
            return false;
        }

        $sql = $db->Query(' SELECT *
                              FROM {tasks}
                             WHERE ' . substr(str_repeat(' task_id = ? OR ', count($tasks)), 0, -3),
                          $tasks);

        while ($row = $db->FetchRow($sql)) {
            // -> user adds himself
            if ($user->id == $user_id) {
                if (!$user->can_view_task($row) && !$do) {
                    continue;
                }
            // -> user is added by someone else
            } else  {
                if (!$user->perms('manage_project', $row['project_id']) && !$do) {
                    continue;
                }
            }

            $notif = $db->Query('SELECT notify_id
                                   FROM {notifications}
                                  WHERE task_id = ? and user_id = ?',
                              array($row['task_id'], $user_id));

            if (!$db->CountRows($notif)) {
                $db->Query('INSERT INTO {notifications} (task_id, user_id)
                                 VALUES  (?,?)', array($row['task_id'], $user_id));
                Flyspray::logEvent($row['task_id'], 9, $user_id);
            }
        }

        return (bool) $db->CountRows($sql);
    }


    /**
     * Removes a user $user_id from the notifications list of $tasks
     * @param integer $user_id
     * @param array $tasks
     * @access public
     * @return void
     * @version 1.0
     */

    public static function remove_notification($user_id, $tasks)
    {
        global $db, $user;

        settype($tasks, 'array');

        if (!count($tasks)) {
            return;
        }

        $sql = $db->Query(' SELECT *
                              FROM {tasks}
                             WHERE ' . substr(str_repeat(' task_id = ? OR ', count($tasks)), 0, -3),
                          $tasks);

        while ($row = $db->FetchRow($sql)) {
            // -> user removes himself
            if ($user->id == $user_id) {
                if (!$user->can_view_task($row)) {
                    continue;
                }
            // -> user is removed by someone else
            } else  {
                if (!$user->perms('manage_project', $row['project_id'])) {
                    continue;
                }
            }

            $db->Query('DELETE FROM  {notifications}
                              WHERE  task_id = ? AND user_id = ?',
                        array($row['task_id'], $user_id));
            if ($db->affectedRows()) {
                Flyspray::logEvent($row['task_id'], 10, $user_id);
            }
        }
    }


    /**
     * Assigns one or more $tasks only to a user $user_id
     * @param integer $user_id
     * @param array $tasks
     * @access public
     * @return void
     * @version 1.0
     */
    public static function assign_to_me($user_id, $tasks)
    {
        global $db, $notify;

        $user = $GLOBALS['user'];
        if ($user_id != $user->id) {
            $user = new User($user_id);
        }

        settype($tasks, 'array');
        if (!count($tasks)) {
            return;
        }

        $sql = $db->Query(' SELECT *
                              FROM {tasks}
                             WHERE ' . substr(str_repeat(' task_id = ? OR ', count($tasks)), 0, -3),
                          $tasks);

        while ($row = $db->FetchRow($sql)) {
            if (!$user->can_take_ownership($row)) {
                continue;
            }

            $db->Query('DELETE FROM {assigned}
                              WHERE task_id = ?',
                        array($row['task_id']));

            $db->Query('INSERT INTO {assigned}
                                    (task_id, user_id)
                             VALUES (?,?)',
                        array($row['task_id'], $user->id));

            if ($db->affectedRows()) {
                Flyspray::logEvent($row['task_id'], 19, $user->id, implode(' ', Flyspray::GetAssignees($row['task_id'])));
                $notify->Create(NOTIFY_OWNERSHIP, $row['task_id']);
            }

            if ($row['item_status'] == STATUS_UNCONFIRMED || $row['item_status'] == STATUS_NEW) {
                $db->Query('UPDATE {tasks} SET item_status = 3 WHERE task_id = ?', array($row['task_id']));
                Flyspray::logEvent($row['task_id'], 3, 3, 1, 'item_status');
            }
        }
    }

    /**
     * Adds a user $user_id to the assignees of one or more $tasks
     * @param integer $user_id
     * @param array $tasks
     * @param bool $do Force execution independent of user permissions
     * @access public
     * @return void
     * @version 1.0
     */
    public static function add_to_assignees($user_id, $tasks, $do = false)
    {
        global $db, $notify;

        settype($tasks, 'array');

        $user = $GLOBALS['user'];
        if ($user_id != $user->id) {
            $user = new User($user_id);
        }

        settype($tasks, 'array');
        if (!count($tasks)) {
            return;
        }

        $sql = $db->Query(' SELECT *
                              FROM {tasks}
                             WHERE ' . substr(str_repeat(' task_id = ? OR ', count($tasks)), 0, -3),
                             $tasks);

        while ($row = $db->FetchRow($sql)) {
            if (!$user->can_add_to_assignees($row) && !$do) {
                continue;
            }

            $db->Replace('{assigned}', array('user_id'=> $user->id, 'task_id'=> $row['task_id']), array('user_id','task_id'));

            if ($db->affectedRows()) {
                Flyspray::logEvent($row['task_id'], 29, $user->id, implode(' ', Flyspray::GetAssignees($row['task_id'])));
                $notify->Create(NOTIFY_ADDED_ASSIGNEES, $row['task_id']);
            }

            if ($row['item_status'] == STATUS_UNCONFIRMED || $row['item_status'] == STATUS_NEW) {
                $db->Query('UPDATE {tasks} SET item_status = 3 WHERE task_id = ?', array($row['task_id']));
                Flyspray::logEvent($row['task_id'], 3, 3, 1, 'item_status');
            }
        }
    }

    /**
     * Adds a vote from $user_id to the task $task_id
     * @param integer $user_id
     * @param integer $task_id
     * @access public
     * @return bool
     * @version 1.0
     */
    public static function add_vote($user_id, $task_id)
    {
        global $db;

        $user = $GLOBALS['user'];
        if ($user_id != $user->id) {
            $user = new User($user_id);
        }

        $task = Flyspray::GetTaskDetails($task_id);

        if (!$task) {
            return false;
        }

        if ($user->can_vote($task) > 0) {

            if($db->Query("INSERT INTO {votes} (user_id, task_id, date_time)
                           VALUES (?,?,?)", array($user->id, $task_id, time()))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Removes a vote from $user_id to the task $task_id
     * @param integer $user_id
     * @param integer $task_id
     * @access public
     * @return bool
     * @version 1.0
     */
    public static function remove_vote($user_id, $task_id)
    {
        global $db;

        $user = $GLOBALS['user'];
        if ($user_id != $user->id) {
            $user = new User($user_id);
        }

        $task = Flyspray::GetTaskDetails($task_id);

        if (!$task) {
            return false;
        }

        if ($user->can_vote($task) == -2) {

            if($db->Query("DELETE FROM {votes} WHERE user_id = ? and task_id = ?",
                            array($user->id, $task_id))) {
               return true;
            }
        }
        return false;
    }

    /**
     * Adds a comment to $task
     * @param array $task
     * @param string $comment_text
     * @param integer $time for synchronisation with other functions
     * @access public
     * @return bool
     * @version 1.0
     */
    public static function add_comment($task, $comment_text, $time = null)
    {
        global $db, $user, $notify;

        if (!($user->perms('add_comments', $task['project_id']) && (!$task['is_closed'] || $user->perms('comment_closed', $task['project_id'])))) {
            return false;
        }

        if (!is_string($comment_text) || !strlen($comment_text)) {
            return false;
        }

        $time =  !is_numeric($time) ? time() : $time ;

        $db->Query('INSERT INTO  {comments}
                                 (task_id, date_added, last_edited_time, user_id, comment_text)
                         VALUES  ( ?, ?, ?, ?, ? )',
                    array($task['task_id'], $time, $time, $user->id, $comment_text));

        $result = $db->Query('SELECT  comment_id
                                FROM  {comments}
                               WHERE  task_id = ?
                            ORDER BY  comment_id DESC',
                            array($task['task_id']), 1);
        $cid = $db->FetchOne($result);

        Flyspray::logEvent($task['task_id'], 4, $cid);

        if (Backend::upload_files($task['task_id'], $cid)) {
            $notify->Create(NOTIFY_COMMENT_ADDED, $task['task_id'], 'files');
        } else {
            $notify->Create(NOTIFY_COMMENT_ADDED, $task['task_id']);
        }

        return true;
    }

    /**
     * Upload files for a comment or a task
     * @param integer $task_id
     * @param integer $comment_id if it is 0, the files will be attached to the task itself
     * @param string $source name of the file input
     * @access public
     * @return bool
     * @version 1.0
     */
    public static function upload_files($task_id, $comment_id = 0, $source = 'userfile')
    {
        global $db, $notify, $conf, $user;

        $task = Flyspray::GetTaskDetails($task_id);

        if (!$user->perms('create_attachments', $task['project_id'])) {
            return false;
        }

        $res = false;

        if (!isset($_FILES[$source]['error'])) {
            return false;
        }

        foreach ($_FILES[$source]['error'] as $key => $error) {
            if ($error != UPLOAD_ERR_OK) {
                continue;
            }


            $fname = substr($task_id . '_' . md5(uniqid(mt_rand(), true)), 0, 30);
            $path = BASEDIR .'/attachments/'. $fname ;

            $tmp_name = $_FILES[$source]['tmp_name'][$key];

            // Then move the uploaded file and remove exe permissions
            if(!@move_uploaded_file($tmp_name, $path)) {
                //upload failed. continue
                continue;
            }

            @chmod($path, 0644);
            $res = true;

            // Use a different MIME type
            $fileparts = explode( '.', $_FILES[$source]['name'][$key]);
            $extension = end($fileparts);
            if (isset($conf['attachments'][$extension])) {
                $_FILES[$source]['type'][$key] = $conf['attachments'][$extension];
            //actually, try really hard to get the real filetype, not what the browser reports.
            } elseif($type = Flyspray::check_mime_type($path)) {
             $_FILES[$source]['type'][$key] = $type;
            }// we can try even more, however, far too much code is needed.

            $db->Query("INSERT INTO  {attachments}
                                     ( task_id, comment_id, file_name,
                                       file_type, file_size, orig_name,
                                       added_by, date_added)
                             VALUES  (?, ?, ?, ?, ?, ?, ?, ?)",
                    array($task_id, $comment_id, $fname,
                        $_FILES[$source]['type'][$key],
                        $_FILES[$source]['size'][$key],
                        $_FILES[$source]['name'][$key],
                        $user->id, time()));

            // Fetch the attachment id for the history log
            $result = $db->Query('SELECT  attachment_id
                                    FROM  {attachments}
                                   WHERE  task_id = ?
                                ORDER BY  attachment_id DESC',
                    array($task_id), 1);
            Flyspray::logEvent($task_id, 7, $db->fetchOne($result), $_FILES[$source]['name'][$key]);
        }

        return $res;
    }

    public static function upload_links($task_id, $comment_id = 0, $source = 'userlink')
    {
	    global $db, $user;

	    $task = Flyspray::GetTaskDetails($task_id);

	    if (!$user->perms('create_attachments', $task['project_id'])) {
		    return false;
	    }

	    if (!isset($_POST[$source])) {
		    return false;
	    }

	    $res = false;
	    foreach($_POST[$source] as $text) {
		    if(empty($text)) {
			    continue;
		    }

		    $res = true;

		    // Insert into database
		    $db->Query("INSERT INTO {links} (task_id, comment_id, url, added_by, date_added) VALUES (?, ?, ?, ?, ?)",
			    array($task_id, $comment_id, $text, $user->id, time()));
	    }

	    return $res;
    }

    /**
     * Delete one or more attachments of a task or comment
     * @param array $attachments
     * @access public
     * @return void
     * @version 1.0
     */
    public static function delete_files($attachments)
    {
        global $db, $user;

        settype($attachments, 'array');
        if (!count($attachments)) {
            return;
        }

        $sql = $db->Query(' SELECT t.*, a.*
                              FROM {attachments} a
                         LEFT JOIN {tasks} t ON t.task_id = a.task_id
                             WHERE ' . substr(str_repeat(' attachment_id = ? OR ', count($attachments)), 0, -3),
                          $attachments);

        while ($task = $db->FetchRow($sql)) {
            if (!$user->perms('delete_attachments', $task['project_id'])) {
                continue;
            }

            $db->Query('DELETE FROM {attachments} WHERE attachment_id = ?',
                       array($task['attachment_id']));
            @unlink(BASEDIR . '/attachments/' . $task['file_name']);
            Flyspray::logEvent($task['task_id'], 8, $task['orig_name']);
        }
    }

    public static function delete_links($links)
    {
	    global $db, $user;

	    settype($links, 'array');

	    if(!count($links)) {
		    return;
	    }

	    $sql = $db->Query('SELECT t.*, l.* FROM {links} l LEFT JOIN {tasks} t ON t.task_id = l.task_id WHERE '.substr(str_repeat('link_id = ? OR ', count($links)), 0, -3), $links);

	    //Delete from database
	    while($task = $db->FetchRow($sql)) {
		    if (!$user->perms('delete_attachments', $task['project_id'])) {
			    continue;
		    }

		    $db->Query('DELETE FROM {links} WHERE link_id = ?', array($task['link_id']));
	    }
    }

    /**
     * Cleans a username (length, special chars, spaces)
     * @param string $user_name
     * @access public
     * @return string
     */
    public static function clean_username($user_name)
    {
        // Limit length
        $user_name = substr(trim($user_name), 0, 32);
        // Remove doubled up spaces and control chars
        $user_name = preg_replace('![\x00-\x1f\s]+!u', ' ', $user_name);
        // Strip special chars
        return utf8_keepalphanum($user_name);
    }

    /**
     * Creates a new user
     * @param string $user_name
     * @param string $password
     * @param string $real_name
     * @param string $jabber_id
     * @param string $email
     * @param integer $notify_type
     * @param integer $time_zone
     * @param integer $group_in
     * @access public
     * @return bool false if username is already taken
     * @version 1.0
     * @notes This function does not have any permission checks (checked elsewhere)
     */
    public static function create_user($user_name, $password, $real_name, $jabber_id, $email, $notify_type, $time_zone, $group_in, $enabled, $oauth_uid = null, $oauth_provider = null, $profile_image = '')
    {
        global $fs, $db, $notify, $baseurl;

        $user_name = Backend::clean_username($user_name);

        // Limit length
        $real_name = substr(trim($real_name), 0, 100);
        // Remove doubled up spaces and control chars
        $real_name = preg_replace('![\x00-\x1f\s]+!u', ' ', $real_name);

        // Check to see if the username is available
        $sql = $db->Query('SELECT COUNT(*) FROM {users} WHERE user_name = ?', array($user_name));

        if ($db->fetchOne($sql)) {
            return false;
        }

        $auto = false;
        // Autogenerate a password
        if (!$password) {
            $auto = true;
            $password = substr(md5(uniqid(mt_rand(), true)), 0, mt_rand(8, 12));
        }

        $db->Query("INSERT INTO  {users}
                             ( user_name, user_pass, real_name, jabber_id, profile_image, magic_url,
                               email_address, notify_type, account_enabled,
                               tasks_perpage, register_date, time_zone, dateformat,
                               dateformat_extended, oauth_uid, oauth_provider, lang_code)
                     VALUES  ( ?, ?, ?, ?, ?, ?, ?, ?, ?, 25, ?, ?, ?, ?, ?, ?, ?)",
            array($user_name, Flyspray::cryptPassword($password), $real_name, strtolower($jabber_id),
                $profile_image, '', strtolower($email), $notify_type, $enabled, time(), $time_zone, '', '', $oauth_uid, $oauth_provider, $fs->prefs['lang_code']));

        $temp = $db->Query('SELECT user_id FROM {users} WHERE user_name = ?',array($user_name));
        $user_id = $db->fetchOne($temp);
        $emailList = explode(';',$email);
        foreach ($emailList as $mail)	//Still need to do: check email
        {
            $count = $db->Query("SELECT COUNT(*) FROM {user_emails} WHERE email_address = ?",array($mail));
            $count = $db->fetchOne($count);
            if ($count > 0)
            {
                Flyspray::show_error("Email address has alredy been taken");
                return false;
            } else if ($mail != '')
                $db->Query("INSERT INTO {user_emails}(id,email_address,oauth_uid,oauth_provider) VALUES (?,?,?,?)",array($user_id,strtolower($mail),$oauth_uid, $oauth_provider));
        }

        // Get this user's id for the record
        $uid = Flyspray::UserNameToId($user_name);

        // Now, create a new record in the users_in_groups table
        $db->Query('INSERT INTO  {users_in_groups} (user_id, group_id)
                         VALUES  (?, ?)', array($uid, $group_in));

        Flyspray::logEvent(0, 30, serialize(Flyspray::getUserDetails($uid)));

        $varnames = array('iwatch','atome','iopened');

        $toserialize = array('string' => NULL,
                        'type' => array (''),
                        'sev' => array (''),
                        'due' => array (''),
                        'dev' => NULL,
                        'cat' => array (''),
                        'status' => array ('open'),
                        'order' => NULL,
                        'sort' => NULL,
                        'percent' => array (''),
                        'opened' => NULL,
                        'search_in_comments' => NULL,
                        'search_for_all' => NULL,
                        'reported' => array (''),
                        'only_primary' => NULL,
                        'only_watched' => NULL);


                foreach($varnames as $tmpname) {

                    if($tmpname == 'iwatch') {

                        $tmparr = array('only_watched' => '1');

                    } elseif ($tmpname == 'atome') {

                        $tmparr = array('dev'=> $uid);

                    } elseif($tmpname == 'iopened') {

                        $tmparr = array('opened'=> $uid);
                    }

                    $$tmpname = $tmparr + $toserialize;
                }

        // Now give him his default searches
        $db->Query('INSERT INTO {searches} (user_id, name, search_string, time)
                         VALUES (?, ?, ?, ?)',
                    array($uid, L('taskswatched'), serialize($iwatch), time()));
        $db->Query('INSERT INTO {searches} (user_id, name, search_string, time)
                         VALUES (?, ?, ?, ?)',
                    array($uid, L('assignedtome'), serialize($atome), time()));
        $db->Query('INSERT INTO {searches} (user_id, name, search_string, time)
                         VALUES (?, ?, ?, ?)',
                    array($uid, L('tasksireported'), serialize($iopened), time()));

        if ($jabber_id) {
            Notifications::JabberRequestAuth($jabber_id);
        }

        // Send a user his details (his username might be altered, password auto-generated)
        // dont send notifications if the user logged in using oauth
        if (! $oauth_provider && $fs->prefs['notify_registration']) {
            // Gather list of admin users
            $sql = $db->Query('SELECT DISTINCT email_address
                                 FROM {users} u
                            LEFT JOIN {users_in_groups} g ON u.user_id = g.user_id
                                 WHERE g.group_id = 1');

            // If the new user is not an admin, add him to the notification list
            $users_to_notify = $db->FetchCol($sql);
            if (! in_array($email, $users_to_notify))
            {
                array_push($users_to_notify, $email);
            }

            // Notify the appropriate users
            $notify->Create(NOTIFY_NEW_USER, null,
                            array($baseurl, $user_name, $real_name, $email, $jabber_id, $password, $auto),
                            $users_to_notify, NOTIFY_EMAIL);
        }

        return true;
    }

	/**
	 * Deletes a user
	 * @param integer $uid
	 * @access public
	 * @return bool
	 * @version 1.0
	 */
	public static function delete_user($uid)
	{
		global $db, $user;

		if (!$user->perms('is_admin')) {
			return false;
		}

		$userDetails = Flyspray::getUserDetails($uid);

		if (is_file(BASEDIR.'/avatars/'.$userDetails['profile_image'])) {
			unlink(BASEDIR.'/avatars/'.$userDetails['profile_image']);
		}

		$tables = array('users', 'users_in_groups', 'searches', 'notifications', 'assigned', 'votes', 'effort');

		foreach ($tables as $table) {
			if (!$db->Query('DELETE FROM ' .'{' . $table .'}' . ' WHERE user_id = ?', array($uid))) {
				return false;
			}
		}

		if (!empty($userDetails['profile_image']) && is_file(BASEDIR.'/avatars/'.$userDetails['profile_image'])) {
			unlink(BASEDIR.'/avatars/'.$userDetails['profile_image']);
		}

		$db->Query('DELETE FROM {registrations} WHERE email_address = "'.$userDetails['email_address'].'"');
		$db->Query('DELETE FROM {user_emails} WHERE email_address = "'.$userDetails['email_address'].'"');
		$db->Query('DELETE FROM {reminders} WHERE to_user_id = '.$uid.' OR from_user_id = '.$uid);

		// for the unusual situuation that a user ID is re-used, make sure that the new user doesn't
		// get permissions for a task automatically
		$db->Query('UPDATE {tasks} SET opened_by = 0 WHERE opened_by = ?', array($uid));

		Flyspray::logEvent(0, 31, serialize($userDetails));

		return true;
	}


    /**
     * Deletes a project
     * @param integer $pid
     * @param integer $move_to to which project contents of the project are moved
     * @access public
     * @return bool
     * @version 1.0
     */
    public static function delete_project($pid, $move_to = 0)
    {
        global $db, $user;

        if (!$user->perms('manage_project', $pid)) {
            return false;
        }

        // Delete all project's tasks related information
        if (!$move_to) {
            $task_ids = $db->Query('SELECT task_id FROM {tasks} WHERE project_id = ' . intval($pid));
            $task_ids = $db->FetchCol($task_ids);
            $tables = array('admin_requests', 'assigned', 'attachments', 'comments', 'dependencies', 'related',
                            'field_values', 'history', 'notification_threads', 'notifications', 'redundant', 'reminders', 'votes');
            foreach ($tables as $table) {
                if ($table == 'related') {
                    $stmt = $db->dblink->prepare('DELETE FROM ' . $db->dbprefix . $table . ' WHERE this_task = ? OR related_task = ? ');
                } else {
                    $stmt = $db->dblink->prepare('DELETE FROM ' . $db->dbprefix . $table . ' WHERE task_id = ?');
                }
                foreach ($task_ids as $id) {
                    $db->dblink->Execute($stmt, ($table == 'related') ? array($id, $id) : array($id));
                }
            }
        }

        // unset category of tasks because we don't move categories
        if ($move_to) {
            $db->Query('UPDATE {tasks} SET product_category = 0 WHERE project_id = ?', array($pid));
        }

        $tables = array('list_category', 'list_os', 'list_resolution', 'list_tasktype',
                        'list_status', 'list_version', 'admin_requests',
                        'cache', 'projects', 'tasks');

        foreach ($tables as $table) {
            if ($move_to && $table !== 'projects' && $table !== 'list_category') {
                $base_sql = 'UPDATE {' . $table . '} SET project_id = ?';
                $sql_params = array($move_to, $pid);
            } else {
                $base_sql = 'DELETE FROM {' . $table . '}';
                $sql_params = array($pid);
            }

            if (!$db->Query($base_sql . ' WHERE project_id = ?', $sql_params)) {
                return false;
            }
        }

        // groups are only deleted, not moved (it is likely
        // that the destination project already has all kinds
        // of groups which are also used by the old project)
        $sql = $db->Query('SELECT group_id FROM {groups} WHERE project_id = ?', array($pid));
        while ($row = $db->FetchRow($sql)) {
            $db->Query('DELETE FROM {users_in_groups} WHERE group_id = ?', array($row['group_id']));
        }
        $sql = $db->Query('DELETE FROM {groups} WHERE project_id = ?', array($pid));

        //we have enough reasons ..  the process is OK.
        return true;
    }

    /**
     * Adds a reminder to a task
     * @param integer $task_id
     * @param string $message
     * @param integer $how_often send a reminder every ~ seconds
     * @param integer $start_time time when the reminder starts
     * @param $user_id the user who is reminded. by default (null) all users assigned to the task are reminded.
     * @access public
     * @return bool
     * @version 1.0
     */
    public static function add_reminder($task_id, $message, $how_often, $start_time, $user_id = null)
    {
        global $user, $db;
        $task = Flyspray::GetTaskDetails($task_id);

        if (!$user->perms('manage_project', $task['project_id'])) {
            return false;
        }

        if (is_null($user_id)) {
            // Get all users assigned to a task
            $user_id = Flyspray::GetAssignees($task_id);
        } else {
            $user_id = array(Flyspray::ValidUserId($user_id));
            if (!reset($user_id)) {
                return false;
            }
        }

        foreach ($user_id as $id) {
            $sql = $db->Replace('{reminders}',
                                array('task_id'=> $task_id, 'to_user_id'=> $id,
                                     'from_user_id' => $user->id, 'start_time' => $start_time,
                                     'how_often' => $how_often, 'reminder_message' => $message),
                                array('task_id', 'to_user_id', 'how_often', 'reminder_message'));
            if(!$sql) {
                // query has failed :(
                return false;
            }
        }
        // 2 = no record has found and was INSERT'ed correclty
        if (isset($sql) && $sql == 2) {
            Flyspray::logEvent($task_id, 17, $task_id);
        }
        return true;
    }

    /**
     * Adds a new task
     * @param array $args array containing all task properties. unknown properties will be ignored
     * @access public
     * @return integer the task ID on success
     * @version 1.0
     * @notes $args is POST data, bad..bad user..
     */
    public static function create_task($args)
    {
        global $db, $user, $proj;

        if (!isset($args)) return 0;

        // these are the POST variables that the user MUST send, if one of
        // them is missing or if one of them is empty, then we have to abort
        $requiredPostArgs = array('item_summary', 'project_id');//modify: made description not required
        foreach ($requiredPostArgs as $required) {
            if (empty($args[$required])) return 0;
        }

        $notify = new Notifications();
        if ($proj->id != $args['project_id']) {
            $proj = new Project($args['project_id']);
        }

        if (!$user->can_open_task($proj)) {
            return 0;
        }

        // first populate map with default values
        $sql_args = array(
            'project_id' => $proj->id,
            'date_opened' => time(),
            'last_edited_time' => time(),
            'opened_by' => intval($user->id),
            'percent_complete' => 0,
            'mark_private' => 0,
            'supertask_id' => 0,
            'closedby_version' => 0,
            'closure_comment' => '',
            'task_priority' => 2,
            'due_date' => 0,
            'anon_email' => '',
            'item_status'=> STATUS_UNCONFIRMED
        );

        // POST variables the user is ALLOWED to provide
        $allowedPostArgs = array(
            'task_type', 'product_category', 'product_version',
            'operating_system', 'task_severity', 'estimated_effort',
            'supertask_id', 'item_summary', 'detailed_desc'
        );
        // these POST variables the user is only ALLOWED to provide if he got the permissions
        if ($user->perms('modify_all_tasks')) {
            $allowedPostArgs[] = 'closedby_version';
            $allowedPostArgs[] = 'task_priority';
            $allowedPostArgs[] = 'due_date';
            $allowedPostArgs[] = 'item_status';
        }
        if ($user->perms('manage_project')) {
            $allowedPostArgs[] = 'mark_private';
        }
        // now copy all over all POST variables the user is ALLOWED to provide
        // (but only if they are not empty)
        foreach ($allowedPostArgs as $allowed) {
            if (!empty($args[$allowed])) {
                $sql_args[$allowed] = $args[$allowed];
            }
        }

        // Process the due_date
        if ( isset($args['due_date']) && ($due_date = $args['due_date']) || ($due_date = 0) ) {
            $due_date = Flyspray::strtotime($due_date);
        }

        $sql_params[] = 'mark_private';
        $sql_values[] = intval($user->perms('manage_project') && isset($args['mark_private']) && $args['mark_private'] == '1');

        $sql_params[] = 'due_date';
        $sql_values[] = $due_date;

        $sql_params[] = 'closure_comment';
        $sql_values[] = '';

        $sql_params[] = 'estimated_effort';
        $sql_values[] = $proj->prefs['use_effort_tracking'] ? $args['estimated_effort'] : 0;

        // Token for anonymous users
        $token = '';
        if ($user->isAnon()) {
            if (empty($args['anon_email'])) {
                return 0;
            }
            $token = md5(function_exists('openssl_random_pseudo_bytes') ?
                              openssl_random_pseudo_bytes(32) :
                              uniqid(mt_rand(), true));
            $sql_args['task_token'] = $token;
            $sql_args['anon_email'] = $args['anon_email'];
        }

        // ensure all variables are in correct format
        if (!empty($sql_args['due_date'])) {
            $sql_args['due_date'] = Flyspray::strtotime($sql_args['due_date']);
        }
        if (isset($sql_args['mark_private'])) {
            $sql_args['mark_private'] = intval($sql_args['mark_private'] == '1');
        }

        // split keys and values into two separate arrays
        $sql_keys   = array();
        $sql_values = array();
        foreach ($sql_args as $key => $value) {
            $sql_keys[]   = $key;
            $sql_values[] = $value;
        }


        $result = $db->Query('SELECT  MAX(task_id)+1
                                FROM  {tasks}');
        $task_id = $db->FetchOne($result);
        $task_id = $task_id ? $task_id : 1;

        //now, $task_id is always the first element of $sql_values
        array_unshift($sql_keys, 'task_id');
        array_unshift($sql_values, $task_id);

        $sql_keys_string = join(', ', $sql_keys);
        $sql_placeholder = $db->fill_placeholders($sql_values);

        $result = $db->Query("INSERT INTO  {tasks}
                                 ($sql_keys_string)
                         VALUES  ($sql_placeholder)", $sql_values);

	/////////////////////////////////////Add tags///////////////////////////////////////
	$tagList = explode(';',$args['tags']);
	foreach ($tagList as $tag)
	{
	   if ($tag == '')
		   continue;
	   $result2 = $db->Query("INSERT INTO {tags} (task_id, tag)
		                           VALUES (?,?)",array($task_id,$tag));
        }

        ////////////////////////////////////////////////////////////////////////////////////
        // Log the assignments and send notifications to the assignees
        if (isset($args['rassigned_to']) && is_array($args['rassigned_to']))
        {
            // Convert assigned_to and store them in the 'assigned' table
            foreach ($args['rassigned_to'] as $val)
            {
                $db->Replace('{assigned}', array('user_id'=> $val, 'task_id'=> $task_id), array('user_id','task_id'));
            }
            // Log to task history
            Flyspray::logEvent($task_id, 14, implode(' ', $args['rassigned_to']));

            // Notify the new assignees what happened.  This obviously won't happen if the task is now assigned to no-one.
            $notify->Create(NOTIFY_NEW_ASSIGNEE, $task_id, null, $notify->SpecificAddresses($args['rassigned_to']));
        }

        // Log that the task was opened
        Flyspray::logEvent($task_id, 1);

        $result = $db->Query('SELECT  *
                                FROM  {list_category}
                               WHERE  category_id = ?',
                               array($args['product_category']));
        $cat_details = $db->FetchRow($result);

        // We need to figure out who is the category owner for this task
        if (!empty($cat_details['category_owner'])) {
            $owner = $cat_details['category_owner'];
        }
        else {
            // check parent categories
            $result = $db->Query('SELECT  *
                                    FROM  {list_category}
                                   WHERE  lft < ? AND rgt > ? AND project_id  = ?
                                ORDER BY  lft DESC',
                                   array($cat_details['lft'], $cat_details['rgt'], $cat_details['project_id']));
            while ($row = $db->FetchRow($result)) {
                // If there's a parent category owner, send to them
                if (!empty($row['category_owner'])) {
                    $owner = $row['category_owner'];
                    break;
                }
            }
        }

        if (!isset($owner)) {
            $owner = $proj->prefs['default_cat_owner'];
        }

        if ($owner) {
            if ($proj->prefs['auto_assign'] && ($args['item_status'] == STATUS_UNCONFIRMED || $args['item_status'] == STATUS_NEW)) {
                Backend::add_to_assignees($owner, $task_id, true);
            }
            Backend::add_notification($owner, $task_id, true);
        }

        // Reminder for due_date field
        if (!empty($sql_args['due_date'])) {
            Backend::add_reminder($task_id, L('defaultreminder') . "\n\n" . CreateURL('details', $task_id), 2*24*60*60, time());
        }

        // Create the Notification
        if (Backend::upload_files($task_id)) {
            $notify->Create(NOTIFY_TASK_OPENED, $task_id, 'files');
        } else {
            $notify->Create(NOTIFY_TASK_OPENED, $task_id);
        }

        // If the reporter wanted to be added to the notification list
        if (isset($args['notifyme']) && $args['notifyme'] == '1' && $user->id != $owner) {
            Backend::add_notification($user->id, $task_id, true);
        }

        if ($user->isAnon()) {
            $notify->Create(NOTIFY_ANON_TASK, $task_id, $token, $args['anon_email'], NOTIFY_EMAIL);
        }

        return array($task_id, $token);
    }

    /**
     * Closes a task
     * @param integer $task_id
     * @param integer $reason
     * @param string $comment
     * @param bool $mark100
     * @access public
     * @return bool
     * @version 1.0
     */
    public static function close_task($task_id, $reason, $comment, $mark100 = true)
    {
        global $db, $notify, $user;
        $task = Flyspray::GetTaskDetails($task_id);

        if (!$user->can_close_task($task)) {
            return false;
        }

        if ($task['is_closed']) {
            return false;
        }

        $db->Query('UPDATE  {tasks}
                       SET  date_closed = ?, closed_by = ?, closure_comment = ?,
                            is_closed = 1, resolution_reason = ?, last_edited_time = ?,
                            last_edited_by = ?
                     WHERE  task_id = ?',
                    array(time(), $user->id, $comment, $reason, time(), $user->id, $task_id));

        if ($mark100) {
            $db->Query('UPDATE {tasks} SET percent_complete = 100 WHERE task_id = ?',
                       array($task_id));

            Flyspray::logEvent($task_id, 3, 100, $task['percent_complete'], 'percent_complete');
        }

        $notify->Create(NOTIFY_TASK_CLOSED, $task_id);
        Flyspray::logEvent($task_id, 2, $reason, $comment);

        // If there's an admin request related to this, close it
        $db->Query('UPDATE  {admin_requests}
                       SET  resolved_by = ?, time_resolved = ?
                     WHERE  task_id = ? AND request_type = ?',
                    array($user->id, time(), $task_id, 1));

        // duplicate
        if ($reason == 6) {
            preg_match("/\b(?:FS#|bug )(\d+)\b/", $comment, $dupe_of);
            if (count($dupe_of) >= 2) {
                $existing = $db->Query('SELECT * FROM {related} WHERE this_task = ? AND related_task = ? AND is_duplicate = 1',
                                        array($task_id, $dupe_of[1]));

                if ($existing && $db->CountRows($existing) == 0) {
                    $db->Query('INSERT INTO {related} (this_task, related_task, is_duplicate) VALUES(?, ?, 1)',
                                array($task_id, $dupe_of[1]));
                }
                Backend::add_vote($task['opened_by'], $dupe_of[1]);
            }
        }

        return true;
    }

    /**
     * Returns an array of tasks (respecting pagination) and an ID list (all tasks)
     * @param array $args
     * @param array $visible
     * @param integer $offset
     * @param integer $comment
     * @param bool $perpage
     * @access public
     * @return array
     * @version 1.0
     */
    public static function get_task_list($args, $visible, $offset = 0, $perpage = 20)
    {
        global $proj, $db, $user, $conf;
        /* build SQL statement {{{ */
        // Original SQL courtesy of Lance Conry http://www.rhinosw.com/
        $where  = $sql_params = array();

        $select = '';
        $groupby = 't.task_id, ';
        $from   = '             {tasks}         t
                     LEFT JOIN  {projects}      p   ON t.project_id = p.project_id
                     LEFT JOIN  {list_tasktype} lt  ON t.task_type = lt.tasktype_id
                     LEFT JOIN  {list_status}   lst ON t.item_status = lst.status_id
                     LEFT JOIN  {list_resolution} lr ON t.resolution_reason = lr.resolution_id ';
        // Only join tables which are really necessary to speed up the db-query
        if (array_get($args, 'cat') || in_array('category', $visible)) {
            $from   .= ' LEFT JOIN  {list_category} lc  ON t.product_category = lc.category_id ';
            $select .= ' lc.category_name               AS category_name, ';
            $groupby .= 'lc.category_name, ';
        }
        if (in_array('votes', $visible)) {
            $from   .= ' LEFT JOIN  {votes} vot         ON t.task_id = vot.task_id ';
            $select .= ' COUNT(DISTINCT vot.vote_id)    AS num_votes, ';
        }
        $maxdatesql = ' GREATEST((SELECT max(c.date_added) FROM {comments} c WHERE c.task_id = t.task_id), t.date_opened, t.date_closed, t.last_edited_time) ';
        $search_for_changes = in_array('lastedit', $visible) || array_get($args, 'changedto') || array_get($args, 'changedfrom');
        if ($search_for_changes) {
            $select .= ' GREATEST((SELECT max(c.date_added) FROM {comments} c WHERE c.task_id = t.task_id), t.date_opened, t.date_closed, t.last_edited_time) AS max_date, ';
        }
        if (array_get($args, 'search_in_comments')) {
            $from   .= ' LEFT JOIN  {comments} c          ON t.task_id = c.task_id ';
        }
        if (in_array('comments', $visible)) {
            $select .= ' (SELECT COUNT(cc.comment_id) FROM {comments} cc WHERE cc.task_id = t.task_id)  AS num_comments, ';
        }
        if (in_array('reportedin', $visible)) {
            $from   .= ' LEFT JOIN  {list_version} lv   ON t.product_version = lv.version_id ';
            $select .= ' lv.version_name                AS product_version, ';
            $groupby .= 'lv.version_name, ';
        }
        if (array_get($args, 'opened') || in_array('openedby', $visible)) {
            $from   .= ' LEFT JOIN  {users} uo          ON t.opened_by = uo.user_id ';
            $select .= ' uo.real_name                   AS opened_by_name, ';
            $groupby .= 'uo.real_name, ';
        }
        if (array_get($args, 'closed')) {
            $from   .= ' LEFT JOIN  {users} uc          ON t.closed_by = uc.user_id ';
            $select .= ' uc.real_name                   AS closed_by_name, ';
            $groupby .= 'uc.real_name, ';
        }
        if (array_get($args, 'due') || in_array('dueversion', $visible)) {
            $from   .= ' LEFT JOIN  {list_version} lvc  ON t.closedby_version = lvc.version_id ';
            $select .= ' lvc.version_name               AS closedby_version, ';
            $groupby .= 'lvc.version_name, ';
        }
        if (in_array('os', $visible)) {
            $from   .= ' LEFT JOIN  {list_os} los       ON t.operating_system = los.os_id ';
            $select .= ' los.os_name                    AS os_name, ';
            $groupby .= 'los.os_name, ';
        }
        if (in_array('attachments', $visible) || array_get($args, 'has_attachment')) {
            $from   .= ' LEFT JOIN  {attachments} att   ON t.task_id = att.task_id ';
            $select .= ' COUNT(DISTINCT att.attachment_id) AS num_attachments, ';
        }

	# 20150213 currently without recursive subtasks!
	if (in_array('effort', $visible)) {
		$from   .= ' LEFT JOIN  {effort} ef   ON t.task_id = ef.task_id ';
		$select .= ' SUM( ef.effort) AS effort, ';
	}

        $from   .= ' LEFT JOIN  {assigned} ass      ON t.task_id = ass.task_id ';
        $from   .= ' LEFT JOIN  {users} u           ON ass.user_id = u.user_id ';
        if (array_get($args, 'dev') || in_array('assignedto', $visible)) {
            $select .= ' MIN(u.real_name)               AS assigned_to_name, ';
            $select .= ' COUNT(DISTINCT ass.user_id)    AS num_assigned, ';
        }

        if (array_get($args, 'only_primary')) {
            $from   .= ' LEFT JOIN  {dependencies} dep  ON dep.dep_task_id = t.task_id ';
            $where[] = 'dep.depend_id IS NULL';
        }
        if (array_get($args, 'has_attachment')) {
            $where[] = 'att.attachment_id IS NOT NULL';
        }

        if (array_get($args, 'hide_subtasks')) {
            $where[] = 't.supertask_id = 0';
        }

        if ($proj->id) {
            $where[]       = 't.project_id = ?';
            $sql_params[]  = $proj->id;
        }

        $order_keys = array (
                'id'           => 't.task_id',
                'project'      => 'project_title',
                'tasktype'     => 'tasktype_name',
                'dateopened'   => 'date_opened',
                'summary'      => 'item_summary',
                'severity'     => 'task_severity',
                'category'     => 'lc.category_name',
                'status'       => 'is_closed, item_status',
                'dueversion'   => 'lvc.list_position',
                'duedate'      => 'due_date',
                'progress'     => 'percent_complete',
                'lastedit'     => 'max_date',
                'priority'     => 'task_priority',
                'openedby'     => 'uo.real_name',
                'reportedin'   => 't.product_version',
                'assignedto'   => 'u.real_name',
                'dateclosed'   => 't.date_closed',
                'os'           => 'los.os_name',
                'votes'        => 'num_votes',
                'attachments'  => 'num_attachments',
                'comments'     => 'num_comments',
                'private'      => 'mark_private',
                'supertask'    => 't.supertask_id',
        );

        // make sure that only columns can be sorted that are visible (and task severity, since it is always loaded)
        $order_keys = array_intersect_key($order_keys, array_merge(array_flip($visible), array('severity' => 'task_severity')));

        $order_column[0] = $order_keys[Filters::enum(array_get($args, 'order', 'priority'), array_keys($order_keys))];
        $order_column[1] = $order_keys[Filters::enum(array_get($args, 'order2', 'severity'), array_keys($order_keys))];
        $sortorder  = sprintf('%s %s, %s %s, t.task_id ASC',
                $order_column[0], Filters::enum(array_get($args, 'sort', 'desc'), array('asc', 'desc')),
                $order_column[1], Filters::enum(array_get($args, 'sort2', 'desc'), array('asc', 'desc')));

        /// process search-conditions {{{
        $submits = array('type' => 'task_type', 'sev' => 'task_severity', 'due' => 'closedby_version', 'reported' => 'product_version',
                         'cat' => 'product_category', 'status' => 'item_status', 'percent' => 'percent_complete', 'pri' => 'task_priority',
                         'dev' => array('a.user_id', 'us.user_name', 'us.real_name'),
                         'opened' => array('opened_by', 'uo.user_name', 'uo.real_name'),
                         'closed' => array('closed_by', 'uc.user_name', 'uc.real_name'));
        foreach ($submits as $key => $db_key) {
            $type = array_get($args, $key, ($key == 'status') ? 'open' : '');
            settype($type, 'array');

            if (in_array('', $type)) continue;

            if ($key == 'dev') {
            	setcookie('tasklist_type', 'assignedtome');
                $from .= 'LEFT JOIN {assigned} a  ON t.task_id = a.task_id ';
                $from .= 'LEFT JOIN {users} us  ON a.user_id = us.user_id ';
            }
            else
            {
            	setcookie('tasklist_type', 'project');
            }

            $temp = '';
            $condition = '';
            foreach ($type as $val) {
                // add conditions for the status selection
                if ($key == 'status' && $val == 'closed' && !in_array('open', $type)) {
                    $temp  .= " is_closed = '1' AND";
                } elseif ($key == 'status' && !in_array('closed', $type)) {
                    $temp .= " is_closed <> '1' AND";
                }
                if (is_numeric($val) && !is_array($db_key) && !($key == 'status' && $val == 'closed')) {
                    $temp .= ' ' . $db_key . ' = ?  OR';
                    $sql_params[] = $val;
                } elseif (is_array($db_key)) {
                    if ($key == 'dev' && ($val == 'notassigned' || $val == '0' || $val == '-1')) {
                        $temp .= ' a.user_id is NULL  OR';
                    } else {
                        foreach ($db_key as $singleDBKey) {
                            if (strpos($singleDBKey, '_name') !== false) {
                                $temp .= ' ' . $singleDBKey . ' LIKE ? OR ';
                                $sql_params[] = '%' . $val . '%';
                            } elseif (is_numeric($val)) {
                                $temp .= ' ' . $singleDBKey . ' = ? OR';
                                $sql_params[] = $val;
                            }
                        }
                    }
                }

                // Add the subcategories to the query
                if ($key == 'cat') {
                    $result = $db->Query('SELECT  *
                                            FROM  {list_category}
                                           WHERE  category_id = ?',
                                          array($val));
                    $cat_details = $db->FetchRow($result);

                    $result = $db->Query('SELECT  *
                                            FROM  {list_category}
                                           WHERE  lft > ? AND rgt < ? AND project_id  = ?',
                                           array($cat_details['lft'], $cat_details['rgt'], $cat_details['project_id']));
                    while ($row = $db->FetchRow($result)) {
                        $temp  .= ' product_category = ?  OR';
                        $sql_params[] = $row['category_id'];
                    }
                }
            }

            if ($temp) $where[] = '(' . substr($temp, 0, -3) . ')';
        }
        /// }}}
        $having = array();
        $dates = array('duedate' => 'due_date', 'changed' => $maxdatesql,
                       'opened' => 'date_opened', 'closed' => 'date_closed');
        foreach ($dates as $post => $db_key) {
            $var = ($post == 'changed') ? 'having' : 'where';
            if ($date = array_get($args, $post . 'from')) {
                ${$var}[]      = '(' . $db_key . ' >= ' . Flyspray::strtotime($date) . ')';
            }
            if ($date = array_get($args, $post . 'to')) {
                ${$var}[]      = '(' . $db_key . ' <= ' . Flyspray::strtotime($date) . ' AND ' . $db_key . ' > 0)';
            }
        }

        if (array_get($args, 'string')) {
            $words = explode(' ', strtr(array_get($args, 'string'), '()', '  '));
            $comments = '';
            $where_temp = array();

            if (array_get($args, 'search_in_comments')) {
                $comments .= 'OR c.comment_text LIKE ?';
            }
            if (array_get($args, 'search_in_details')) {
                $comments .= 'OR t.detailed_desc LIKE ?';
            }

            foreach ($words as $word) {
                $likeWord = '%' . str_replace('+', ' ', trim($word)) . '%';
                $where_temp[] = "(t.item_summary LIKE ? OR t.task_id = ? $comments)";
                array_push($sql_params, $likeWord, intval($word));
                if (array_get($args, 'search_in_comments')) {
                    array_push($sql_params, $likeWord);
                }
                if (array_get($args, 'search_in_details')) {
                    array_push($sql_params, $likeWord);
                }
            }

            $where[] = '(' . implode( (array_get($args, 'search_for_all') ? ' AND ' : ' OR '), $where_temp) . ')';
        }

        if (array_get($args, 'only_watched')) {
            //join the notification table to get watched tasks
            $from        .= ' LEFT JOIN {notifications} fsn ON t.task_id = fsn.task_id';
            $where[]      = 'fsn.user_id = ?';
            $sql_params[] = $user->id;
        }

        $where = (count($where)) ? 'WHERE '. join(' AND ', $where) : '';

        // Get the column names of table tasks for the group by statement
        if (!strcasecmp($conf['database']['dbtype'], 'pgsql')) {
             $groupby .= "p.project_title, p.project_is_active, lst.status_name, lt.tasktype_name,{$order_column[0]},{$order_column[1]}, lr.resolution_name, ";
             $groupby .= $db->GetColumnNames('{tasks}', 't.task_id', 't.');
        } else {
            $groupby = 't.task_id';
        }

        $having = (count($having)) ? 'HAVING '. join(' AND ', $having) : '';

        $sql = $db->Query("
                          SELECT   t.*, $select
                                   p.project_title, p.project_is_active,
                                   lst.status_name AS status_name,
                                   lt.tasktype_name AS task_type,
                                   lr.resolution_name
                          FROM     $from
                          $where
                          GROUP BY $groupby
                          $having
                          ORDER BY $sortorder", $sql_params);

        $tasks = $db->fetchAllArray($sql);
        $id_list = array();
        $limit = array_get($args, 'limit', -1);
        $task_count = 0;
        foreach ($tasks as $key => $task) {
            $id_list[] = $task['task_id'];
            if (!$user->can_view_task($task)) {
                unset($tasks[$key]);
                array_pop($id_list);
                --$task_count;
            } elseif (!is_null($perpage) && ($task_count < $offset || ($task_count > $offset - 1 + $perpage) || ($limit > 0 && $task_count >= $limit))) {
                unset($tasks[$key]);
            }

            ++$task_count;
        }

        return array($tasks, $id_list);
    }

}
