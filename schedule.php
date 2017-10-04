<?php

/* * ******************************************************\
  | Scheduled Jobs                                         |
  |                                                        |
  | This script checks for pending scheduled notifications |
  | and sends them at the right time.                      |
  |                                                        |
  | Setup a cronjob that calls this file on a regular basis|
  | (crontab -l to view, crontab -e to edit on linux)
  | Example for a cronjob entry:                           |
  |                                                        |
  | 7  * * * * php flyspray/schedule.php                   |
  |                                                        |
  | Similiar setup possible with Windows Task Scheduler(MS)|
  \****************************************************** */

define('IN_FS', true);

if (PHP_SAPI !== 'cli') {
  die("Scheduler must be called from the CLI SAPI only, for instance by a cronjob.");
}

require_once 'header.php';
include_once BASEDIR . '/includes/class.notify.php';

function send_reminders() {
  global $db, $fs, $proj;

  $notify = new Notifications;
  $user = new User(0);
  $now = time();

  $get_reminders = $db->Query("SELECT r.*, t.*, u.*
              FROM {reminders} r
              INNER JOIN {users}    u ON u.user_id = r.to_user_id
              INNER JOIN {tasks}    t ON r.task_id = t.task_id
              INNER JOIN {projects} p ON t.project_id = p.project_id
              WHERE t.is_closed = '0'
              AND r.start_time < ?
              AND r.last_sent + r.how_often < ?
              ORDER BY r.reminder_id", array($now, $now)
  );

  while ($row = $db->FetchRow($get_reminders)) {
    // So that the sender in emails will is the right project, not 'Default project'
    // and also to get the projects default language, if needed.
    $proj = new Project($row['project_id']);
    $jabber_users = array();
    $email_users = array();

    if (( $fs->prefs['user_notify'] == 1 || $fs->prefs['user_notify'] == 2 ) && ($row['notify_type'] == 1 || $row['notify_type'] == 3 )) {
      $email_users[] = $row['email_address'];
    }

    if (( $fs->prefs['user_notify'] == 1 || $fs->prefs['user_notify'] == 3 ) && ($row['notify_type'] == 2 || $row['notify_type'] == 3 )) {
      $jabber_users[] = $row['jabber_id'];
    }

    if (!empty($row['lang_code'])) {
      $lang = $row['lang_code'];
    } else if (!empty($proj->prefs['lang_code'])) {
      $lang = $proj->prefs['lang_code'];
    } else {
      $lang = $fs->prefs['lang_code'];
    }

    $subject = tL('notifyfromfs', $lang);
    $message = $row['reminder_message'];

    // Pass the recipients and message onto the notification function
    $notify->SendEmail($email_users, $subject, $message);
    $notify->StoreJabber($jabber_users, $subject, $message);

    // Update the database with the time sent
    $update_db = $db->Query("UPDATE {reminders}
      SET last_sent = ?
      WHERE reminder_id = ?", array(time(), $row['reminder_id']));
  }

  // send those stored notifications
  $notify->SendJabber();
  unset($notify, $user);
}

if (isset($conf['general']['reminder_daemon']) && ($conf['general']['reminder_daemon'] == 1)) {
  send_reminders();
} else {
  die("Scheduled Reminding is disabled.");
}
?>
