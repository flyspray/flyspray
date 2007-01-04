<?php

/*
   ---------------------------------------------------
   | This script contains the notification functions |
   ---------------------------------------------------
*/

/**
 * Notifications 
 * 
 * @package 
 * @version $Id$
 * @copyright 2006 Flyspray.org
 * @notes: This is a mess and should be replaced for 1.0
 */

class Notifications {

   // {{{ Wrapper function for all others 
   function Create ( $type, $task_id, $info = null, $to = null, $ntype = NOTIFY_BOTH)
   {
      if (is_null($to)) {
          $to = $this->Address($task_id, $type);
      }
      
      settype($to, 'array');

      $msg = $this->GenerateMsg($type, $task_id, $info);
      
      if ($ntype == NOTIFY_EMAIL || $ntype == NOTIFY_BOTH) {
          if(!$this->SendEmail($to[0], $msg[0], $msg[1], $task_id)) {
              return false;
          }
      }
      if ($ntype == NOTIFY_JABBER || $ntype == NOTIFY_BOTH) {
          if(!$this->StoreJabber($to[1], $msg[0], $msg[1])) {
              return false;
          }
      } 
            return true;

   // End of Create() function
   } // }}}
   // {{{ Store Jabber messages for sending later 
   function StoreJabber( $to, $subject, $body )
   {
      global $db, $fs;

      if (empty($fs->prefs['jabber_server'])
          || empty($fs->prefs['jabber_port'])
          || empty($fs->prefs['jabber_username'])
          || empty($fs->prefs['jabber_password'])) {
            return false;
      }

      if (empty($to)) {
         return false;
      }

      $date = time();

      // store notification in table
      $db->Query("INSERT INTO {notification_messages}
                  (message_subject, message_body, time_created)
                  VALUES (?, ?, ?)",
                  array($subject, $body, $date)
                );

      // grab notification id
      $result = $db->Query("SELECT message_id FROM {notification_messages}
                            WHERE message_subject = ?
                            AND message_body = ?
                            AND time_created = ?",
                            array($subject, $body, $date)
                          );

      $row = $db->FetchRow($result);
      $message_id = $row['message_id'];
      
      // If message could not be inserted for
      // whatever reason...
      if (!$message_id) {
          return false;
      }
    
      // make sure every email address is only added once
      settype($to, 'array');
      $to = array_unique($to);

      foreach ($to as $jid)
      {
         // store each recipient in table
         $db->Query("INSERT INTO {notification_recipients}
                     (notify_method, message_id, notify_address)
                     VALUES (?, ?, ?)",
                     array('j', $message_id, $jid)
                    );

      }

      return TRUE;
   } // }}} 
   // {{{ Send Jabber messages that were stored earlier
   function SendJabber()
   {
      global $db, $fs;

      include_once BASEDIR . '/includes/class.jabber2.php';
      

      if (empty($fs->prefs['jabber_server'])
          || empty($fs->prefs['jabber_port'])
          || empty($fs->prefs['jabber_username'])
          || empty($fs->prefs['jabber_password'])) {
            return false;
      }

      $JABBER = new Jabber($fs->prefs['jabber_username'] . '@' . $fs->prefs['jabber_server'], 
                           $fs->prefs['jabber_password'],
                           $fs->prefs['jabber_ssl'],
                           $fs->prefs['jabber_port']);

      $JABBER->log_enabled = defined('JABBER_DEBUG');

      // get listing of all pending jabber notifications
      $result = $db->Query("SELECT DISTINCT message_id
                            FROM {notification_recipients}
                            WHERE notify_method='j'");

      if (!$db->CountRows($result))
      {
         $JABBER->log("No notifications to send");
         return false;
      }

      // we have notifications to process - connect
      $JABBER->log("We have notifications to process...");
      $JABBER->log("Starting Jabber session:");

      while ( $row = $db->FetchRow($result) )
      {
         $ids[] = $row['message_id'];
      }

      $desired = join(",", $ids);
      $JABBER->log("message ids to send = {" . $desired . "}");

      // removed array usage as it's messing up the select
      // I suspect this is due to the variable being comma separated
      // Jamin W. Collins 20050328
      $notifications = $db->Query("SELECT * FROM {notification_messages}
                                   WHERE message_id in ($desired)
                                   ORDER BY time_created ASC"
                                 );

      $JABBER->log("number of notifications {" . $db->CountRows($notifications) . "}");

      // loop through notifications
      while ( $notification = $db->FetchRow($notifications) )
      {
         $subject = $notification['message_subject'];
         $body    = $notification['message_body'];

         $JABBER->log("Processing notification {" . $notification['message_id'] . "}");

            $recipients = $db->Query("SELECT * FROM {notification_recipients}
                                      WHERE message_id = ?
                                      AND notify_method = 'j'",
                                      array($notification['message_id'])
                                    );

            // loop through recipients
            while ($recipient = $db->FetchRow($recipients) )
            {
               $jid = $recipient['notify_address'];
               $JABBER->log("- attempting send to {" . $jid . "}");

               // send notification
               if ($JABBER->send_message($jid, $body, 'normal'))
               {
                   // delete entry from notification_recipients
                   $result = $db->Query("DELETE FROM {notification_recipients}
                                         WHERE message_id = ?
                                         AND notify_method = 'j'
                                         AND notify_address = ?",
                                         array($notification['message_id'], $jid)
                                       );
                   $JABBER->log("- notification sent");
                } else {
                   $JABBER->log("- notification not sent");
                }
            }
            // check to see if there are still recipients for this notification
            $result = $db->Query("SELECT * FROM {notification_recipients}
                                  WHERE message_id = ?",
                                  array($notification['message_id'])
                                );

            if ( $db->CountRows($result) == 0 )
            {
               $JABBER->log("No further recipients for message id {" . $notification['message_id'] . "}");
               // remove notification no more recipients
               $result = $db->Query("DELETE FROM {notification_messages}
                                     WHERE message_id = ?",
                                     array($notification['message_id'])
                                   );
               $JABBER->log("- Notification deleted");
            }
         }

         // disconnect from server
         $JABBER->disconnect();
         $JABBER->log("Disconnected from Jabber server");

      return true;
   } // }}} 
   // {{{ Send email 
   function SendEmail($to, $subject, $body, $task_id)
   {
      global $fs, $proj, $user;

      if (empty($to) || empty( $to[0] ) || ($to == $user->id && !$user->infos['notify_own'])) {
         return;
      }

      // Get the new email class
      require_once dirname(__FILE__) .'/external/swift-mailer/class.phpmailer.php';

      // Define the class
      $mail = new PHPMailer();

      $mail->From = trim($fs->prefs['admin_email']);
      $mail->Sender = trim($fs->prefs['admin_email']);
      if ($proj->prefs['notify_reply']) {
          $mail->AddReplyTo(trim($proj->prefs['notify_reply']));
      }
      $mail->FromName = 'Flyspray';
      $mail->CharSet = 'UTF-8';

      // Do we want to use a remote mail server?
      if (!empty($fs->prefs['smtp_server']))
      {
         $mail->IsSMTP();
         $mail->Host = $fs->prefs['smtp_server'];
         if (!empty($fs->prefs['smtp_user']))
         {
            $mail->SMTPAuth = true;     // turn on SMTP authentication
            $mail->Username = $fs->prefs['smtp_user'];  // SMTP username
            $mail->Password = $fs->prefs['smtp_pass']; // SMTP password
         }

      // Use php's built-in mail() function
      } else {
         $mail->IsMail();
      }

      if (is_array($to))
      {
         // do not disclose user's address
         $mail->AddAddress($fs->prefs['admin_email']);
         // make sure every email address is only added once
         $to = array_map('trim', array_unique($to));

         foreach ($to as $val) {
             //do not send email twice to admin
             if($val === $fs->prefs['admin_email']) {
                 continue;
             }
            // Unlike the docs say, it *does (appear to)* work with mail()
            $mail->AddBcc($val);
         }

      } else {
         $mail->AddAddress(trim($to));
      }

      $mail->WordWrap = 70; // 70 characters
      $mail->Subject = trim($subject);
      $mail->Body = $body;

      if($task_id) {

          $inreplyto = '<FS' . intval($task_id) . '@' . parse_url($GLOBALS['baseurl'], PHP_URL_HOST) . '>';
          $mail->AddCustomHeader('In-Reply-To: ' . $inreplyto);
      }

      if (!$mail->Send()) {
         // Flyspray::show_error(21, false, $mail->ErrorInfo);
          return false;
      }

        return true;

   } //   }}} 
   // {{{ Create a message for any occasion
   function GenerateMsg($type, $task_id, $arg1='0')
   {
      global $db, $fs, $user, $proj;

      // Get the task details
      $task_details = Flyspray::getTaskDetails($task_id);
      if ($task_id) {
          $proj = new Project($task_details['project_id']);
      }

      // Set the due date correctly
      if ($task_details['due_date'] == '0') {
         $due_date = L('undecided');
      } else {
         $due_date = formatDate($task_details['due_date']);
      }

      // Set the due version correctly
      if ($task_details['closedby_version'] == '0') {
         $task_details['due_in_version_name'] = L('undecided');
      }

      // Get the string of modification
      $notify_type_msg = array(
      	0 => L('none'),
        NOTIFY_TASK_OPENED     => L('taskopened'),
        NOTIFY_TASK_CHANGED    => L('pm.taskchanged'),
        NOTIFY_TASK_CLOSED     => L('taskclosed'),
        NOTIFY_TASK_REOPENED   => L('pm.taskreopened'),
        NOTIFY_DEP_ADDED       => L('pm.depadded'),
        NOTIFY_DEP_REMOVED     => L('pm.depremoved'),
        NOTIFY_COMMENT_ADDED   => L('commentadded'),
        NOTIFY_ATT_ADDED       => L('attachmentadded'),
        NOTIFY_REL_ADDED       => L('relatedadded'),
        NOTIFY_OWNERSHIP       => L('ownershiptaken'),
        NOTIFY_PM_REQUEST      => L('pmrequest'),
        NOTIFY_PM_DENY_REQUEST => L('pmrequestdenied'),
        NOTIFY_NEW_ASSIGNEE    => L('newassignee'),
        NOTIFY_REV_DEP         => L('revdepadded'),
        NOTIFY_REV_DEP_REMOVED => L('revdepaddedremoved'),
        NOTIFY_ADDED_ASSIGNEES => L('assigneeadded'),
      );

      // Generate the nofication message
      if ($type == NOTIFY_CONFIRMATION || $type == NOTIFY_ANON_TASK || $type == NOTIFY_PW_CHANGE || $type == NOTIFY_NEW_USER) {
          $subject = L('notifyfromfs');
      } else if ($proj->prefs['notify_subject']) {
          $subject = str_replace(array('%p','%s','%t', '%a'),
                                    array($proj->prefs['project_title'], $task_details['item_summary'], $task_id, $notify_type_msg[$type]),
                                    $proj->prefs['notify_subject']);
      } else {
          $subject = L('notifyfrom') . $proj->prefs['project_title'];
      }
      
      $subject = strtr($subject, "\r\n", '');


      /* -------------------------------
         | List of notification types: |
         | 1. Task opened              |
         | 2. Task details changed     |
         | 3. Task closed              |
         | 4. Task re-opened           |
         | 5. Dependency added         |
         | 6. Dependency removed       |
         | 7. Comment added            |
         | 8. Attachment added         |
         | 9. Related task added       |
         |10. Taken ownership          |
         |11. Confirmation code        |
         |12. PM request               |
         |13. PM denied request        |
         |14. New assignee             |
         |15. Reversed dep             |
         |16. Reversed dep removed     |
         |17. Added to assignees list  |
         |18. Anon-task opened         |
         |19. Password change          |
         |20. New user                 |
         -------------------------------
      */
      
      $body = L('donotreply') . "\r\n\r\n";
      // {{{ New task opened
      if ($type == NOTIFY_TASK_OPENED)
      {
         $body .=  L('newtaskopened') . "\r\n\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n\r\n";
         $body .= L('attachedtoproject') . ' - ' .  $task_details['project_title'] . "\r\n";
         $body .= L('summary') . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('tasktype') . ' - ' . $task_details['tasktype_name'] . "\r\n";
         $body .= L('category') . ' - ' . $task_details['category_name'] . "\r\n";
         $body .= L('status') . ' - ' . $task_details['status_name'] . "\r\n";
         $body .= L('assignedto') . ' - ' . implode(', ', $task_details['assigned_to_name']) . "\r\n";
         $body .= L('operatingsystem') . ' - ' . $task_details['os_name'] . "\r\n";
         $body .= L('severity') . ' - ' . $task_details['severity_name'] . "\r\n";
         $body .= L('priority') . ' - ' . $task_details['priority_name'] . "\r\n";
         $body .= L('reportedversion') . ' - ' . $task_details['reported_version_name'] . "\r\n";
         $body .= L('dueinversion') . ' - ' . $task_details['due_in_version_name'] . "\r\n";
         $body .= L('duedate') . ' - ' . $due_date . "\r\n";
         $body .= L('details') . ' - ' . $task_details['detailed_desc'] . "\r\n\r\n";
         $body .= L('moreinfo') . "\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n";
      } // }}}
      // {{{ Task details changed
      if ($type == NOTIFY_TASK_CHANGED)
      {
         $translation = array('priority_name' => L('priority'),
                              'severity_name' => L('severity'),
                              'status_name'   => L('status'),
                              'assigned_to_name' => L('assignedto'),
                              'due_in_version_name' => L('dueinversion'),
                              'reported_version_name' => L('reportedversion'),
                              'tasktype_name' => L('tasktype'),
                              'os_name' => L('operatingsystem'),
                              'category_name' => L('category'),
                              'due_date' => L('duedate'),
                              'percent_complete' => L('percentcomplete'),
                              'mark_private' => L('visibility'),
                              'item_summary' => L('summary'),
                              'detailed_desc' => L('taskedited'),
                              'project_title' => L('attachedtoproject'));
                              
         $body .= L('taskchanged') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ': ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n";
         
         foreach($arg1 as $change)
         {
            if($change[0] == 'assigned_to_name') {
                $change[1] = implode(', ', $change[1]);
                $change[2] = implode(', ', $change[2]);
            }
            
            if($change[0] == 'detailed_desc') {
                $body .= $translation[$change[0]] . ":\r\n-------\r\n" . $change[2] . "\r\n-------\r\n";
            } else {
                $body .= $translation[$change[0]] . ': ' . ( ($change[1]) ? $change[1] : '[-]' ) . ' -> ' . ( ($change[2]) ? $change[2] : '[-]' ) . "\r\n";
            }
         }
         $body .= "\r\n" . L('moreinfo') . "\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n";
      } // }}}
      // {{{ Task closed
      if ($type == NOTIFY_TASK_CLOSED)
      {
         $body .=  L('notify.taskclosed') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n\r\n";
         $body .= L('reasonforclosing') . ' ' . $task_details['resolution_name'] . "\r\n";

         if (!empty($task_details['closure_comment']))
         {
            $body .= L('closurecomment') . ' ' . $task_details['closure_comment'] . "\r\n\r\n";
         }

         $body .= L('moreinfo') . "\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n";
      } // }}}
      // {{{ Task re-opened
      if ($type == NOTIFY_TASK_REOPENED)
      {
         $body .=  L('notify.taskreopened') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] .  ")\r\n\r\n";
         $body .= L('moreinfo') . "\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n";
      } // }}}
      // {{{ Dependency added
      if ($type == NOTIFY_DEP_ADDED)
      {
         $depend_task = Flyspray::getTaskDetails($arg1);

         $body .=  L('newdep') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n\r\n";
         $body .= L('newdepis') . ':' . "\r\n\r\n";
         $body .= 'FS#' . $depend_task['task_id'] . ' - ' .  $depend_task['item_summary'] . "\r\n";
         $body .= CreateURL('details', $depend_task['task_id']) . "\r\n\r\n";
      } // }}}
      // {{{ Dependency removed
      if ($type == NOTIFY_DEP_REMOVED)
      {
         $depend_task = Flyspray::getTaskDetails($arg1);
         
         $body .= L('notify.depremoved') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n\r\n";
         $body .= L('removeddepis') . ':' . "\r\n\r\n";
         $body .= 'FS#' . $depend_task['task_id'] . ' - ' .  $depend_task['item_summary'] . "\r\n";
         $body .= CreateURL('details', $depend_task['task_id']) . "\r\n\r\n";         
      } // }}}
      // {{{ Comment added
      if ($type == NOTIFY_COMMENT_ADDED)
      {
         // Get the comment information
         $result = $db->Query("SELECT comment_id, comment_text
                               FROM {comments}
                               WHERE user_id = ?
                               AND task_id = ?
                               ORDER BY comment_id DESC",
                               array($user->id, $task_id), '1');
         $comment = $db->FetchRow($result);
         
         $body .= L('notify.commentadded') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n\r\n";
         $body .= "----------\r\n";
         $body .= $comment['comment_text'] . "\r\n";
         $body .= "----------\r\n\r\n";

         if ($arg1 == 'files') {
            $body .= L('fileaddedtoo') . "\r\n\r\n";
         }

         $body .= L('moreinfo') . "\r\n";
         $body .= CreateURL('details', $task_id) . '#comment' . $comment['comment_id'] . "\r\n\r\n";
      } // }}}
      // {{{ Attachment added
      if ($type == NOTIFY_ATT_ADDED)
      {
         $body .= L('newattachment') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n\r\n";
         $body .= L('moreinfo') . "\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n";
      } // }}}
      // {{{ Related task added
      if ($type == NOTIFY_REL_ADDED)
      {
         $related_task = Flyspray::getTaskDetails($arg1);

         $body .= L('notify.relatedadded') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n\r\n";
         $body .= L('relatedis') . ':' . "\r\n\r\n";
         $body .= 'FS#' . $related_task['task_id'] . ' - ' . $related_task['item_summary'] . "\r\n";
         $body .= CreateURL('details', $related_task['task_id']) . "\r\n\r\n";
      } // }}}
      // {{{ Ownership taken
      if ($type == NOTIFY_OWNERSHIP)
      {
         $body .= implode(', ', $task_details['assigned_to_name']) . ' ' . L('takenownership') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n\r\n";
         $body .= L('moreinfo') . "\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n";
      } // }}}
      // {{{ Confirmation code
      if ($type == NOTIFY_CONFIRMATION)
      {
         $body .= L('noticefrom') . " {$proj->prefs['project_title']}\r\n\r\n"
               . L('addressused') . "\r\n\r\n"
               . "{$arg1[0]}index.php?do=register&magic_url={$arg1[1]}\r\n\r\n"
                // In case that spaces in the username have been removed
               . L('username') . ': '. $arg1[2] . "\r\n"
               . L('confirmcodeis') . " $arg1[3] \r\n\r\n";
      } // }}}
      // {{{ Pending PM request
      if ($type == NOTIFY_PM_REQUEST)
      {
         $body .= L('requiresaction') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n\r\n";
         $body .= L('moreinfo') . "\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n";
      } // }}}
      // {{{ PM request denied
      if ($type == NOTIFY_PM_DENY_REQUEST)
      {
         $body .= L('pmdeny') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n\r\n";
         $body .= L('denialreason') . ':' . "\r\n";
         $body .= $arg1 . "\r\n\r\n";
         $body .= L('moreinfo') . "\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n";
      } // }}}
      // {{{ New assignee
      if ($type == NOTIFY_NEW_ASSIGNEE)
      {
         $body .= L('assignedtoyou') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n\r\n";
         $body .= L('moreinfo') . "\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n";
      } // }}}
      // {{{ Reversed dep
      if ($type == NOTIFY_REV_DEP)
      {
         $depend_task = Flyspray::getTaskDetails($arg1);

         $body .= L('taskwatching') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n\r\n";
         $body .= L('isdepfor') . ':' . "\r\n\r\n";
         $body .= 'FS#' . $depend_task['task_id'] . ' - ' .  $depend_task['item_summary'] . "\r\n";
         $body .= CreateURL('details', $depend_task['task_id']) . "\r\n\r\n";
      } // }}}
      // {{{ Reversed dep - removed
      if ($type == NOTIFY_REV_DEP_REMOVED)
      {
         $depend_task = Flyspray::getTaskDetails($arg1);
         
         $body .= L('taskwatching') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n\r\n";
         $body .= L('isnodepfor') . ':' . "\r\n\r\n";
         $body .= 'FS#' . $depend_task['task_id'] . ' - ' .  $depend_task['item_summary'] . "\r\n";
         $body .= CreateURL('details', $depend_task['task_id']) . "\r\n\r\n";
      } // }}}
      // {{{ User added to assignees list
      if ($type == NOTIFY_ADDED_ASSIGNEES)
      {
         $body .= L('useraddedtoassignees') . "\r\n\r\n";
         $body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\r\n";
         $body .= L('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\r\n";
         $body .= CreateURL('details', $task_id) . "\r\n\r\n\r\n";
      } // }}}
      // {{{ Anon-task has been opened
      if ($type == NOTIFY_ANON_TASK)
      {
         $body .= L('thankyouforbug') . "\r\n\r\n";
         $body .= CreateURL('details', $task_id, null, array('task_token' => $arg1)) . "\r\n\r\n";
      } // }}}
      // {{{ Password change
      if ($type == NOTIFY_PW_CHANGE)
      {
          $body = L('messagefrom'). $arg1[0] . "\r\n\r\n"
                  . L('magicurlmessage')." \r\n"
                  . "{$arg1[0]}index.php?do=lostpw&magic_url=$arg1[1]\r\n";
      } // } }}
      // {{{ New user
      if ($type == NOTIFY_NEW_USER)
      {
          $body = L('messagefrom'). $arg1[0] . "\r\n\r\n"
                  . L('newuserregistered')." \r\n\r\n"
                  . L('username') . ': ' . $arg1[1] . "\r\n" .
                    L('realname') . ': ' . $arg1[2] . "\r\n";
          if ($arg1[6]) {
              $body .= L('password') . ': ' . $arg1[5] . "\r\n";
          }
              $body .= L('emailaddress') . ': ' . $arg1[3] . "\r\n" .
                    L('jabberid') . ':' . $arg1[4] . "\r\n\r\n";
      } // }}}
      
      $body .= L('disclaimer');
      return array($subject, $body);
   
   } // }}} 
   // {{{ Create an address list for specific users
   function SpecificAddresses($users, $ignoretype = false)
   {
        global $db, $fs, $user;

        $jabber_users = array();
        $email_users = array();
        settype($users, 'array');
        
        if (count($users) < 1) {
            return array();
        }

        $sql = $db->Query('SELECT *
                             FROM {users}
                            WHERE' . substr(str_repeat(' user_id = ? OR ', count($users)), 0, -3),
                           array_values($users));
                         
        while ($user_details = $db->FetchRow($sql))
        {
            if ($user_details['user_id'] == $user->id && !$user->infos['notify_own']) {
                continue;
            }
            
            if ( ($fs->prefs['user_notify'] == '1' && ($user_details['notify_type'] == NOTIFY_EMAIL || $user_details['notify_type'] == NOTIFY_BOTH) )
                || $fs->prefs['user_notify'] == '2' || $ignoretype)
            {
                array_push($email_users, $user_details['email_address']);

            }

            if ( ($fs->prefs['user_notify'] == '1' && ($user_details['notify_type'] == NOTIFY_JABBER || $user_details['notify_type'] == NOTIFY_BOTH) )
                || $fs->prefs['user_notify'] == '3' || $ignoretype)
            {
                array_push($jabber_users, $user_details['jabber_id']);
            }
        }

        return array($email_users, $jabber_users);

   } // }}}
   // {{{ Create a standard address list of users (assignees, notif tab and proj addresses)
   function Address($task_id, $type)
   {
      global $db, $fs, $proj, $user;

      $users = array();

      $jabber_users = array();
      $email_users = array();

      $task_details = Flyspray::GetTaskDetails($task_id);

      // Get list of users from the notification tab
      $get_users = $db->Query("SELECT *
                               FROM {notifications} n
                               LEFT JOIN {users} u ON n.user_id = u.user_id
                               WHERE n.task_id = ?",
                               array($task_id));

      while ($row = $db->FetchRow($get_users))
      {
         if ($row['user_id'] == $user->id && !$user->infos['notify_own']) {
            continue;
         }
        
         if ( ($fs->prefs['user_notify'] == '1' && ($row['notify_type'] == NOTIFY_EMAIL || $row['notify_type'] == NOTIFY_BOTH) )
             || $fs->prefs['user_notify'] == '2')
         {
               array_push($email_users, $row['email_address']);

         }
         
         if ( ($fs->prefs['user_notify'] == '1' && ($row['notify_type'] == NOTIFY_JABBER || $row['notify_type'] == NOTIFY_BOTH) )
             || $fs->prefs['user_notify'] == '3')
         {
               array_push($jabber_users, $row['jabber_id']);
         }
      }

      // Get list of assignees
      $get_users = $db->Query("SELECT *
                               FROM {assigned} a
                               LEFT JOIN {users} u ON a.user_id = u.user_id
                               WHERE a.task_id = ?",
                               array($task_id));

      while ($row = $db->FetchRow($get_users))
      {
         if ($row['user_id'] == $user->id && !$user->infos['notify_own']) {
            continue;
         }
         
         if ( ($fs->prefs['user_notify'] == '1' && ($row['notify_type'] == NOTIFY_EMAIL || $row['notify_type'] == NOTIFY_BOTH) )
             || $fs->prefs['user_notify'] == '2')
         {
               array_push($email_users, $row['email_address']);

         }
         
         if ( ($fs->prefs['user_notify'] == '1' && ($row['notify_type'] == NOTIFY_JABBER || $row['notify_type'] == NOTIFY_BOTH) )
             || $fs->prefs['user_notify'] == '3')
         {
               array_push($jabber_users, $row['jabber_id']);
         }
      }

      // Now, we add the project contact addresses...
      // ...but only if the task is public
      $task_details = Flyspray::getTaskDetails($task_id);
      if ($task_details['mark_private'] != '1' && in_array($type, Flyspray::int_explode(' ', $proj->prefs['notify_types'])))
      {
         $proj_emails = preg_split('/[\s,;]+/', $proj->prefs['notify_email'], -1, PREG_SPLIT_NO_EMPTY);
         $proj_jids = explode(',', $proj->prefs['notify_jabber']);

         foreach ($proj_emails as $key => $val)
         {
            if (!empty($val) && !in_array($val, $email_users))
               array_push($email_users, $val);
         }

         foreach ($proj_jids as $key => $val)
         {
            if (!empty($val) && !in_array($val, $jabber_users))
               array_push($jabber_users, $val);
         }

      // End of checking if a task is private
      }

      // Send back two arrays containing the notification addresses
      return array($email_users, $jabber_users);

   } // }}}

// End of Notify class
}

?>
