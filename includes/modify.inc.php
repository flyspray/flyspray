<?php

  /*************************\
  | database modifications  |
  | ~~~~~~~~~~~~~~~~~~~~~~  |
  \*************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

// Include the notifications class
require_once BASEDIR . '/includes/class.notify.php';
require_once BASEDIR . '/includes/class.jabber2.php';
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

if (Req::num('task_id')) {
    $task = Flyspray::GetTaskDetails(Req::num('task_id'));
}

unset($_SESSION['SUCCESS'], $_SESSION['ERROR']);

switch ($action = Req::val('action'))
{
    // ##################
    // Adding a new task
    // ##################
    case 'newtask.newtask':
        if (!Post::val('item_summary') || !Post::val('detailed_desc')) {
            Flyspray::show_error(L('summaryanddetails'));
            break;
        }
        
        $task_id = Backend::create_task($_POST);

        // Status and redirect
        if ($task_id) {
            $_SESSION['SUCCESS'] = L('newtaskadded');
            
            if ($user->isAnon()) {        
                Flyspray::Redirect(CreateURL('details', $task_id, null, array('task_token' => Get::val('task_token'))));
            } else {
                Flyspray::Redirect(CreateURL('details', $task_id));
            }
        } else {
            Flyspray::show_error(L('databasemodfailed'));
            break;
        }
        break;

    // ##################
    // Modifying an existing task
    // ##################
    case 'details.update':
        if (!$user->can_edit_task($task)) {
            break;
        }
        
        if (!Post::val('item_summary') || !Post::val('detailed_desc')) {
            Flyspray::show_error(L('summaryanddetails'));
            break;
        }


        if ($due_date = Post::val('due_date', 0)) {
            $due_date = Flyspray::strtotime(Post::val('due_date'));
            Backend::add_reminder($task['task_id'], L('defaultreminder') . "\n\n" . CreateURL('details', $task['task_id']), 2*24*60*60, time());
        }

        $time = time();

        $db->Query('UPDATE  {tasks}
                       SET  project_id = ?, task_type = ?, item_summary = ?,
                            detailed_desc = ?, item_status = ?, mark_private = ?,
                            product_category = ?, closedby_version = ?, operating_system = ?,
                            task_severity = ?, task_priority = ?, last_edited_by = ?,
                            last_edited_time = ?, due_date = ?, percent_complete = ?, product_version = ?
                     WHERE  task_id = ?',
                array($proj->id, Post::val('task_type'),
                    Post::val('item_summary'), Post::val('detailed_desc'),
                    Post::val('item_status'), intval($user->can_change_private($task) && Post::val('mark_private')),
                    Post::val('product_category'), Post::val('closedby_version', 0),
                    Post::val('operating_system'), Post::val('task_severity'),
                    Post::val('task_priority'), intval($user->id), $time, intval($due_date),
                    Post::val('percent_complete'), Post::val('reportedver'),
                    $task['task_id']));

        // Update the list of users assigned this task
        if ($user->perms('edit_assignments') && Post::val('old_assigned') != trim(Post::val('assigned_to')) ) {
            
            // Delete the current assignees for this task
            $db->Query('DELETE FROM {assigned}
                              WHERE task_id = ?',
                        array($task['task_id']));

            // Convert assigned_to and store them in the 'assigned' table
            foreach (Flyspray::int_explode(' ', trim(Post::val('assigned_to'))) as $key => $val)
            {
                $db->Replace('{assigned}', array('user_id'=> $val, 'task_id'=> $task['task_id']), array('user_id','task_id'));
            }
         }

        // Get the details of the task we just updated
        // To generate the changed-task message
        $new_details_full = Flyspray::GetTaskDetails($task['task_id']);
        // Not very nice...maybe combine compare_tasks() and logEvent() ?
        $result = $db->Query("SELECT * FROM {tasks} WHERE task_id = ?",
                             array($task['task_id']));
        $new_details = $db->FetchRow($result);

        foreach ($new_details as $key => $val) {
            if (strstr($key, 'last_edited_') || $key == 'assigned_to'
                    || is_numeric($key))
            {
                continue;
            }
            
            if ($val != $task[$key]) {
                // Log the changed fields in the task history
                Flyspray::logEvent($task['task_id'], 3, $val, $task[$key], $key, $time);
            }
        }
        
        $changes = Flyspray::compare_tasks($task, $new_details_full);
        if (count($changes) > 0) {
            $notify->Create(NOTIFY_TASK_CHANGED, $task['task_id'], $changes);
        }

        if (Post::val('old_assigned') != trim(Post::val('assigned_to')) ) {
            // Log to task history
            Flyspray::logEvent($task['task_id'], 14, trim(Post::val('assigned_to')), Post::val('old_assigned'), '', $time);

            // Notify the new assignees what happened.  This obviously won't happen if the task is now assigned to no-one.
            if (Post::val('assigned_to') != '') {
                $new_assignees = array_diff(Flyspray::int_explode(' ', Post::val('assigned_to')), Flyspray::int_explode(' ', Post::val('old_assigned')));
                // Remove current user from notification list
                if (!$user->infos['notify_own']) {
                    $new_assignees = array_filter($new_assignees, create_function('$u', 'global $user; return $user->id != $u;'));
                }
                $notify->Create(NOTIFY_NEW_ASSIGNEE, $task['task_id'], null, $notify->SpecificAddresses($new_assignees));
            }
        }
        
        Backend::add_comment($task, Post::val('comment_text'), $time);
        Backend::delete_files(Post::val('delete_att'));
        Backend::upload_files($task['task_id'], '0', 'usertaskfile');

        $_SESSION['SUCCESS'] = L('taskupdated');
        break;
    
    // ##################
    // closing a task
    // ##################
    case 'details.close':
        if (!$user->can_close_task($task)) {
            break;
        }
        
        if (!Post::val('resolution_reason')) {
            Flyspray::show_error(L('noclosereason'));
            break;
        }
        
        Backend::close_task($task['task_id'], Post::val('resolution_reason'), Post::val('closure_comment', ''), Post::val('mark100', false));

        $_SESSION['SUCCESS'] = L('taskclosedmsg');
        break;
    
    case 'reopen':
    // ##################
    // re-opening an task
    // ##################
        if (!$user->can_close_task($task)) {
            break;
        }
        
        // Get last %
        $old_percent = $db->Query("SELECT old_value, new_value
                                     FROM {history}
                                    WHERE field_changed = 'percent_complete'
                                          AND task_id = ? AND old_value != '100'
                                 ORDER BY event_date DESC
                                    LIMIT 1",
                                  array($task['task_id']));
        $old_percent = $db->FetchRow($old_percent);
        
        $db->Query("UPDATE  {tasks}
                       SET  resolution_reason = 0, closure_comment = '', date_closed = 0,
                            last_edited_time = ?, last_edited_by = ?, is_closed = 0, percent_complete = ?
                     WHERE  task_id = ?",
                    array(time(), $user->id, $old_percent['old_value'], $task['task_id']));
        
        Flyspray::logEvent($task['task_id'], 3, $old_percent['old_value'], $old_percent['new_value'], 'percent_complete');

        $notify->Create(NOTIFY_TASK_REOPENED, $task['task_id']);

        // If there's an admin request related to this, close it
        $db->Query('UPDATE  {admin_requests}
                       SET  resolved_by = ?, time_resolved = ?
                     WHERE  task_id = ? AND request_type = ?',
                  array($user->id, time(), $task['task_id'], 2));

        Flyspray::logEvent($task['task_id'], 13);

        $_SESSION['SUCCESS'] = L('taskreopenedmsg');
        break;

    // ##################
    // adding a comment
    // ##################
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
        break;

    // ##################
    // sending a new user a confirmation code 
    // ##################
    case 'register.sendcode':
        if (!$user->can_register()) {
            break;
        }
        
        if (!Post::val('user_name') || !Post::val('real_name')
            || !Post::val('email_address'))
         {
            // If the form wasn't filled out correctly, show an error
            Flyspray::show_error(L('registererror'));
            break;
        }
        
        $email =  Post::val('email_address');
        $jabber_id = Post::val('jabber_id');

        //email is mandatory
        if (!$email || !Flyspray::check_email($email)) {
            Flyspray::show_error(L('novalidemail'));
            break;
        }
        //jabber_id is optional
        if ($jabber_id && !Jabber::check_jid($jabber_id)) {
            Flyspray::show_error(L('novalidjabber'));
            break;
        }

        // Limit lengths
        $user_name = substr(trim(Post::val('user_name')), 0, 32);
        $real_name = substr(trim(Post::val('real_name')), 0, 100);
        // Remove doubled up spaces and control chars
        if (version_compare(PHP_VERSION, '4.3.4') == 1) {
            $user_name = preg_replace('![\x00-\x1f\s]+!u', ' ', $user_name);
            $real_name = preg_replace('![\x00-\x1f\s]+!u', ' ', $real_name);
        }
        // Strip special chars
        $user_name = utf8_keepalphanum($user_name);

        if (!$user_name || !$real_name) {
            Flyspray::show_error(L('entervalidusername'));
            break;
        }

        // Delete registration codes older than 24 hours
        $yesterday = time() - 86400;
        $db->Query('DELETE FROM {registrations} WHERE reg_time < ?', array($yesterday));

        $sql = $db->Query('SELECT COUNT(*) FROM {users} u, {registrations} r
                           WHERE  u.user_name = ? OR r.user_name = ?',
                           array($user_name, $user_name));
        if ($db->fetchOne($sql)) {
            Flyspray::show_error(L('usernametaken'));
            break;
        }

        $sql = $db->Query("SELECT COUNT(*) FROM {users} WHERE 
                           jabber_id = ? AND jabber_id != '' 
                           OR email_address = ? AND email_address != ''",
                          array($jabber_id, $email));
        if ($db->fetchOne($sql)) {
            Flyspray::show_error(L('emailtaken'));
            break;
        }
        
        // Generate a random bunch of numbers for the confirmation code and the confirmation url

        foreach(array('randval','magic_url') as $genrandom) {

            $$genrandom = md5(uniqid(rand(), true));
        }

        // Convert those numbers to a seemingly random string using crypt
        $confirm_code = crypt($randval, $conf['general']['cookiesalt']);

        //send the email first.
        if($notify->Create(NOTIFY_CONFIRMATION, null, array($baseurl, $magic_url, $user_name, $confirm_code),
                           Post::val('email_address'), NOTIFY_EMAIL)) {
        
            //email sent succefully, now update the database.
            $reg_values = array(time(), $confirm_code, $user_name, $real_name,
                        Post::val('email_address'), Post::val('jabber_id'),
                        Post::num('notify_type'), $magic_url, Post::num('time_zone')); 
            // Insert everything into the database
            $query = $db->Query("INSERT INTO  {registrations}
                                 ( reg_time, confirm_code, user_name, real_name,
                                   email_address, jabber_id, notify_type,
                                   magic_url, time_zone )
                         VALUES ( " . $db->fill_placeholders($reg_values) . ' )', $reg_values);

                if ($query) {
                    $_SESSION['SUCCESS'] = L('codesent');
                    Flyspray::Redirect('./');
                }
         
            } else {
                Flyspray::show_error(L('codenotsent'));
                break;
            }
         
        break;

    // ##################
    // new user self-registration with a confirmation code
    // ##################
    case 'register.registeruser':
        if (!$user->can_register()) {
            break;
        }
        
        if (!Post::val('user_pass') || !Post::val('confirmation_code')) {
            Flyspray::show_error(L('formnotcomplete'));
            break;
        }

        if (Post::val('user_pass') != Post::val('user_pass2')) {
            Flyspray::show_error(L('nomatchpass'));
            break;
        }

        // Check that the user entered the right confirmation code
        $sql = $db->Query("SELECT * FROM {registrations} WHERE magic_url = ?",
                array(Post::val('magic_url')));
        $reg_details = $db->FetchRow($sql);

        if ($reg_details['confirm_code'] != trim(Post::val('confirmation_code'))) {
            Flyspray::show_error(L('confirmwrong'));
            break;
        }

        if (!Backend::create_user($reg_details['user_name'], Post::val('user_pass'), $reg_details['real_name'], $reg_details['jabber_id'],
                         $reg_details['email_address'], $reg_details['notify_type'], $reg_details['time_zone'], $fs->prefs['anon_group'])) {
            Flyspray::show_error(L('usernametaken'));
            break;
        }

        $_SESSION['SUCCESS'] = L('accountcreated');
        define('NO, DO', true);
        $page->pushTpl('register.ok.tpl');
        break;

    // ##################
    // new user self-registration with a confirmation code
    // ##################
    case 'register.newuser':
    case 'admin.newuser':
        if (!($user->perms('is_admin') || $user->can_self_register())) {
            break;
        }

        if (!Post::val('user_name') || !Post::val('real_name')
            || !Post::val('email_address'))
        {
            // If the form wasn't filled out correctly, show an error
            Flyspray::show_error(L('registererror'));
            break;
        }
        
        if (Post::val('user_pass') != Post::val('user_pass2')) {
            Flyspray::show_error(L('nomatchpass'));
            break;
        }
        
        if ($user->perms('is_admin')) {
            $group_in = Post::val('group_in');
        } else {
            $group_in = $fs->prefs['anon_group'];
        }

        $sql = $db->Query("SELECT COUNT(*) FROM {users} WHERE 
                           jabber_id = ? AND jabber_id != '' 
                           OR email_address = ? AND email_address != ''",
                          array(Post::val('jabber_id'), Post::val('email_address')));
        if ($db->fetchOne($sql)) {
            Flyspray::show_error(L('emailtaken'));
            break;
        }
        
        if (!Backend::create_user(Post::val('user_name'), Post::val('user_pass'),
                              Post::val('real_name'), Post::val('jabber_id'),
                              Post::val('email_address'), Post::num('notify_type'),
                              Post::num('time_zone'), $group_in)) {
            Flyspray::show_error(L('usernametaken'));
            break;
        }

        $_SESSION['SUCCESS'] = L('newusercreated');
        if (!$user->perms('is_admin')) {
            define('NO_DO', true);
            $page->pushTpl('register.ok.tpl');
        }
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
            $sql = $db->Query("SELECT  COUNT(*)
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
                        'close_other_tasks', 'assign_to_self',
                        'assign_others_to_self', 'add_to_assignees', 'view_reports', 'group_open');

                $params = array_map('Post_to0',$cols);
                array_unshift($params, $proj->id);
                
                $db->Query("INSERT INTO  {groups} (project_id, ". join(',', $cols).")
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
        
        $settings = array('jabber_server', 'jabber_port', 'jabber_username', 'notify_registration',
                'jabber_password', 'anon_group', 'user_notify', 'admin_email',
                'lang_code', 'spam_proof', 'default_project', 'dateformat', 'jabber_ssl',
                'dateformat_extended', 'anon_reg', 'global_theme', 'smtp_server', 'page_title',
                'smtp_user', 'smtp_pass', 'funky_urls', 'reminder_daemon','cache_feeds');
        foreach ($settings as $setting) {
            $db->Query('UPDATE {prefs} SET pref_value = ? WHERE pref_name = ?',
                    array(Post::val($setting), $setting));
        }

        // Process the list of groups into a format we can store
        $db->Query("UPDATE  {prefs} SET pref_value = ?
                     WHERE  pref_name = 'visible_columns'",
                array(trim(Post::val('visible_columns'))));

        $_SESSION['SUCCESS'] = L('optionssaved');
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
                    : 'id tasktype severity summary status dueversion progress';


        $db->Query('INSERT INTO  {projects}
                                 ( project_title, theme_style, intro_message,
                                   others_view, anon_open, project_is_active,
                                   visible_columns, lang_code, notify_reply)
                         VALUES  (?, ?, ?, ?, ?, 1, ?, ?, ?)',
                  array(Post::val('project_title'), Post::val('theme_style'),
                        Post::val('intro_message'), Post::val('others_view', 0),
                        Post::val('anon_open', 0),
                        $viscols,
                        Post::val('lang_code'), ''));

        $sql = $db->Query('SELECT project_id FROM {projects} ORDER BY project_id DESC', false, 1);
        $pid = $db->fetchOne($sql);

        $cols = array( 'manage_project', 'view_tasks', 'open_new_tasks',
                'modify_own_tasks', 'modify_all_tasks', 'view_comments',
                'add_comments', 'edit_comments', 'delete_comments',
                'create_attachments', 'delete_attachments', 'view_history', 'add_votes',
                'close_own_tasks', 'close_other_tasks', 'assign_to_self', 'edit_own_comments',
                'assign_others_to_self', 'add_to_assignees', 'view_reports', 'group_open');
        $args = array_fill(0, count($cols), '1');
        array_unshift($args, 'Project Managers',
                'Permission to do anything related to this project.',
                intval($pid));

        $db->Query("INSERT INTO  {groups}
                                 ( group_name, group_desc, project_id,
                                   ".join(',', $cols).")
                         VALUES  ( ". $db->fill_placeholders($cols, 3) .")", $args);

        $db->Query("INSERT INTO  {list_category}
                                 ( project_id, category_name,
                                   show_in_list, category_owner, lft, rgt)
                         VALUES  ( ?, ?, 1, 0, 1, 4)", array($pid, 'root'));
                         
        $db->Query("INSERT INTO  {list_category}
                                 ( project_id, category_name,
                                   show_in_list, category_owner, lft, rgt )
                         VALUES  ( ?, ?, 1, 0, 2, 3)", array($pid, 'Backend / Core'));

        $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, 1, 1)", array($pid, 'All'));

        $db->Query("INSERT INTO  {list_version}
                                 ( project_id, version_name, list_position,
                                   show_in_list, version_tense )
                         VALUES  (?, ?, 1, 1, 2)", array($pid, '1.0'));

        $_SESSION['SUCCESS'] = L('projectcreated');
        Flyspray::Redirect(CreateURL('pm', 'prefs', $pid));
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
                Flyspray::Redirect(CreateURL('pm', 'prefs', Post::val('move_to')));
            } else {
                Flyspray::Redirect($baseurl);
            }
        }
        
        if (!Post::val('project_title')) {
            Flyspray::show_error(L('emptytitle'));
            break;
        }

        $cols = array( 'project_title', 'theme_style', 'lang_code', 'default_task', 'default_entry',
                'intro_message', 'project_is_active', 'others_view', 'anon_open',
                'notify_email', 'notify_jabber', 'notify_subject', 'notify_reply',
                'feed_description', 'feed_img_url', 'comment_closed', 'auto_assign');
        $args = array_map('Post_to0', $cols);
        $cols[] = 'notify_types';
        $args[] = implode(' ', Post::val('notify_types'));
        $cols[] = 'last_updated';
        $args[] = time();
        $cols[] = 'default_cat_owner';
        $args[] =  Flyspray::username_to_id(Post::val('default_cat_owner'));
        $args[] = $proj->id;

        $update = $db->Query("UPDATE  {projects}
                                 SET  ".join('=?, ', $cols)."=?
                               WHERE  project_id = ?", $args);

        $update = $db->Query('UPDATE {projects} SET visible_columns = ? WHERE project_id = ?',
                             array(trim(Post::val('visible_columns')), $proj->id));


        $_SESSION['SUCCESS'] = L('projectupdated');
        break;

    // ##################
    // modifying user details/profile
    // ##################
    case 'admin.edituser':
    case 'myprofile.edituser':
        if (Post::val('delete_user')) {
            // check that he is not the last user
            $admins = $db->FetchOne($db->Query('SELECT count(*) FROM {users}'));
            if ($admins > 1) {
                Backend::delete_user(Post::val('user_id'));
                $_SESSION['SUCCESS'] = L('userdeleted');
                Flyspray::Redirect(CreateURL('admin', 'groups'));
            } else {
                Flyspray::show_error(L('lastuser'));
                break;
            }
        }
        
        if (!Post::val('onlypmgroup')):
        if ($user->perms('is_admin') || $user->id == Post::val('user_id')): // only admin or user himself can change
        
        if (!Post::val('real_name') || (!Post::val('email_address') && !Post::val('jabber_id'))) {
            Flyspray::show_error(L('realandnotify'));
            break;
        }
        
        if ( (!$user->perms('is_admin') || $user->id == Post::val('user_id')) && !Post::val('oldpass')
             && (Post::val('changepass') || Post::val('confirmpass')) ) {
            Flyspray::show_error(L('nooldpass'));
            break;
        }
        if (Post::val('changepass') || Post::val('confirmpass')) {
            if (Post::val('changepass') != Post::val('confirmpass')) {
                Flyspray::show_error(L('passnomatch'));
                break;
            }
            if (Post::val('oldpass')) {
              $sql = $db->Query('SELECT user_pass FROM {users} WHERE user_id = ?', array(Post::val('user_id')));
              $oldpass =  $db->FetchRow($sql);

              if (Flyspray::cryptPassword(Post::val('oldpass')) != $oldpass['user_pass']){
                Flyspray::show_error(L('oldpasswrong'));
                break;
              }  
            }
            $new_hash = Flyspray::cryptPassword(Post::val('changepass'));
            $db->Query('UPDATE {users} SET user_pass = ? WHERE user_id = ?',
                    array($new_hash, Post::val('user_id')));

            // If the user is changing their password, better update their cookie hash
            if ($user->id == Post::val('user_id')) {
                Flyspray::setcookie('flyspray_passhash',
                        crypt($new_hash, $conf['general']['cookiesalt']), time()+3600*24*30);
            }
        }

        $db->Query('UPDATE  {users}
                       SET  real_name = ?, email_address = ?, notify_own = ?,
                            jabber_id = ?, notify_type = ?,
                            dateformat = ?, dateformat_extended = ?,
                            tasks_perpage = ?, time_zone = ?
                     WHERE  user_id = ?',
                array(Post::val('real_name'), Post::val('email_address'), Post::val('notify_own', 0),
                    Post::val('jabber_id', 0), Post::num('notify_type'),
                    Post::val('dateformat', 0), Post::val('dateformat_extended', 0),
                    Post::val('tasks_perpage'), Post::val('time_zone'), Post::val('user_id')));

        endif; // end only admin or user himself can change
        
        if ($user->perms('is_admin')) {
            $db->Query('UPDATE {users} SET account_enabled = ?  WHERE user_id = ?',
                    array(Post::val('account_enabled', 0), Post::val('user_id')));

            $db->Query('UPDATE {users_in_groups} SET group_id = ?
                         WHERE group_id = ? AND user_id = ?',
                    array(Post::val('group_in'), Post::val('old_global_id'), Post::val('user_id')));
        }
        
        endif; // end non project group changes

        if ($user->perms('manage_project') && !is_null(Post::val('project_group_in')) && Post::val('project_group_in') != Post::val('old_project_id')) {
           $db->Query('DELETE FROM {users_in_groups} WHERE group_id = ? AND user_id = ?',
                        array(Post::val('old_project_id'), Post::val('user_id')));
            if (Post::val('project_group_in')) {
                $db->Query('INSERT INTO {users_in_groups} (group_id, user_id) VALUES(?, ?)',
                           array(Post::val('project_group_in'), Post::val('user_id')));
            }
        }

        $_SESSION['SUCCESS'] = L('userupdated');
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
            $uids = preg_split('/[\s,;]+/', $uid, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($uids as $uid) {
                $uid = Flyspray::username_to_id($uid);
                
                // If user is already a member of one of the project's groups, **move** (not add) him to the new group
                $sql = $db->Query('SELECT g.group_id
                                     FROM {users_in_groups} uig, {groups} g
                                    WHERE g.group_id = uig.group_id AND uig.user_id = ? AND project_id = ?',
                                    array($uid, $proj->id));
                if ($db->CountRows($sql)) {
                    $oldid = $db->FetchOne($sql);
                    $db->Query('UPDATE {users_in_groups} SET group_id = ? WHERE user_id = ? AND group_id = ?',
                                array(Post::val('group_id'), $uid, $oldid));
                } else {
                    $db->Query('INSERT INTO {users_in_groups} (group_id, user_id) VALUES(?, ?)',
                                array(Post::val('group_id'), $uid));
                }
            }
        }
        
        if (Post::val('delete_group') && Post::val('group_id') != '1') {
            $db->Query('DELETE FROM {groups} WHERE group_id = ?', Post::val('group_id'));
            
            if (Post::val('move_to')) {
                $db->Query('UPDATE {users_in_groups} SET group_id = ? WHERE group_id = ?',
                            array(Post::val('move_to'), Post::val('group_id')));
            }
            
            $_SESSION['SUCCESS'] = L('groupupdated');
            Flyspray::Redirect(CreateURL( (($proj->id) ? 'pm' : 'admin'), 'groups', $proj->id));
        }
        // Allow all groups to update permissions except for global Admin
        if (Post::val('group_id') != '1') {
            $cols = array_merge($cols,
                                array('manage_project', 'view_tasks', 'edit_own_comments', 
                                  'open_new_tasks', 'modify_own_tasks', 'modify_all_tasks',
                                  'view_comments', 'add_comments', 'edit_comments', 'delete_comments',
                                  'create_attachments', 'delete_attachments',
                                  'view_history', 'close_own_tasks', 'close_other_tasks', 'edit_assignments',
                                  'assign_to_self', 'assign_others_to_self', 'add_to_assignees', 'view_reports',
                                  'add_votes', 'group_open'));
        }

        $args = array_map('Post_to0', $cols);
        $args[] = Post::val('group_id');
        $args[] = $proj->id;

        $db->Query("UPDATE  {groups}
                       SET  ".join('=?,', $cols)."=?
                     WHERE  group_id = ? AND project_id = ?", $args);

        $_SESSION['SUCCESS'] = L('groupupdated');
        break;
        
    // ##################
    // updating a list
    // ##################
    case 'update_list':
        if (!$user->perms('manage_project') || !isset($list_table_name)) {
            break;
        }
        
        $listnames    = Post::val('list_name');
        $listposition = Post::val('list_position');
        $listshow     = Post::val('show_in_list');
        $listdelete   = Post::val('delete');

        foreach ($listnames as $id => $listname) {
            if ($listname != '') {
                if (!isset($listshow[$id])) {
                    $listshow[$id] = 0;
                }
                $update = $db->Query("UPDATE  $list_table_name
                                         SET  $list_column_name = ?, list_position = ?, show_in_list = ?
                                       WHERE  $list_id = ? AND project_id = ?",
                                      array($listnames[$id], intval($listposition[$id]), intval($listshow[$id]), $id, $proj->id));
            } else {
                Flyspray::show_error(L('fieldsmissing'));
            }
        }

        if (is_array($listdelete) && count($listdelete)) {
            $deleteids = "$list_id = " . join(" OR $list_id =", array_map('intval', array_keys($listdelete)));
            $db->Query("DELETE FROM $list_table_name WHERE project_id = ? AND ($deleteids)", array($proj->id));
        }

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
            $position = intval($db->FetchOne($db->Query("SELECT max(list_position)+1
                                                    FROM $list_table_name
                                                   WHERE project_id = ?",
                                                 array($proj->id))));
        }
        
        $db->Query("INSERT INTO  $list_table_name
                                 (project_id, $list_column_name, list_position, show_in_list)
                         VALUES  (?, ?, ?, ?)",
                array($proj->id, Post::val('list_name'), $position, '1'));

        $_SESSION['SUCCESS'] = L('listitemadded');
        break;

    // ##################
    // updating the version list
    // ##################
    case 'update_version_list':
        if (!$user->perms('manage_project') || !isset($list_table_name)) {
            break;
        }
        
        $listnames    = Post::val('list_name');
        $listposition = Post::val('list_position');
        $listshow     = Post::val('show_in_list');
        $listtense    = Post::val('version_tense');
        $listdelete   = Post::val('delete');

        foreach ($listnames as $id => $listname) {
            if (is_numeric($listposition[$id]) && $listnames[$id] != '') {
                if (!isset($listshow[$id])) {
                    $listshow[$id] = 0;
                }
                $update = $db->Query("UPDATE  $list_table_name
                                         SET  $list_column_name = ?, list_position = ?,
                                              show_in_list = ?, version_tense = ?
                                       WHERE  $list_id = ? AND project_id = ?",
                        array($listnames[$id], intval($listposition[$id]),
                            intval($listshow[$id]), intval($listtense[$id]), $id, $proj->id));
            } else {
                Flyspray::show_error(L('fieldsmissing'));
            }
        }

        if (is_array($listdelete) && count($listdelete)) {
            $deleteids = "$list_id = " . join(" OR $list_id =", array_map('intval', array_keys($listdelete)));
            $db->Query("DELETE FROM $list_table_name WHERE project_id = ? AND ($deleteids)", array($proj->id));
        }

        $_SESSION['SUCCESS'] = L('listupdated');
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
            $position = $db->FetchOne($db->Query("SELECT max(list_position)+1
                                                    FROM $list_table_name
                                                   WHERE project_id = ?",
                                                 array($proj->id)));
        }

        $db->Query("INSERT INTO  $list_table_name
                                (project_id, $list_column_name, list_position, show_in_list, version_tense)
                        VALUES  (?, ?, ?, ?, ?)",
                 array($proj->id, Post::val('list_name'),
                     intval($position), '1', Post::val('version_tense')));

        $_SESSION['SUCCESS'] = L('listitemadded');
        break;

    // ##################
    // updating the category list
    // ##################
    case 'update_category':
        if (!$user->perms('manage_project')) {
            break;
        }
        
        $listnames    = Post::val('list_name');
        $listshow     = Post::val('show_in_list');
        $listdelete   = Post::val('delete');
        $listlft      = Post::val('lft');
        $listrgt      = Post::val('rgt');
        $listowners   = Post::val('category_owner');

        foreach ($listnames as $id => $listname) {
            if ($listname != '') {
                if (!isset($listshow[$id])) {
                    $listshow[$id] = 0;
                }
                $update = $db->Query('UPDATE  {list_category}
                                         SET  category_name = ?,
                                              show_in_list = ?, category_owner = ?,
                                              lft = ?, rgt = ?
                                       WHERE  category_id = ? AND project_id = ?',
                                  array($listname, intval($listshow[$id]), Flyspray::username_to_id($listowners[$id]), $listlft[$id], $listrgt[$id], $id, $proj->id));
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
            $db->Query("DELETE FROM {list_category} WHERE project_id = ? AND ($deleteids)", array($proj->id));
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
        $right = $db->Query('SELECT rgt FROM {list_category} WHERE category_id = ?', array(Post::val('parent_id', -1)));
        $right = $db->FetchOne($right);
        $db->Query('UPDATE {list_category} SET rgt=rgt+2 WHERE rgt >= ? AND project_id = ?', array($right, $proj->id));
        $db->Query('UPDATE {list_category} SET lft=lft+2 WHERE lft >= ? AND project_id = ?', array($right, $proj->id));
        
        $db->Query("INSERT INTO  {list_category}
                                 ( project_id, category_name, show_in_list, category_owner, lft, rgt )
                         VALUES  (?, ?, 1, ?, ?, ?)",
                array($proj->id, Post::val('list_name'),
                      Post::val('category_owner', 0) == '' ? '0' : Flyspray::username_to_id(Post::val('category_owner', 0)), $right, $right+1));

        $_SESSION['SUCCESS'] = L('listitemadded');
        break;

    // ##################
    // adding a related task entry
    // ##################
    case 'details.add_related':
        if (!$user->can_edit_task($task)) {
            break;
        }

        $sql = $db->Query('SELECT  project_id
                             FROM  {tasks}
                            WHERE  task_id = ?',
                          array(Post::val('related_task')));
        if (!$db->CountRows($sql)) {
            Flyspray::show_error(L('relatedinvalid'));
            break;
        }

        $sql = $db->Query("SELECT related_id
                             FROM {related}
                            WHERE this_task = ? AND related_task = ?
                                  OR
                                  related_task = ? AND this_task = ?",
                          array($task['task_id'], Post::val('related_task'),
                                $task['task_id'], Post::val('related_task')));
       
        if ($db->CountRows($sql)) {
            Flyspray::show_error(L('relatederror'));
            break;
        }
            
        $db->Query("INSERT INTO {related} (this_task, related_task) VALUES(?,?)",
                array($task['task_id'], Post::val('related_task')));

        Flyspray::logEvent($task['task_id'], 11, Post::val('related_task'));
        Flyspray::logEvent(Post::val('related_task'), 11, $task['task_id']);

        $notify->Create(NOTIFY_REL_ADDED, $task['task_id'], Post::val('related_task'));

        $_SESSION['SUCCESS'] = L('relatedaddedmsg');
        break;

    // ##################
    // Removing a related task entry
    // ##################
    case 'remove_related':
        if (!$user->can_edit_task($task)  || !is_array(Post::val('related_id'))) {
            break;
        }

        foreach (Post::val('related_id') as $related) {
            $sql = $db->Query('SELECT this_task, related_task FROM {related} WHERE related_id = ?',
                              array($related));
            $db->Query('DELETE FROM {related} WHERE related_id = ? AND (this_task = ? OR related_task = ?)',
                        array($related, $task['task_id'], $task['task_id']));
            if ($db->AffectedRows()) {
                $related_task = $db->FetchRow($sql);
                $related_task = ($related_task['this_task'] == $task['task_id']) ? $related_task['related_task'] : $task['task_id'];
                Flyspray::logEvent($task['task_id'], 12, $related_task);
                Flyspray::logEvent($related_task, 12, $task['task_id']);
                $_SESSION['SUCCESS'] = L('relatedremoved');
            }
        }

        break;

    // ##################
    // adding a user to the notification list
    // ##################
    case 'details.add_notification':
        if (!Backend::add_notification(Req::val('user_id'), Req::val('ids'))) {
            Flyspray::show_error(L('couldnotaddusernotif'));
            break;
        }
        
        $_SESSION['SUCCESS'] = L('notifyadded');
        break;

    // ##################
    // removing a notification entry
    // ##################
    case 'remove_notification':
        Backend::remove_notification(Req::val('user_id'), Req::val('ids'));
        
        $_SESSION['SUCCESS'] = L('notifyremoved');
        break;

    // ##################
    // editing a comment
    // ##################
    case 'editcomment':
        if (!($user->perms('edit_comments') || $user->perms('edit_own_comments'))) {
            break;
        }
        
        $where = '';
        
        $params = array(Post::val('comment_text'), time(), 
                        Post::val('comment_id'), $task['task_id']);

        if ($user->perms('edit_own_comments') && !$user->perms('edit_comments')) {
            
            $where = ' AND user_id = ?';
            array_push($params, $user->id);
        }
        
        $db->Query("UPDATE  {comments}
                       SET  comment_text = ?, last_edited_time = ?
                     WHERE  comment_id = ? AND task_id = ? $where", $params);

        Flyspray::logEvent($task['task_id'], 5, Post::val('comment_text'),
                Post::val('previous_text'), Post::val('comment_id'));
                
        Backend::upload_files($task['task_id'], Post::val('comment_id'));
        Backend::delete_files(Post::val('delete_att'));

        $_SESSION['SUCCESS'] = L('editcommentsaved');
        break;
        
    // ##################
    // deleting a comment
    // ##################
    case 'details.deletecomment':
        if (!$user->perms('delete_comments')) {
            break;
        }
        
        $result = $db->Query('SELECT  task_id, comment_text, user_id, date_added
                                FROM  {comments}
                               WHERE  comment_id = ?',
                            array(Get::val('comment_id')));
        $comment = $db->FetchRow($result);

        // Check for files attached to this comment
        $check_attachments = $db->Query('SELECT  *
                                           FROM  {attachments}
                                          WHERE  comment_id = ?',
                                        array(Req::val('comment_id')));

        if ($db->CountRows($check_attachments) && !$user->perms('delete_attachments')) {
            Flyspray::show_error(L('commentattachperms'));
            break;
        }

        $db->Query("DELETE FROM {comments} WHERE comment_id = ? AND task_id = ?",
                   array(Req::val('comment_id'), $task['task_id']));

        if ($db->AffectedRows()) {
            Flyspray::logEvent($task['task_id'], 6, $comment['user_id'],
                    $comment['comment_text'], $comment['date_added']);
        }

        while ($attachment = $db->FetchRow($check_attachments)) {
            $db->Query("DELETE from {attachments} WHERE attachment_id = ?",
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
        $how_often  = Post::val('timeamount1', 1) * Post::val('timetype1');
        $start_time = Flyspray::strtotime(Post::val('timeamount2', 0));
        
        if (!Backend::add_reminder($task['task_id'], Post::val('reminder_message'), $how_often, $start_time, Post::val('to_user_id'))) {
            Flyspray::show_error(L('usernotexist'));
            break;
        }
        
        $_SESSION['SUCCESS'] = L('reminderaddedmsg');
        break;
        
    // ##################
    // removing a reminder
    // ##################
    case 'deletereminder':
        if (!$user->perms('manage_project') || !is_array(Post::val('reminder_id'))) {
            break;
        }
        
        foreach (Post::val('reminder_id') as $reminder_id) {
            $sql = $db->Query('SELECT to_user_id FROM {reminders} WHERE reminder_id = ?',
                              array($reminder_id));
            $reminder = $db->fetchOne($sql);
            $db->Query('DELETE FROM {reminders} WHERE reminder_id = ? AND task_id = ?',
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
        $sql = $db->Query('SELECT project_id FROM {groups} WHERE group_id = ? OR group_id = ?',
                          array(Post::val('switch_to_group'), Post::val('old_group')));
        $old_pr = $db->FetchOne($sql);
        $new_pr = $db->FetchOne($sql);
        if ($proj->id != $old_pr || ($new_pr && $new_pr != $proj->id)) {
            break;
        }
        
        if (!$user->perms('manage_project', $old_pr) || !is_array(Post::val('users'))) {
            break;
        }
        
        foreach (Post::val('users') as $user_id => $val) {
            if (Post::val('switch_to_group') == '0') {
                $db->Query('DELETE FROM  {users_in_groups}
                                  WHERE  user_id = ? AND group_id = ?',
                        array($user_id, Post::val('old_group')));
            } else {
                $db->Query('UPDATE  {users_in_groups}
                               SET  group_id = ?
                             WHERE  user_id = ? AND group_id = ?',
                         array(Post::val('switch_to_group'), $user_id, Post::val('old_group')));
            }
        }

        $_SESSION['SUCCESS'] = L('groupswitchupdated');
        break;
        
    // ##################
    // taking ownership
    // ##################
    case 'takeownership':      
        Backend::assign_to_me($user->id, Req::val('ids'));

        $_SESSION['SUCCESS'] = L('takenownershipmsg');
        break;
        
    // ##################
    // add to assignees list
    // ##################
    case 'addtoassignees':       
        Backend::add_to_assignees($user->id, Req::val('ids'));
    
        $_SESSION['SUCCESS'] = L('addedtoassignees');
        break;

    // ##################
    // admin request
    // ##################
    case 'requestclose':
    case 'requestreopen':
        if ($action == 'requestclose') {
            Flyspray::AdminRequest(1, $proj->id, $task['task_id'], $user->id, Post::val('reason_given'));
            Flyspray::logEvent($task['task_id'], 20, Post::val('reason_given'));
        } elseif ($action == 'requestreopen') {
            Flyspray::AdminRequest(2, $proj->id, $task['task_id'], $user->id, Post::val('reason_given'));
            Flyspray::logEvent($task['task_id'], 21, Post::val('reason_given'));
            Backend::add_notification($user->id, $task['task_id']);
        }

        // Now, get the project managers' details for this project
        $sql = $db->Query("SELECT  u.user_id
                             FROM  {users} u
                        LEFT JOIN  {users_in_groups} uig ON u.user_id = uig.user_id
                        LEFT JOIN  {groups} g ON uig.group_id = g.group_id
                            WHERE  g.project_id = ? AND g.manage_project = '1'",
                          array($proj->id));

        $pms = $db->fetchCol($sql);

        // Call the functions to create the address arrays, and send notifications
        $notify->Create(NOTIFY_PM_REQUEST, $task['task_id'], null, $notify->SpecificAddresses($pms));

        $_SESSION['SUCCESS'] = L('adminrequestmade');
        break;

    // ##################
    // denying a PM request
    // ##################
    case 'denypmreq':
        $result = $db->Query("SELECT  task_id, project_id
                                FROM  {admin_requests}
                               WHERE  request_id = ?",
                              array(Req::val('req_id')));
        $req_details = $db->FetchRow($result);
        
        if (!$user->perms('manage_project', $req_details['project_id'])) {
            break;
        }

        // Mark the PM request as 'resolved'
        $db->Query("UPDATE  {admin_requests}
                       SET  resolved_by = ?, time_resolved = ?, deny_reason = ?
                     WHERE  request_id = ?",
                    array($user->id, time(), Req::val('deny_reason'), Req::val('req_id')));

        Flyspray::logEvent($req_details['task_id'], 28, Req::val('deny_reason'));
        $notify->Create(NOTIFY_PM_DENY_REQUEST, $req_details['task_id'], Req::val('deny_reason'));

        $_SESSION['SUCCESS'] = L('pmreqdeniedmsg');
        break;

    // ##################
    // adding a dependency
    // ##################
    case 'details.newdep':
        if (!$user->can_edit_task($task)) {
            break;
        }
        
        if (!Post::val('dep_task_id')) {
            Flyspray::show_error(L('formnotcomplete'));
            break;
        }
                            
        // First check that the user hasn't tried to add this twice
        $sql1 = $db->Query('SELECT  COUNT(*) FROM {dependencies}
                             WHERE  task_id = ? AND dep_task_id = ?',
                array($task['task_id'], Post::val('dep_task_id')));

        // or that they are trying to reverse-depend the same task, creating a mutual-block
        $sql2 = $db->Query('SELECT  COUNT(*) FROM {dependencies}
                             WHERE  task_id = ? AND dep_task_id = ?',
                array(Post::val('dep_task_id'), $task['task_id']));

        // Check that the dependency actually exists!
        $sql3 = $db->Query('SELECT COUNT(*) FROM {tasks} WHERE task_id = ?',
                array(Post::val('dep_task_id')));

        if ($db->fetchOne($sql1) || $db->fetchOne($sql2) || !$db->fetchOne($sql3)
                // Check that the user hasn't tried to add the same task as a dependency
                || Post::val('task_id') == Post::val('dep_task_id'))
        {
            Flyspray::show_error(L('dependaddfailed'));
            break;
        }

        $notify->Create(NOTIFY_DEP_ADDED, $task['task_id'], Post::val('dep_task_id'));
        $notify->Create(NOTIFY_REV_DEP, Post::val('dep_task_id'), $task['task_id']);

        // Log this event to the task history, both ways
        Flyspray::logEvent($task['task_id'], 22, Post::val('dep_task_id'));
        Flyspray::logEvent(Post::val('dep_task_id'), 23, $task['task_id']);

        $db->Query('INSERT INTO  {dependencies} (task_id, dep_task_id)
                         VALUES  (?,?)',
                    array($task['task_id'], Post::val('dep_task_id')));

        $_SESSION['SUCCESS'] = L('dependadded');
        break;

    // ##################
    // removing a dependency
    // ##################
    case 'removedep':
        if (!$user->can_edit_task($task)) {
            break;
        }
        
        $result = $db->Query('SELECT  * FROM {dependencies}
                               WHERE  depend_id = ?',
                            array(Get::val('depend_id')));
        $dep_info = $db->FetchRow($result);
        
        $db->Query('DELETE FROM {dependencies} WHERE depend_id = ? AND task_id = ?',
                    array(Get::val('depend_id'), $task['task_id']));
        
        if ($db->AffectedRows()) {
            $notify->Create(NOTIFY_DEP_REMOVED, $dep_info['task_id'], $dep_info['dep_task_id']);
            $notify->Create(NOTIFY_REV_DEP_REMOVED, $dep_info['dep_task_id'], $dep_info['task_id']);

            Flyspray::logEvent($dep_info['task_id'], 24, $dep_info['dep_task_id']);
            Flyspray::logEvent($dep_info['dep_task_id'], 25, $dep_info['task_id']);
        }

        $_SESSION['SUCCESS'] = L('depremovedmsg');
        break;

    // ##################
    // user requesting a password change
    // ##################
    case 'lostpw.sendmagic':
        // Check that the username exists
        $sql = $db->Query('SELECT * FROM {users} WHERE user_name = ?',
                          array(Post::val('user_name')));

        // If the username doesn't exist, throw an error
        if (!$db->CountRows($sql)) {
            Flyspray::show_error(L('usernotexist'));
            break;
        }

        $user_details = $db->FetchRow($sql);
        //no microtime(), time,even with microseconds is predictable ;-)
        $magic_url    = md5(uniqid(rand(), true));

        // Insert the random "magic url" into the user's profile
        $db->Query('UPDATE {users}
                       SET magic_url = ?
                     WHERE user_id = ?',
                array($magic_url, $user_details['user_id']));

        $notify->Create(NOTIFY_PW_CHANGE, null, array($baseurl, $magic_url), $notify->SpecificAddresses(array($user_details['user_id']), true));

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

        if (Post::val('pass1') != Post::val('pass2')) {
            Flyspray::show_error(L('passnomatch'));
            break;
        }

        $new_pass_hash = Flyspray::cryptPassword(Post::val('pass1'));
        $db->Query("UPDATE  {users} SET user_pass = ?, magic_url = ''
                     WHERE  magic_url = ?",
                   array($new_pass_hash, Post::val('magic_url')));

        $_SESSION['SUCCESS'] = L('passchanged');
        Flyspray::Redirect('./');
        break;

    // ##################
    // making a task private
    // ##################
    case 'makeprivate':
        if (!$user->perms('manage_project')) {
            break;
        }
        
        $db->Query('UPDATE  {tasks}
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
        
        $db->Query('UPDATE  {tasks}
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
}

?>
