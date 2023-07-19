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

        $user_id = Flyspray::validUserId($user_id);

        if (!$user_id || !count($tasks)) {
            return false;
        }

        $sql = $db->query(' SELECT *
                              FROM {tasks}
                             WHERE ' . substr(str_repeat(' task_id = ? OR ', count($tasks)), 0, -3),
                          $tasks);

        while ($row = $db->fetchRow($sql)) {
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

            $notif = $db->query('SELECT notify_id
                                   FROM {notifications}
                                  WHERE task_id = ? and user_id = ?',
                              array($row['task_id'], $user_id));

            if (!$db->countRows($notif)) {
                $db->query('INSERT INTO {notifications} (task_id, user_id)
                                 VALUES  (?,?)', array($row['task_id'], $user_id));
                Flyspray::logEvent($row['task_id'], 9, $user_id);
            }
        }

        return (bool) $db->countRows($sql);
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

        $sql = $db->query(' SELECT *
                              FROM {tasks}
                             WHERE ' . substr(str_repeat(' task_id = ? OR ', count($tasks)), 0, -3),
                          $tasks);

        while ($row = $db->fetchRow($sql)) {
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

            $db->query('DELETE FROM  {notifications}
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

        $sql = $db->query(' SELECT *
                              FROM {tasks}
                             WHERE ' . substr(str_repeat(' task_id = ? OR ', count($tasks)), 0, -3),
                          $tasks);

        while ($row = $db->fetchRow($sql)) {
            if (!$user->can_take_ownership($row)) {
                continue;
            }

            $db->query('DELETE FROM {assigned}
                              WHERE task_id = ?',
                        array($row['task_id']));

            $db->query('INSERT INTO {assigned}
                                    (task_id, user_id)
                             VALUES (?,?)',
                        array($row['task_id'], $user->id));

            if ($db->affectedRows()) {
                $current_proj = new Project($row['project_id']);
                Flyspray::logEvent($row['task_id'], 19, $user->id, implode(' ', Flyspray::getAssignees($row['task_id'])));
                $notify->create(NOTIFY_OWNERSHIP, $row['task_id'], null, null, NOTIFY_BOTH, $current_proj->prefs['lang_code']);
            }

            if ($row['item_status'] == STATUS_UNCONFIRMED || $row['item_status'] == STATUS_NEW) {
                $db->query('UPDATE {tasks} SET item_status = 3 WHERE task_id = ?', array($row['task_id']));
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

        $sql = $db->query(' SELECT *
                              FROM {tasks}
                             WHERE ' . substr(str_repeat(' task_id = ? OR ', count($tasks)), 0, -3),
                             $tasks);

        while ($row = $db->fetchRow($sql)) {
            if (!$user->can_add_to_assignees($row) && !$do) {
                continue;
            }

            $db->replace('{assigned}', array('user_id'=> $user->id, 'task_id'=> $row['task_id']), array('user_id','task_id'));

            if ($db->affectedRows()) {
                $current_proj = new Project($row['project_id']);
                Flyspray::logEvent($row['task_id'], 29, $user->id, implode(' ', Flyspray::getAssignees($row['task_id'])));
                $notify->create(NOTIFY_ADDED_ASSIGNEES, $row['task_id'], null, null, NOTIFY_BOTH, $current_proj->prefs['lang_code']);
            }

            if ($row['item_status'] == STATUS_UNCONFIRMED || $row['item_status'] == STATUS_NEW) {
                $db->query('UPDATE {tasks} SET item_status = 3 WHERE task_id = ?', array($row['task_id']));
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

        $task = Flyspray::getTaskDetails($task_id);

        if (!$task) {
            return false;
        }

        if ($user->can_vote($task) > 0) {

            if($db->query("INSERT INTO {votes} (user_id, task_id, date_time)
                           VALUES (?,?,?)", array($user->id, $task_id, time()))) {
                // TODO: Log event in a later version.
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

        $task = Flyspray::getTaskDetails($task_id);

        if (!$task) {
            return false;
        }

        if ($user->can_vote($task) == -2) {

            if($db->query("DELETE FROM {votes} WHERE user_id = ? and task_id = ?",
                            array($user->id, $task_id))) {
                // TODO: Log event in a later version.
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
        global $conf, $db, $user, $notify, $proj, $fs;

        if (!($user->perms('add_comments', $task['project_id']) && (!$task['is_closed'] || $user->perms('comment_closed', $task['project_id'])))) {
            return false;
        }

	if ($conf['general']['syntax_plugin'] != 'dokuwiki') {
		$purifierconfig = HTMLPurifier_Config::createDefault();
		$purifierconfig->set('CSS.AllowedProperties', array());
		if ($fs->prefs['relnofollow']) {
			$purifierconfig->set('HTML.Nofollow', true);
		}
		$purifier = new HTMLPurifier($purifierconfig);
		$comment_text = $purifier->purify($comment_text);
	}
	    
        if (!is_string($comment_text) || !strlen($comment_text)) {
            return false;
        }

        $time =  !is_numeric($time) ? time() : $time ;

        $db->query('INSERT INTO {comments}
                                (task_id, date_added, last_edited_time, user_id, comment_text)
                         VALUES ( ?, ?, ?, ?, ? )',
                    array($task['task_id'], $time, $time, $user->id, $comment_text));
        $cid = $db->Insert_ID();
	Backend::upload_links($task['task_id'], $cid);
        Flyspray::logEvent($task['task_id'], 4, $cid);

        if (Backend::upload_files($task['task_id'], $cid)) {
            $notify->create(NOTIFY_COMMENT_ADDED, $task['task_id'], 'files', null, NOTIFY_BOTH, $proj->prefs['lang_code']);
        } else {
            $notify->create(NOTIFY_COMMENT_ADDED, $task['task_id'], null, null, NOTIFY_BOTH, $proj->prefs['lang_code']);
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

        $task = Flyspray::getTaskDetails($task_id);

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

            $db->query("INSERT INTO  {attachments}
                                     ( task_id, comment_id, file_name,
                                       file_type, file_size, orig_name,
                                       added_by, date_added)
                             VALUES  (?, ?, ?, ?, ?, ?, ?, ?)",
                    array($task_id, $comment_id, $fname,
                        $_FILES[$source]['type'][$key],
                        $_FILES[$source]['size'][$key],
                        $_FILES[$source]['name'][$key],
                        $user->id, time()));
            $attid = $db->insert_ID();
            Flyspray::logEvent($task_id, 7, $attid, $_FILES[$source]['name'][$key]);
        }

        return $res;
    }

    public static function upload_links($task_id, $comment_id = 0, $source = 'userlink')
    {
	    global $db, $user;

	    $task = Flyspray::getTaskDetails($task_id);

	    if (!$user->perms('create_attachments', $task['project_id'])) {
		    return false;
	    }

	    if (!isset($_POST[$source])) {
		    return false;
	    }

	    $res = false;
	    foreach($_POST[$source] as $text) {
			$text = filter_var($text, FILTER_SANITIZE_URL);
		
			if( preg_match( '/^\s*(javascript:|data:)/', $text)){
				continue;
			}
		    
			if(empty($text)) {
				continue;
			}

			$res = true;

		    // Insert into database
		    $db->query("INSERT INTO {links} (task_id, comment_id, url, added_by, date_added) VALUES (?, ?, ?, ?, ?)",
			    array($task_id, $comment_id, $text, $user->id, time()));
                    // TODO: Log event in a later version.
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

        $sql = $db->query(' SELECT t.*, a.*
                              FROM {attachments} a
                         LEFT JOIN {tasks} t ON t.task_id = a.task_id
                             WHERE ' . substr(str_repeat(' attachment_id = ? OR ', count($attachments)), 0, -3),
                          $attachments);

        while ($task = $db->fetchRow($sql)) {
            if (!$user->perms('delete_attachments', $task['project_id'])) {
                continue;
            }

            $db->query('DELETE FROM {attachments} WHERE attachment_id = ?',
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

	    $sql = $db->query('SELECT t.*, l.* FROM {links} l LEFT JOIN {tasks} t ON t.task_id = l.task_id WHERE '.substr(str_repeat('link_id = ? OR ', count($links)), 0, -3), $links);

	    //Delete from database
	    while($task = $db->fetchRow($sql)) {
		    if (!$user->perms('delete_attachments', $task['project_id'])) {
			    continue;
		    }

		    $db->query('DELETE FROM {links} WHERE link_id = ?', array($task['link_id']));
                    // TODO: Log event in a later version.
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

    public static function getAdminAddresses() {
        global $db;

        $emails = array();
        $jabbers = array();
        $onlines = array();
        
        $sql = $db->query('SELECT DISTINCT u.user_id, u.email_address, u.jabber_id,
                                  u.notify_online, u.notify_type, u.notify_own, u.lang_code
                             FROM {users} u
                             JOIN {users_in_groups} ug ON u.user_id = ug.user_id
                             JOIN {groups} g ON g.group_id = ug.group_id
                             WHERE g.is_admin = 1 AND u.account_enabled = 1');
 
	Notifications::assignRecipients($db->fetchAllArray($sql), $emails, $jabbers, $onlines);
        
        return array($emails, $jabbers, $onlines);
    }

    public static function getProjectManagerAddresses($project_id) {
        global $db;
 
        $emails = array();
        $jabbers = array();
        $onlines = array();
        
        $sql = $db->query('SELECT DISTINCT u.user_id, u.email_address, u.jabber_id,
                                  u.notify_online, u.notify_type, u.notify_own, u.lang_code
                             FROM {users} u
                             JOIN {users_in_groups} ug ON u.user_id = ug.user_id
                             JOIN {groups} g ON g.group_id = ug.group_id
                             WHERE g.manage_project = 1 AND g.project_id = ? AND u.account_enabled = 1',
                array($project_id));

	Notifications::assignRecipients($db->fetchAllArray($sql), $emails, $jabbers, $onlines);
        
        return array($emails, $jabbers, $onlines);
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
    public static function create_user($user_name, $password, $real_name, $jabber_id, $email, $notify_type, $time_zone, $group_in, $enabled, $oauth_uid = '', $oauth_provider = '', $profile_image = '')
    {
        global $fs, $db, $notify, $baseurl;

        $user_name = Backend::clean_username($user_name);

    	// TODO Handle this whole create_user better concerning return false. Why did it fail?
		# 'notassigned' and '-1' are possible filtervalues for advanced task search
    	if( empty($user_name) || ctype_digit($user_name) || $user_name == '-1' || $user_name=='notassigned' ) {
    		return false;
    	}

        // Limit length
        $real_name = substr(trim($real_name), 0, 100);
        // Remove doubled up spaces and control chars
        $real_name = preg_replace('![\x00-\x1f\s]+!u', ' ', $real_name);

		# 'notassigned' and '-1' are possible filtervalues for advanced task search, lets avoid them
    	if( ctype_digit($real_name) || $real_name == '-1' || $real_name=='notassigned' ) {
    		return false;
    	}
		
        // Check to see if the username is available
        $sql = $db->query('SELECT COUNT(*) FROM {users} WHERE user_name = ?', array($user_name));

        if ($db->fetchOne($sql)) {
            return false;
        }

        $auto = false;
        // Autogenerate a password
        if (!$password) {
            $auto = true;
            $password = substr(md5(uniqid(mt_rand(), true)), 0, mt_rand(8, 12));
        }

        // Check the emails before inserting anything to database.
        $emailList = explode(';',$email);
        foreach ($emailList as $mail) {	//Still need to do: check email
            $count = $db->query("SELECT COUNT(*) FROM {user_emails} WHERE email_address = ?",array($mail));
            $count = $db->fetchOne($count);
            if ($count > 0) {
                Flyspray::show_error("Email address has alredy been taken");
                return false;
            }
        }
        
        $db->query("INSERT INTO  {users}
                             ( user_name, user_pass, real_name, jabber_id, profile_image, magic_url,
                               email_address, notify_type, account_enabled,
                               tasks_perpage, register_date, time_zone, dateformat,
                               dateformat_extended, oauth_uid, oauth_provider, lang_code)
                     VALUES  ( ?, ?, ?, ?, ?, ?, ?, ?, ?, 25, ?, ?, ?, ?, ?, ?, ?)",
            array($user_name, Flyspray::cryptPassword($password), $real_name, strtolower($jabber_id),
                $profile_image, '', strtolower($email), $notify_type, $enabled, time(), $time_zone, '', '', $oauth_uid, $oauth_provider, $fs->prefs['lang_code']));

        // Get this user's id for the record
        $uid = Flyspray::userNameToId($user_name);

        foreach ($emailList as $mail) {
            if ($mail != '') {
                $db->query("INSERT INTO {user_emails}(id,email_address,oauth_uid,oauth_provider) VALUES (?,?,?,?)",
                        array($uid,strtolower($mail),$oauth_uid, $oauth_provider));
            }
        }

        // Now, create a new record in the users_in_groups table
        $db->query('INSERT INTO  {users_in_groups} (user_id, group_id)
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
        $db->query('INSERT INTO {searches} (user_id, name, search_string, time)
                         VALUES (?, ?, ?, ?)',
                    array($uid, L('taskswatched'), serialize($iwatch), time()));
        $db->query('INSERT INTO {searches} (user_id, name, search_string, time)
                         VALUES (?, ?, ?, ?)',
                    array($uid, L('assignedtome'), serialize($atome), time()));
        $db->query('INSERT INTO {searches} (user_id, name, search_string, time)
                         VALUES (?, ?, ?, ?)',
                    array($uid, L('tasksireported'), serialize($iopened), time()));

        if ($jabber_id) {
            Notifications::jabberRequestAuth($jabber_id);
        }

        // Send a user his details (his username might be altered, password auto-generated)
        // dont send notifications if the user logged in using oauth
        if (!$oauth_provider) {
            $recipients = self::getAdminAddresses();
            $newuser = array();
            
            // Add the right message here depending on $enabled.
            if ($enabled === 0) {
                $newuser[0][$email] = array('recipient' => $email, 'lang' => $fs->prefs['lang_code']);
                
            } else {
                $newuser[0][$email] = array('recipient' => $email, 'lang' => $fs->prefs['lang_code']);
            }

	    if(is_null($notify)) {
		    $notify = new Notifications();
	    }
            // Notify the appropriate users
			if ($fs->prefs['notify_registration']) {
                $notify->create(NOTIFY_NEW_USER, null,
                            array($baseurl, $user_name, $real_name, $email, $jabber_id, $password, $auto),
                            $recipients, NOTIFY_EMAIL);
			}
            // And also the new user
            $notify->create(NOTIFY_OWN_REGISTRATION, null,
                            array($baseurl, $user_name, $real_name, $email, $jabber_id, $password, $auto),
                            $newuser, NOTIFY_EMAIL);
        }

        // If the account is created as not enabled, no matter what any
        // preferences might say or how the registration was made in first
        // place, it MUST be first approved by an admin. And a small
        // work-around: there's no field for email, so we use reason_given
        // for that purpose.
        if ($enabled === 0) {
            Flyspray::adminRequest(3, 0, 0, $uid, $email);
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
		# FIXME Deleting a users effort without asking when user is deleted may not be wanted in every situation.
		# For example for billing a project and the deleted user worked for a project.
		# The better solution is to just deactivate the user, but maybe there are cases a user MUSt be deleted from the database.
		# Move that effort to an 'anonymous users' effort if the effort(s) was legal and should be measured for project(s)?
		foreach ($tables as $table) {
			if (!$db->query('DELETE FROM ' .'{' . $table .'}' . ' WHERE user_id = ?', array($uid))) {
				return false;
			}
		}

		if (!empty($userDetails['profile_image']) && is_file(BASEDIR.'/avatars/'.$userDetails['profile_image'])) {
			unlink(BASEDIR.'/avatars/'.$userDetails['profile_image']);
		}

		$db->query('DELETE FROM {registrations} WHERE email_address = ?',
                        array($userDetails['email_address']));
                
		$db->query('DELETE FROM {user_emails} WHERE id = ?',
                        array($uid));
		
                $db->query('DELETE FROM {reminders} WHERE to_user_id = ? OR from_user_id = ?',
                        array($uid, $uid));

		// for the unusual situuation that a user ID is re-used, make sure that the new user doesn't
		// get permissions for a task automatically
		$db->query('UPDATE {tasks} SET opened_by = 0 WHERE opened_by = ?', array($uid));

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
            $task_ids = $db->query('SELECT task_id FROM {tasks} WHERE project_id = ' . intval($pid));
            $task_ids = $db->fetchCol($task_ids);
            // What was supposed to be in tables field_values, notification_threads
            // and redundant, they do not exist in database?
            $tables = array('admin_requests', 'assigned', 'attachments', 'comments',
                            'dependencies', 'related', 'history',
                            'notifications',
                            'reminders', 'votes');
            foreach ($tables as $table) {
                if ($table == 'related') {
                    $stmt = $db->dblink->prepare('DELETE FROM ' . $db->dbprefix . $table . ' WHERE this_task = ? OR related_task = ? ');
                } else {
                    $stmt = $db->dblink->prepare('DELETE FROM ' . $db->dbprefix . $table . ' WHERE task_id = ?');
                }
                foreach ($task_ids as $id) {
                    $db->dblink->execute($stmt, ($table == 'related') ? array($id, $id) : array($id));
                }
            }
        }

        // unset category of tasks because we don't move categories
        if ($move_to) {
            $db->query('UPDATE {tasks} SET product_category = 0 WHERE project_id = ?', array($pid));
        }

        $tables = array('list_category', 'list_os', 'list_resolution', 'list_tasktype',
                        'list_status', 'list_version', 'admin_requests',
                        'cache', 'projects', 'tasks');

        foreach ($tables as $table) {
            if ($move_to && $table !== 'projects' && $table !== 'list_category') {
                // Having a unique index in most list_* tables prevents
                // doing just a simple update, if the list item already
                // exists in target project, so we have to update existing
                // tasks to use the one in target project. Something similar
                // should be done when moving a single task to another project.
                // Consider making this a separate function that can be used
                // for that purpose too, if possible.
                if (strpos($table, 'list_') === 0) {
                    list($type, $name) = explode('_', $table);
                    $sql = $db->query('SELECT ' . $name . '_id, ' . $name . '_name
                                         FROM {' . $table . '}
                                        WHERE project_id = ?',
                            array($pid));
                    $rows = $db->fetchAllArray($sql);
                    foreach ($rows as $row) {
                        $sql = $db->query('SELECT ' . $name . '_id
                                             FROM {' . $table . '}
                                            WHERE project_id = ? AND '. $name . '_name = ?', 
                                array($move_to, $row[$name .'_name']));
                        $new_id = $db->fetchOne($sql);
                        if ($new_id) {
                            switch ($name) {
                                case 'os';
                                    $column = 'operating_system';
                                    break;
                                case 'resolution';
                                    $column = 'resolution_reason';
                                    break;
                                case 'tasktype';
                                    $column = 'task_type';
                                    break;
                                case 'status';
                                    $column = 'item_status';
                                    break;
                                case 'version';
                                    // Questionable what to do with this one. 1.0 could
                                    // have been still future in the old project and
                                    // already past in the new one...
                                    $column = 'product_version';
                                    break;
                            }
                            if (isset($column)) {
                                $db->query('UPDATE {tasks}
                                               SET ' . $column . ' = ?
                                             WHERE ' . $column . ' = ?',
                                        array($new_id, $row[$name . '_id']));
                                $db->query('DELETE FROM {' . $table . '}
                                             WHERE '  . $name . '_id = ?',
                                        array($row[$name . '_id']));
                            }
                        }
                    }
                }
                $base_sql = 'UPDATE {' . $table . '} SET project_id = ?';
                $sql_params = array($move_to, $pid);
            } else {
                $base_sql = 'DELETE FROM {' . $table . '}';
                $sql_params = array($pid);
            }

            if (!$db->query($base_sql . ' WHERE project_id = ?', $sql_params)) {
                return false;
            }
        }

        // groups are only deleted, not moved (it is likely
        // that the destination project already has all kinds
        // of groups which are also used by the old project)
        $sql = $db->query('SELECT group_id FROM {groups} WHERE project_id = ?', array($pid));
        while ($row = $db->fetchRow($sql)) {
            $db->query('DELETE FROM {users_in_groups} WHERE group_id = ?', array($row['group_id']));
        }
        $sql = $db->query('DELETE FROM {groups} WHERE project_id = ?', array($pid));

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
        $task = Flyspray::getTaskDetails($task_id);

        if (!$user->perms('manage_project', $task['project_id'])) {
            return false;
        }

        if (is_null($user_id)) {
            // Get all users assigned to a task
            $user_id = Flyspray::getAssignees($task_id);
        } else {
            $user_id = array(Flyspray::validUserId($user_id));
            if (!reset($user_id)) {
                return false;
            }
        }

        foreach ($user_id as $id) {
            $sql = $db->replace('{reminders}',
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
        global $conf, $db, $user, $proj, $fs;

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
			# always set detailed_desc even if empty. NULL and a default value set may not work for TEXT/BLOB Mysql8 (but not Mariadb10.2+)
			if (!empty($args[$allowed]) or $allowed === 'detailed_desc') {
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

        // Process estimated effort
        $estimated_effort = 0;
        if ($proj->prefs['use_effort_tracking'] && isset($sql_args['estimated_effort'])) {
            if (($estimated_effort = effort::editStringToSeconds($sql_args['estimated_effort'], $proj->prefs['hours_per_manday'], $proj->prefs['estimated_effort_format'])) === FALSE) {
                Flyspray::show_error(L('invalideffort'));
                $estimated_effort = 0;
            }
            $sql_args['estimated_effort'] = $estimated_effort;
        }

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

	# dokuwiki syntax plugin filters on output
	if ($conf['general']['syntax_plugin'] != 'dokuwiki' && isset($sql_args['detailed_desc'])) {
		$purifierconfig = HTMLPurifier_Config::createDefault();
		$purifierconfig->set('CSS.AllowedProperties', array());
		if ($fs->prefs['relnofollow']) {
			$purifierconfig->set('HTML.Nofollow', true);
		}
		$purifier = new HTMLPurifier($purifierconfig);
		$sql_args['detailed_desc'] = $purifier->purify($sql_args['detailed_desc']);
	}

        // split keys and values into two separate arrays
        $sql_keys   = array();
        $sql_values = array();
        foreach ($sql_args as $key => $value) {
            $sql_keys[]   = $key;
            $sql_values[] = $value;
        }

	/*
         * TODO: At least with PostgreSQL, this has caused the sequence to be
         * out of sync with reality. Must be fixed in upgrade process. Check
         * what's the situation with MySQL. (It's fine, it updates the value even
         * if the column was manually adjusted. Remove this whole block later.)
        $result = $db->query('SELECT  MAX(task_id)+1
                                FROM  {tasks}');
        $task_id = $db->fetchOne($result);
        $task_id = $task_id ? $task_id : 1;
	*/
        //now, $task_id is always the first element of $sql_values
        #array_unshift($sql_keys, 'task_id');
        #array_unshift($sql_values, $task_id);

        $sql_keys_string = join(', ', $sql_keys);
        $sql_placeholder = $db->fill_placeholders($sql_values);

        $result = $db->query("INSERT INTO {tasks}
                                ($sql_keys_string)
                         VALUES ($sql_placeholder)", $sql_values);
	$task_id=$db->insert_ID();
	
	Backend::upload_links($task_id);
	
	// create tags
	if (isset($args['tags'])) {
		$tagList = explode(';', $args['tags']);
		$tagList = array_map('strip_tags', $tagList);
		$tagList = array_map('trim', $tagList);
		$tagList = array_unique($tagList); # avoid duplicates for inputs like: "tag1;tag1" or "tag1; tag1<p></p>"
		foreach ($tagList as $tag){
			if ($tag == ''){
				continue;
			}
			
			# old tag feature
			#$result2 = $db->query("INSERT INTO {tags} (task_id, tag) VALUES (?,?)",array($task_id,$tag));
			
			# new tag feature. let's do it in 2 steps, it is getting too complicated to make it cross database compatible, drawback is possible (rare) race condition (use transaction?)
			$res=$db->query("SELECT tag_id FROM {list_tag} WHERE (project_id=0 OR project_id=?) AND tag_name LIKE ? ORDER BY project_id", array($proj->id,$tag) );
			if($t=$db->fetchRow($res)){   
				$tag_id=$t['tag_id'];
			} else{ 
				if( $proj->prefs['freetagging']==1){
					# add to taglist of the project
					$db->query("INSERT INTO {list_tag} (project_id,tag_name) VALUES (?,?)", array($proj->id,$tag));
					$tag_id=$db->insert_ID();
				} else{
					continue;
				}
			};
			#$db->query("INSERT INTO {task_tag}(task_id,tag_id) VALUES(?,?)", array($task_id, $tag_id) );
			$db->query(
				"INSERT INTO {task_tag} (task_id, tag_id, added, added_by) VALUES(?,?,?,?)",
				array($task_id, $tag_id, time(), intval($user->id))
			);
		}
	}

        // Log the assignments and send notifications to the assignees
        if (isset($args['rassigned_to']) && is_array($args['rassigned_to']))
        {
            // Convert assigned_to and store them in the 'assigned' table
            foreach ($args['rassigned_to'] as $val)
            {
                $db->replace('{assigned}', array('user_id'=> $val, 'task_id'=> $task_id), array('user_id','task_id'));
            }
            // Log to task history
            Flyspray::logEvent($task_id, 14, implode(' ', $args['rassigned_to']));

            // Notify the new assignees what happened.  This obviously won't happen if the task is now assigned to no-one.
            $notify->create(NOTIFY_NEW_ASSIGNEE, $task_id, null, $notify->specificAddresses($args['rassigned_to']), NOTIFY_BOTH, $proj->prefs['lang_code']);
        }

        // Log that the task was opened
        Flyspray::logEvent($task_id, 1);

        $result = $db->query('SELECT  *
                                FROM  {list_category}
                               WHERE  category_id = ?',
                               array($args['product_category']));
        $cat_details = $db->fetchRow($result);

        // We need to figure out who is the category owner for this task
        if (!empty($cat_details['category_owner'])) {
            $owner = $cat_details['category_owner'];
        }
        else {
            // check parent categories
            $result = $db->query('SELECT  *
                                    FROM  {list_category}
                                   WHERE  lft < ? AND rgt > ? AND project_id  = ?
                                ORDER BY  lft DESC',
                                   array($cat_details['lft'], $cat_details['rgt'], $cat_details['project_id']));
            while ($row = $db->fetchRow($result)) {
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
            Backend::add_reminder($task_id, L('defaultreminder') . "\n\n" . createURL('details', $task_id), 2*24*60*60, time());
        }

        // Create the Notification
        if (Backend::upload_files($task_id)) {
            $notify->create(NOTIFY_TASK_OPENED, $task_id, 'files', null, NOTIFY_BOTH, $proj->prefs['lang_code']);
        } else {
            $notify->create(NOTIFY_TASK_OPENED, $task_id, null, null, NOTIFY_BOTH, $proj->prefs['lang_code']);
        }

        // If the reporter wanted to be added to the notification list
        if (isset($args['notifyme']) && $args['notifyme'] == '1' && $user->id != $owner) {
            Backend::add_notification($user->id, $task_id, true);
        }

        if ($user->isAnon()) {
            $anonuser = array();
            $anonuser[] = array('recipient' => $args['anon_email']);
            $recipients = array($anonuser);
            $notify->create(NOTIFY_ANON_TASK, $task_id, $token,
                            $recipients, NOTIFY_EMAIL, $proj->prefs['lang_code']);
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
        global $db, $notify, $user, $proj;
        $task = Flyspray::getTaskDetails($task_id);

        if (!$user->can_close_task($task)) {
            return false;
        }

        if ($task['is_closed']) {
            return false;
        }

        $db->query('UPDATE  {tasks}
                       SET  date_closed = ?, closed_by = ?, closure_comment = ?,
                            is_closed = 1, resolution_reason = ?, last_edited_time = ?,
                            last_edited_by = ?
                     WHERE  task_id = ?',
                    array(time(), $user->id, $comment, $reason, time(), $user->id, $task_id));

        if ($mark100) {
            $db->query('UPDATE {tasks} SET percent_complete = 100 WHERE task_id = ?',
                       array($task_id));

            Flyspray::logEvent($task_id, 3, 100, $task['percent_complete'], 'percent_complete');
        }

        $notify->create(NOTIFY_TASK_CLOSED, $task_id, null, null, NOTIFY_BOTH, $proj->prefs['lang_code']);
        Flyspray::logEvent($task_id, 2, $reason, $comment);

        // If there's an admin request related to this, close it
        $db->query('UPDATE  {admin_requests}
                       SET  resolved_by = ?, time_resolved = ?
                     WHERE  task_id = ? AND request_type = ?',
                    array($user->id, time(), $task_id, 1));

        // duplicate
        if ($reason == RESOLUTION_DUPLICATE) {
            preg_match("/\b(?:FS#|bug )(\d+)\b/", $comment, $dupe_of);
            if (count($dupe_of) >= 2) {
                $existing = $db->query('SELECT * FROM {related} WHERE this_task = ? AND related_task = ? AND is_duplicate = 1',
                                        array($task_id, $dupe_of[1]));

                if ($existing && $db->countRows($existing) == 0) {
                    $db->query('INSERT INTO {related} (this_task, related_task, is_duplicate) VALUES(?, ?, 1)',
                                array($task_id, $dupe_of[1]));
                }
                Backend::add_vote($task['opened_by'], $dupe_of[1]);
            }
        }

        return true;
    }

	/**
	 * @param array $p usually $_POST by modify.inc.php
	 *
	 * @todo test and real use
	*/
	public static function updateTasks($p)
	{
		global $db;		

		$task_ids=filter_var($p['ids'], FILTER_VALIDATE_INT, FILTER_FORCE_ARRAY);
		if (!$task_ids){
			return false;
		}
		
		$columns = array();
		$values = array();

		// determine the task properties that should been changed
		$status=filter_var($p['bulk_status'], FILTER_VALIDATE_INT, FILTER_REQUIRE_SCALAR);
		if ($status > 0 ){
			$columns[] = 'item_status';
			$values[] = $status;
		}

		if (isset($p['bulk_percent_complete'])) {
			$percentcomplete=filter_var($p['bulk_percent_complete'], FILTER_VALIDATE_INT, FILTER_REQUIRE_SCALAR);
			if ($percentcomplete !='' && $percentcomplete >= 0){
				$columns[] = 'percent_complete';
				$values[] = $percentcomplete;
			}
		}

		$tasktype=filter_var($p['bulk_task_type'], FILTER_VALIDATE_INT, FILTER_REQUIRE_SCALAR);
		if ($tasktype > 0){
			$columns[] = 'task_type';
			$values[] = $tasktype;
		}

		$category=filter_var($p['bulk_category'], FILTER_VALIDATE_INT, FILTER_REQUIRE_SCALAR);
		if ($category > 0){
			$columns[] = 'product_category';
			$values[] = $category;
		}

		$os=filter_var($p['bulk_os'], FILTER_VALIDATE_INT, FILTER_REQUIRE_SCALAR);
		if ($os > 0){
			$columns[] = 'operating_system';
			$values[] = $os;
		}

		$severity=filter_var($p['bulk_severity'], FILTER_VALIDATE_INT, FILTER_REQUIRE_SCALAR);
		if ($severity  > 0){
			$columns[] = 'task_severity';
			$values[] = $severity;
		}

		$priority=filter_var($p['bulk_priority'], FILTER_VALIDATE_INT, FILTER_REQUIRE_SCALAR);
		if ($priority>0){
			$columns[] = 'task_priority';
			$values[] = $priority;
		}

		$reportedver=filter_var($p['bulk_reportedver'], FILTER_VALIDATE_INT, FILTER_REQUIRE_SCALAR);
		if ($reportedver > 0){
			$columns[] = 'product_version';
			$values[] = $reportedver;
		}

		$dueversion=filter_var($p['bulk_due_version'], FILTER_VALIDATE_INT, FILTER_REQUIRE_SCALAR);
		if ($dueversion > 0){
			$columns[] = 'closedby_version';
			$values[] = $dueversion;
		}

		# TODO Does the user has similiar rights in current and target projects?
		# TODO Does a task has subtasks? What happens to them? What if they are open/closed?
		# But: Allowing task dependencies between tasks in different projects is a feature!
		if ($targetproject=filter_var($p['bulk_projects'], FILTER_VALIDATE_INT, FILTER_REQUIRE_SCALAR) > 0){
			array_push($columns, 'project_id');
			array_push($values, $targetproject);
		}

		# TODO: empty text =>no change, 0 =>unset duedate?
		if ($p['bulk_due_date']==='0'){
			$columns[] = 'due_date';
			$values[] = 0;
		} elseif ($p['bulk_due_date']){
			$columns[] = 'due_date';
			$values[] = Flyspray::strtotime($p['bulk_due_date']);
		}

		$affectedtasks=0;
		// only process if at least one of the task fields should be changed
		if (count($columns)>0){

			$valuesAndTasks = array_merge_recursive($values, $task_ids);

			// execute the database update on all selected queries
			$update = $db->query("UPDATE {tasks}
				SET  ".join('=?, ', $columns)."=?
				WHERE". substr(str_repeat(' task_id = ? OR ', count($task_ids)), 0, -3), $valuesAndTasks);
			$affectedtasks=$db->affectedRows();
		}

		$affecteddeleteassigned=0;
		$affectedaddassigned=0;

		// assignments
		if (isset($p['bulk_assignment'])){
			$assigned_ids = filter_var($p['bulk_assignment'], FILTER_VALIDATE_INT, FILTER_FORCE_ARRAY);

			// Delete the current assignees for the selected tasks
			$db->query("DELETE FROM {assigned} WHERE". substr(str_repeat(' task_id = ? OR ', count($task_ids)), 0, -3), $task_ids);	
			$affecteddeleteassigned=$db->affectedRows();

			// Convert assigned_to and store them in the 'assigned' table
			foreach ($task_ids as $id){
				// iterate the users that are selected on the user list.
				foreach ($assigned_ids as $assignee){
					// if 'noone' has been selected then dont do the database update.
					if ($assignee > 0){
						// insert the task and user id's into the assigned table.
						$db->query('
							INSERT INTO {assigned} (task_id, user_id)
							VALUES (?, ?)', 
							array($id,$assignee));
						$affectedaddassigned+=$db->affectedRows();
					}
				}
			}
		}

		// set success message
		$_SESSION['SUCCESS'] = L('tasksupdated').' tasks updated:'.$affectedtasks.' delass:'.$affecteddeleteassigned.' addass:'.$affectedaddassigned;
		#$_SESSION['SUCCESS'] .= print_r($columns, true);

		return true;
	}

    /**
     * Returns an array of tasks (respecting pagination) and an ID list (all tasks)
     *
     * @param array $args search/filter values
     * @param array $visible task fields/properties to be fetched from database
     * @param integer $offset for paginated results
     * @param bool $perpage for paginated results
     *
     * @access public
     * @return array
     * @version 1.0
     */
    public static function get_task_list($args, $visible, $offset = 0, $perpage = 20) {
        global $fs, $proj, $db, $user, $conf;
        /* build SQL statement {{{ */
        // Original SQL courtesy of Lance Conry http://www.rhinosw.com/
        $where = $sql_params = array();

        // echo '<pre>' . print_r($visible, true) . '</pre>';
        // echo '<pre>' . print_r($args, true) . '</pre>';
        // PostgreSQL LIKE searches are by default case sensitive,
        // so we use ILIKE instead. For other databases, in our case
        // only MySQL/MariaDB, LIKE is good for our purposes.
        $LIKEOP = 'LIKE';
        if ($db->dblink->dataProvider == 'postgres') {
            $LIKEOP = 'ILIKE';
        }

        $select = '';
        $groupby = 't.task_id, ';
        $cgroupbyarr = array();

        // Joins absolutely needed for user viewing rights
        $from = ' {tasks} t
-- All tasks have a project!
JOIN {projects} p ON t.project_id = p.project_id';
	
	// Not needed for anonymous users
        if (!$user->isAnon()) {
$from .= ' -- Global group always exists
JOIN ({groups} gpg
    JOIN {users_in_groups} gpuig ON gpg.group_id = gpuig.group_id AND gpuig.user_id = ?		
) ON gpg.project_id = 0
-- Project group might exist or not.
LEFT JOIN ({groups} pg
    JOIN {users_in_groups} puig ON pg.group_id = puig.group_id AND puig.user_id = ?	
) ON pg.project_id = t.project_id';
	    $sql_params[] = $user->id;
	    $sql_params[] = $user->id;
	}
	
	// Keep this always, could also used for showing assigned users for a task.
	// Keeps the overall logic somewhat simpler.
	$from .= ' LEFT JOIN {assigned} ass ON t.task_id = ass.task_id';
	$from .= ' LEFT JOIN {task_tag} tt ON t.task_id = tt.task_id';
	$from .= ' LEFT JOIN {list_tag} flt ON tt.tag_id = flt.tag_id';
        $cfrom = $from;
        
        // Seems resution name really is needed...
        $select .= 'lr.resolution_name, ';
        $from .= ' LEFT JOIN {list_resolution} lr ON t.resolution_reason = lr.resolution_id ';
        $groupby .= 'lr.resolution_name, ';
        
        // Otherwise, only join tables which are really necessary to speed up the db-query
        if (array_get($args, 'type') || in_array('tasktype', $visible)) {
            $select .= ' lt.tasktype_name, ';
            $from .= '
LEFT JOIN {list_tasktype} lt ON t.task_type = lt.tasktype_id ';
            $groupby .= ' lt.tasktype_id, ';
        }        

        if (array_get($args, 'status') || in_array('status', $visible)) {
            $select .= ' lst.status_name, ';
            $from .= '
LEFT JOIN {list_status} lst ON t.item_status = lst.status_id ';
            $groupby .= ' lst.status_id, ';
        }

        if (array_get($args, 'cat') || in_array('category', $visible)) {
            $select .= ' lc.category_name AS category_name, ';
            $from .= '
LEFT JOIN {list_category} lc ON t.product_category = lc.category_id ';
            $groupby .= 'lc.category_id, ';
        }

        if (in_array('votes', $visible)) {
            $select .= ' (SELECT COUNT(vot.vote_id) FROM {votes} vot WHERE vot.task_id = t.task_id) AS num_votes, ';
        }

        $maxdatesql = ' GREATEST(COALESCE((SELECT max(c.date_added) FROM {comments} c WHERE c.task_id = t.task_id), 0), t.date_opened, t.date_closed, t.last_edited_time) ';
        $search_for_changes = in_array('lastedit', $visible) || array_get($args, 'changedto') || array_get($args, 'changedfrom');
        if ($search_for_changes) {
            $select .= ' GREATEST(COALESCE((SELECT max(c.date_added) FROM {comments} c WHERE c.task_id = t.task_id), 0), t.date_opened, t.date_closed, t.last_edited_time) AS max_date, ';
            $cgroupbyarr[] = 't.task_id';
        }

        if (array_get($args, 'search_in_comments')) {
            $from .= '
LEFT JOIN {comments} c ON t.task_id = c.task_id ';
            $cfrom .= '
LEFT JOIN {comments} c ON t.task_id = c.task_id ';
            $cgroupbyarr[] = 't.task_id';
        }

        if (in_array('comments', $visible)) {
            $select .= ' (SELECT COUNT(cc.comment_id) FROM {comments} cc WHERE cc.task_id = t.task_id) AS num_comments, ';
        }

        if (in_array('reportedin', $visible)) {
            $select .= ' lv.version_name AS product_version_name, ';
            $from .= '
LEFT JOIN {list_version} lv ON t.product_version = lv.version_id ';
            $groupby .= 'lv.version_id, ';
        }

        if (array_get($args, 'opened') || in_array('openedby', $visible)) {
            $select .= ' uo.real_name AS opened_by_name, ';
            $from .= '
LEFT JOIN {users} uo ON t.opened_by = uo.user_id ';
            $groupby .= 'uo.user_id, ';
            if (array_get($args, 'opened')) {
                $cfrom .= '
LEFT JOIN {users} uo ON t.opened_by = uo.user_id ';
            }
        }

        if (array_get($args, 'closed')) {
            $select .= ' uc.real_name AS closed_by_name, ';
            $from .= '
LEFT JOIN {users} uc ON t.closed_by = uc.user_id ';
            $groupby .= 'uc.user_id, ';
            $cfrom .= '
LEFT JOIN {users} uc ON t.closed_by = uc.user_id ';
        }

        if (array_get($args, 'due') || in_array('dueversion', $visible)) {
            $select .= ' lvc.version_name AS closedby_version_name, ';
            $from .= '
LEFT JOIN {list_version} lvc ON t.closedby_version = lvc.version_id ';
            $groupby .= 'lvc.version_id, lvc.list_position, ';
        }

        if (in_array('os', $visible)) {
            $select .= ' los.os_name AS os_name, ';
            $from .= '
LEFT JOIN {list_os} los ON t.operating_system = los.os_id ';
            $groupby .= 'los.os_id, ';
        }

        if (in_array('attachments', $visible)) {
            $select .= ' (SELECT COUNT(attc.attachment_id) FROM {attachments} attc WHERE attc.task_id = t.task_id) AS num_attachments, ';
        }

        if (array_get($args, 'has_attachment')) {
            $where[] = 'EXISTS (SELECT 1 FROM {attachments} att WHERE t.task_id = att.task_id)';
        }
        # 20150213 currently without recursive subtasks!
        if (in_array('effort', $visible)) {
            $select .= ' (SELECT SUM(ef.effort) FROM {effort} ef WHERE t.task_id = ef.task_id) AS effort, ';
        }

	if (array_get($args, 'dev') || in_array('assignedto', $visible)) {
		# not every db system has this feature out of box
		if($conf['database']['dbtype']=='mysqli' || $conf['database']['dbtype']=='mysql'){
			$select .= ' GROUP_CONCAT(DISTINCT u.user_name ORDER BY u.user_id) AS assigned_to_name, ';
			$select .= ' GROUP_CONCAT(DISTINCT u.user_id ORDER BY u.user_id) AS assignedids, ';
			$select .= ' GROUP_CONCAT(DISTINCT u.profile_image ORDER BY u.user_id) AS assigned_image, ';
		} elseif( $conf['database']['dbtype']=='pgsql'){
			$select .= " array_to_string(array_agg(u.user_name ORDER BY u.user_id), ',') AS assigned_to_name, ";
			$select .= " array_to_string(array_agg(CAST(u.user_id as text) ORDER BY u.user_id), ',') AS assignedids, ";
                        $select .= " array_to_string(array_agg(u.profile_image ORDER BY u.user_id), ',') AS assigned_image, ";
		} else{
			$select .= ' MIN(u.user_name) AS assigned_to_name, ';
			$select .= ' (SELECT COUNT(assc.user_id) FROM {assigned} assc WHERE assc.task_id = t.task_id) AS num_assigned, ';
		}
		// assigned table is now always included in join
		$from .= '
LEFT JOIN {users} u ON ass.user_id = u.user_id ';
		$groupby .= 'ass.task_id, ';
		if (array_get($args, 'dev')) {
			$cfrom .= '
LEFT JOIN {users} u ON ass.user_id = u.user_id ';
			$cgroupbyarr[] = 't.task_id';
			$cgroupbyarr[] = 'ass.task_id';
		}
	}
        
	# not every db system has this feature out of box, it is not standard sql
	if($conf['database']['dbtype']=='mysqli' || $conf['database']['dbtype']=='mysql'){
		#$select .= ' GROUP_CONCAT(DISTINCT tg.tag_name ORDER BY tg.list_position) AS tags, ';
		$select .= ' GROUP_CONCAT(DISTINCT tg.tag_id ORDER BY tg.list_position) AS tagids, ';
		#$select .= ' GROUP_CONCAT(DISTINCT tg.class ORDER BY tg.list_position) AS tagclass, ';
	} elseif($conf['database']['dbtype']=='pgsql'){
		#$select .= " array_to_string(array_agg(tg.tag_name ORDER BY tg.list_position), ',') AS tags, ";
		$select .= " array_to_string(array_agg(CAST(tg.tag_id as text) ORDER BY tg.list_position), ',') AS tagids, ";
		#$select .= " array_to_string(array_agg(tg.class ORDER BY tg.list_position), ',') AS tagclass, ";
	} else{
		# unsupported groupconcat or we just do not know how write it for the other databasetypes in this section 
		#$select .= ' MIN(tg.tag_name) AS tags, ';
		#$select .= ' (SELECT COUNT(tt.tag_id) FROM {task_tag} tt WHERE tt.task_id = t.task_id)  AS tagnum, ';
		$select .= ' MIN(tg.tag_id) AS tagids, ';
		#$select .= " '' AS tagclass, ";
	}
	// task_tag join table is now always included in join
	$from .= '
LEFT JOIN {list_tag} tg ON tt.tag_id = tg.tag_id ';
	$groupby .= 'tt.task_id, ';
	$cfrom .= '
LEFT JOIN {list_tag} tg ON tt.tag_id = tg.tag_id ';
	$cgroupbyarr[] = 't.task_id';
	$cgroupbyarr[] = 'tt.task_id';


	# use preparsed task description cache for dokuwiki when possible
	if($conf['general']['syntax_plugin']=='dokuwiki' && FLYSPRAY_USE_CACHE==true){
		$select.=' MIN(cache.content) desccache, ';
		$from.='
LEFT JOIN {cache} cache ON t.task_id=cache.topic AND cache.type=\'task\' ';
	} else {
            $select .= 'NULL AS desccache, ';
        }

        if (array_get($args, 'only_primary')) {
            $where[] = 'NOT EXISTS (SELECT 1 FROM {dependencies} dep WHERE dep.dep_task_id = t.task_id)';
        }
        
        # feature FS#1600
        if (array_get($args, 'only_blocker')) {
            $where[] = 'EXISTS (SELECT 1 FROM {dependencies} dep WHERE dep.dep_task_id = t.task_id)';
        }
        
        if (array_get($args, 'only_blocked')) {
            $where[] = 'EXISTS (SELECT 1 FROM {dependencies} dep WHERE dep.task_id = t.task_id)';
        }
        
        # feature FS#1599
        if (array_get($args, 'only_unblocked')) {
            $where[] = 'NOT EXISTS (SELECT 1 FROM {dependencies} dep WHERE dep.task_id = t.task_id)';
        }

        if (array_get($args, 'hide_subtasks')) {
            $where[] = 't.supertask_id = 0';
        }

        if (array_get($args, 'only_watched')) {
            $where[] = 'EXISTS (SELECT 1 FROM {notifications} fsn WHERE t.task_id = fsn.task_id AND fsn.user_id = ?)';
            $sql_params[] = $user->id;
        }

        if ($proj->id) {
            $where[] = 't.project_id = ?';
            $sql_params[] = $proj->id;
        } else {
            if (!$user->isAnon()) { // Anon-case handled later.
                $allowed = array();
                foreach($fs->projects as $p) {
                    $allowed[] = $p['project_id'];
                }
		if(count($allowed)>0){
			$where[] = 't.project_id IN (' . implode(',', $allowed). ')';
		}else{
			$where[] = '0 = 1'; # always empty result
		}
            }
        }

        // process users viewing rights, if not anonymous
        if (!$user->isAnon()) {
 $where[] = '
(   -- Begin block where users viewing rights are checked.
    -- Case everyone can see all project tasks anyway and task not private
    (t.mark_private = 0 AND p.others_view = 1)
    OR
    -- Case admin or project manager, can see any task, even private
    (gpg.is_admin = 1 OR gpg.manage_project = 1 OR pg.is_admin = 1 OR pg.manage_project = 1)
    OR
    -- Case allowed to see all tasks, but not private
    ((gpg.view_tasks = 1 OR pg.view_tasks = 1) AND t.mark_private = 0)
    OR
    -- Case allowed to see own tasks (automatically covers private tasks also for this user!)
    ((gpg.view_own_tasks = 1 OR pg.view_own_tasks = 1) AND (t.opened_by = ? OR ass.user_id = ?))
    OR
    -- Case task is private, but user either opened it or is an assignee
    (t.mark_private = 1 AND (t.opened_by = ? OR ass.user_id = ?))
    OR
    -- Leave groups tasks as the last one to check. They are the only ones that actually need doing a subquery
    -- for checking viewing rights. There\'s a chance that a previous check already matched and the subquery is
    -- not executed at all. All this of course depending on how the database query optimizer actually chooses
    -- to fetch the results and execute this query... At least it has been given the hint.

    -- Case allowed to see groups tasks, all projects (NOTE: both global and project specific groups accepted here)
    -- Strange... do not use OR here with user_id in EXISTS clause, seems to prevent using index with both mysql and
    -- postgresql, query times go up a lot. So it\'ll be 2 different EXISTS OR\'ed together.
    (gpg.view_groups_tasks = 1 AND t.mark_private = 0 AND (
	EXISTS (SELECT 1 FROM {users_in_groups} WHERE (group_id = pg.group_id OR group_id = gpg.group_id) AND user_id = t.opened_by)
	OR
	EXISTS (SELECT 1 FROM {users_in_groups} WHERE (group_id = pg.group_id OR group_id = gpg.group_id) AND user_id = ass.user_id)
    ))
    OR
    -- Case allowed to see groups tasks, current project. Only project group allowed here.
    (pg.view_groups_tasks = 1 AND t.mark_private = 0 AND (
	EXISTS (SELECT 1 FROM {users_in_groups} WHERE group_id = pg.group_id AND user_id = t.opened_by)
	OR
	EXISTS (SELECT 1 FROM {users_in_groups} WHERE group_id = pg.group_id AND user_id = ass.user_id)
    ))
)   -- Rights have been checked 
';
        $sql_params[] = $user->id;
        $sql_params[] = $user->id;
        $sql_params[] = $user->id;
        $sql_params[] = $user->id;
	}
        /// process search-conditions {{{
        $submits = array('type' => 'task_type', 'sev' => 'task_severity',
            'due' => 'closedby_version', 'reported' => 'product_version',
            'cat' => 'product_category', 'status' => 'item_status',
            'percent' => 'percent_complete', 'pri' => 'task_priority',
            'dev' => array('ass.user_id', 'u.user_name', 'u.real_name'),
            'opened' => array('opened_by', 'uo.user_name', 'uo.real_name'),
            'closed' => array('closed_by', 'uc.user_name', 'uc.real_name'));
        foreach ($submits as $key => $db_key) {
            $type = array_get($args, $key, ($key == 'status') ? 'open' : '');
            settype($type, 'array');

            if (in_array('', $type)) {
                continue;
            }

            $temp = '';
            $condition = '';
            foreach ($type as $val) {
                // add conditions for the status selection
                if ($key == 'status' && $val == 'closed' && !in_array('open', $type)) {
                    $temp .= ' is_closed = 1 AND';
                } elseif ($key == 'status' && !in_array('closed', $type)) {
                    $temp .= ' is_closed = 0 AND';
                }
                if (is_numeric($val) && !is_array($db_key) && !($key == 'status' && $val == 'closed')) {
                    $temp .= ' ' . $db_key . ' = ?  OR';
                    $sql_params[] = $val;
                } elseif (is_array($db_key)) {
                    if ($key == 'dev' && ($val == 'notassigned' || $val == '0' || $val == '-1')) {
                        $temp .= ' ass.user_id is NULL  OR';
                    } else {
                        foreach ($db_key as $singleDBKey) {
                            if(ctype_digit($val) && strpos($singleDBKey, '_name') === false) {
                                $temp .= ' ' . $singleDBKey . ' = ?  OR';
                                $sql_params[] = $val;
                            } elseif (!ctype_digit($val) && strpos($singleDBKey, '_name') !== false) {
                                $temp .= ' ' . $singleDBKey . " $LIKEOP ?  OR";
                                $sql_params[] = '%' . $val . '%';
                            }
                        }
                    }
                }

                // Add the subcategories to the query
                if ($key == 'cat') {
                    $result = $db->query('SELECT *
                                            FROM {list_category}
                                           WHERE category_id = ?', array($val));
                    $cat_details = $db->fetchRow($result);

                    $result = $db->query('SELECT *
                                            FROM {list_category}
                                           WHERE lft > ? AND rgt < ? AND project_id  = ?', array($cat_details['lft'], $cat_details['rgt'], $cat_details['project_id']));
                    while ($row = $db->fetchRow($result)) {
                        $temp .= ' product_category = ?  OR';
                        $sql_params[] = $row['category_id'];
                    }
                }
            }

            if ($temp) {
                $where[] = '(' . substr($temp, 0, -3) . ')'; # strip last ' OR' and 'AND'
            }
        }
/// }}}

        $order_keys = array(
            'id' => 't.task_id',
            'project' => 'project_title',
            'tasktype' => 'tasktype_name',
            'dateopened' => 'date_opened',
            'summary' => 'item_summary',
            'severity' => 'task_severity',
            'category' => 'lc.category_name',
            'status' => 'is_closed, item_status',
            'dueversion' => 'lvc.list_position',
            'duedate' => 'due_date',
            'progress' => 'percent_complete',
            'lastedit' => 'max_date',
            'priority' => 'task_priority',
            'openedby' => 'uo.real_name',
            'reportedin' => 't.product_version',
            'assignedto' => 'u.real_name',
            'dateclosed' => 't.date_closed',
            'os' => 'los.os_name',
            'votes' => 'num_votes',
            'attachments' => 'num_attachments',
            'comments' => 'num_comments',
            'private' => 'mark_private',
            'supertask' => 't.supertask_id',
	    'effort' => 'effort',
            'estimatedeffort' => 'estimated_effort'
        );

        // make sure that only columns can be sorted that are visible (and task severity, since it is always loaded)
        $order_keys = array_intersect_key($order_keys, array_merge(array_flip($visible), array('severity' => 'task_severity')));

        // Implementing setting "Default order by"
        if (!array_key_exists('order', $args)) {
        	# now also for $proj->id=0 (allprojects)
                $orderBy = $proj->prefs['sorting'][0]['field'];
                $sort =    $proj->prefs['sorting'][0]['dir'];
                if (count($proj->prefs['sorting']) >1){
                        $orderBy2 =$proj->prefs['sorting'][1]['field'];
                        $sort2=    $proj->prefs['sorting'][1]['dir'];
                } else{
                        $orderBy2='severity';
                        $sort2='DESC';
                }
        } else {
            $orderBy = $args['order'];
            $sort = $args['sort'];
            $orderBy2='severity';
            $sort2='desc';
        }

        // TODO: Fix this! If something is already ordered by task_id, there's
        // absolutely no use to even try to order by something else also. 
        $order_column[0] = $order_keys[Filters::enum(array_get($args, 'order', $orderBy), array_keys($order_keys))];
        $order_column[1] = $order_keys[Filters::enum(array_get($args, 'order2', $orderBy2), array_keys($order_keys))];
        $sortorder = sprintf('%s %s, %s %s, t.task_id ASC',
        	$order_column[0],
        	Filters::enum(array_get($args, 'sort', $sort), array('asc', 'desc')),
        	$order_column[1],
        	Filters::enum(array_get($args, 'sort2', $sort2), array('asc', 'desc'))
        );

        $having = array();
        $dates = array('duedate' => 'due_date', 'changed' => $maxdatesql,
            'opened' => 'date_opened', 'closed' => 'date_closed');
        foreach ($dates as $post => $db_key) {
            $var = ($post == 'changed') ? 'having' : 'where';
            if ($date = array_get($args, $post . 'from')) {
                ${$var}[] = '(' . $db_key . ' >= ' . Flyspray::strtotime($date) . ')';
            }
            if ($date = array_get($args, $post . 'to')) {
                ${$var}[] = '(' . $db_key . ' <= ' . Flyspray::strtotime($date) . ' AND ' . $db_key . ' > 0)';
            }
        }

		if (array_get($args, 'string') && !is_array(array_get($args, 'string'))) {
			$words = explode(' ', strtr(array_get($args, 'string'), '()', '  '));
			$comments = '';
			$where_temp = array();

			if (array_get($args, 'search_in_comments')) {
				$comments .= " OR c.comment_text $LIKEOP ?";
			}
			if (array_get($args, 'search_in_details')) {
				$comments .= " OR t.detailed_desc $LIKEOP ?";
			}

			foreach ($words as $word) {
				$word = trim($word);
				if ($word==''){
					continue;
				}

				# The 2006-2020 hidden/undocumented '+' search feature: 'open+source' finds 'open source', but not 'open closed source'
				if (substr($word, -1) === '+') {
					# do not replace the + at end of a word like archlinux package name 'memtest86+'
					$likeWord = '%' . trim(str_replace('+', ' ', substr($word, 0, -1))) . '+%';
				} else {
					$likeWord = '%' . trim(str_replace('+', ' ', $word)) . '%';
				}
				
				$where_temp[] = "(t.item_summary $LIKEOP ? OR t.task_id = ? OR flt.tag_name $LIKEOP ? $comments)";
				array_push($sql_params, $likeWord, intval($word), $likeWord);
				if (array_get($args, 'search_in_comments')) {
					array_push($sql_params, $likeWord);
				}
				if (array_get($args, 'search_in_details')) {
					array_push($sql_params, $likeWord);
				}
			}

			if(count($where_temp)>0){
				$where[] = '(' . implode((array_get($args, 'search_for_all') ? ' AND ' : ' OR '), $where_temp) . ')';
			}
		}

		if ($user->isAnon()) {
			$where[] = 't.mark_private = 0 AND p.others_view = 1';
			if(array_key_exists('status', $args)){
				if (in_array('closed', $args['status']) && !in_array('open', $args['status'])) {
					$where[] = 't.is_closed = 1';
				} elseif (in_array('open', $args['status']) && !in_array('closed', $args['status'])) {
					$where[] = 't.is_closed = 0';
				}
			}
		}

		$where = (count($where)) ? 'WHERE ' . join(' AND ', $where) : '';

        // Get the column names of table tasks for the group by statement
        if (!strcasecmp($conf['database']['dbtype'], 'pgsql')) {
            $groupby .= "p.project_title, p.project_is_active, ";
            // Remove this after checking old PostgreSQL docs.
            // 1 column from task table should be enough, after
            // already grouping by task_id, there's no possibility
            // to have anything more in that table to group by.
            $groupby .= $db->getColumnNames('{tasks}', 't.task_id', 't.');
        } else {
            $groupby = 't.task_id';
        }

        $having = (count($having)) ? 'HAVING ' . join(' AND ', $having) : '';
        
        // echo '<pre>' . print_r($args, true) . '</pre>';
        // echo '<pre>' . print_r($cgroupbyarr, true) . '</pre>';
        $cgroupby = count($cgroupbyarr) ? 'GROUP BY ' . implode(',', array_unique($cgroupbyarr)) : '';

        $sqlcount = "SELECT COUNT(*) FROM (SELECT 1, t.task_id, t.date_opened, t.date_closed, t.last_edited_time
                           FROM $cfrom
                           $where
                           $cgroupby
                           $having) s";
        $sqltext = "SELECT t.*, $select
p.project_title, p.project_is_active
FROM $from
$where
GROUP BY $groupby
$having
ORDER BY $sortorder";

        // Very effective alternative with a little bit more work
        // and if row_number() can be emulated in mysql. Idea:
        // Move every join and other operation not needed in
        // the inner clause to select rows to the outer query,
        // and do the rest when we already know which rows
        // are in the window to show. Got it to run constantly
        // under 6000 ms.
        /* Leave this for next version, don't have enough time for testing.
        $sqlexperiment = "SELECT * FROM (
SELECT row_number() OVER(ORDER BY task_id) AS rownum,
t.*, $select p.project_title, p.project_is_active FROM $from
$where
GROUP BY $groupby
$having
ORDER BY $sortorder
)
t WHERE rownum BETWEEN $offset AND " . ($offset + $perpage);
*/

	//echo '<pre>'.print_r($sql_params, true).'</pre>'; # for debugging 
	//echo '<pre>'.$sqlcount.'</pre>'; # for debugging 
	//echo '<pre>'.$sqltext.'</pre>'; # for debugging 
        $sql = $db->query($sqlcount, $sql_params);
        $totalcount = $db->fetchOne($sql);

	# 20150313 peterdd: Do not override task_type with tasktype_name until we changed t.task_type to t.task_type_id! We need the id too.

        $sql = $db->query($sqltext, $sql_params, $perpage, $offset);
        //$sql = $db->query($sqlexperiment, $sql_params);
        $tasks = $db->fetchAllArray($sql);
        $id_list = array();
        $limit = array_get($args, 'limit', -1);
        $forbidden_tasks_count = 0;
        foreach ($tasks as $key => $task) {
            $id_list[] = $task['task_id'];
            if (!$user->can_view_task($task)) {
                unset($tasks[$key]);
                $forbidden_tasks_count++;
            }
        }

	// Work on this is not finished until $forbidden_tasks_count is always zero.
	// echo "<pre>$offset : $perpage : $totalcount : $forbidden_tasks_count</pre>";
        return array($tasks, $id_list, $totalcount, $forbidden_tasks_count);
	// # end alternative
    } # end get_task_list
} # end class
