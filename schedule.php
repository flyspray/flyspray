<?php

  /********************************************************\
  | Scheduled Jobs (poor man's cron)                       |
  | ~~~~~~~~~~~~~~                                         |
  | This script checks for pending scheduled notifications |
  | and sends them at the right time.                      |
  \********************************************************/

define('IN_FS', true);

if(php_sapi_name() !== 'cli') {
    die("Reminder daemon must run in the CLI SAPI only");
}

require_once 'header.php';
include_once BASEDIR . '/includes/class.notify.php';

function send_reminders()
{
    global $db, $fs;
    //we touch the file on every single iteration to avoid
    //the possible restart done by Startremiderdaemon method
    //in class.flyspray.conf


$notify =& new Notifications;
$user =& new User(0);
$now = time();

$get_reminders = $db->Query("SELECT  r.*, t.*, p.*
                               FROM  {reminders} r
                         INNER JOIN  {users}     u ON u.user_id = r.to_user_id
                         INNER JOIN  {tasks}     t ON r.task_id = t.task_id
                         INNER JOIN  {projects}  p ON t.project_id = p.project_id
                              WHERE  t.is_closed = '0' AND r.start_time < ?
                                                       AND r.last_sent + r.how_often < ?
                           ORDER BY  r.reminder_id", array(time(), time())
                        );

while ($row = $db->FetchRow($get_reminders)) {
   $jabber_users = array();
   $email_users  = array();

   // Get the user's notification type and address
   $get_details  = $db->Query("SELECT  notify_type, jabber_id, email_address
                                 FROM  {users}
                                WHERE  user_id = ?", array($row['to_user_id']));

   while ($subrow = $db->FetchRow($get_details)) {
      if (($fs->prefs['user_notify'] == '1' && $subrow['notify_type'] == '1')
      OR ($fs->prefs['user_notify'] == '2'))
      {
         $email_users[] = $subrow['email_address'];

      }
      elseif (($fs->prefs['user_notify'] == '1' && $subrow['notify_type'] == '2')
      OR ($fs->prefs['user_notify'] == '3'))
      {
         $jabber_users[] = $subrow['jabber_id'];
      }
   }

   $subject = L('notifyfromfs');
   $message = $row['reminder_message'];

   // Pass the recipients and message onto the notification function
   $notify->SendEmail($email_users, $subject, $message);
   $notify->StoreJabber($jabber_users, $subject, $message);

   // Update the database with the time sent
   $update_db = $db->Query("UPDATE  {reminders}
                               SET  last_sent = ?
                             WHERE  reminder_id = ?",
                            array(time(), $row['reminder_id']));

 }
	// send those stored notifications
$notify->SendJabber();
unset($notify, $user);

}

if(isset($conf['general']['reminder_daemon']) && ($conf['general']['reminder_daemon'] == 1)) {

    send_reminders();

} else {

    die("reminder daemon is disabled");
}

?>
