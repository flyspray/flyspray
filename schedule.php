<?php

  /********************************************************\
  | Scheduled Jobs (poor man's cron)                       |
  | ~~~~~~~~~~~~~~                                         |
  | This script checks for pending scheduled notifications |
  | and sends them at the right time.                      |
  \********************************************************/

define('IN_FS', true);

/**
 * Developers warning :
 * Be aware while debugging this, it actually daemonize ¡¡
 * it runs **forever** in the background every ten minutes
 * to simulate a real cron task, it WONT STOP if you click
 * stop in your browser, it will only stop if you restart
 * your webserver.
 */

require_once 'header.php';
include_once BASEDIR . '/includes/class.notify.php';

function send_reminders()
{
    global $db;
    //we touch the file on every single iteration to avoid
    //the possible restart done by Startremiderdaemon method
    //in class.flyspray.conf
touch(Flyspray::get_tmp_dir() . '/flysprayreminders.run');


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
unlink(Flyspray::get_tmp_dir() . '/flysprayreminders.run');

}


if(isset($conf['general']['reminder_daemon']) && in_array($conf['general']['reminder_daemon'], range(1, 2))) {

	if(php_sapi_name() === 'cli') {
		//once
		send_reminders();

    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] == '127.0.0.1'
              && $conf['general']['reminder_daemon'] == '2') {

		//keep going, execute the script in the background
		ignore_user_abort(true);
		set_time_limit(0);

        do {

			send_reminders();
			//wait 10 minutes for the next loop.
			sleep(600);

        //forever ¡¡¡ ( oh well. a least will not stop unless killed or the server restarted)
		} while(true);
         
       } else {

    		die("you are not authorized to start the reminder daemon\n");
	}
} else {

    die("reminder is disabled...not running..\n");
}
?>
