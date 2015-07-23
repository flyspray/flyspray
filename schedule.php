<?php

/* * ******************************************************\
  | Scheduled Jobs (poor man's cron)                       |
  | ~~~~~~~~~~~~~~                                         |
  | This script checks for pending scheduled notifications |
  | and sends them at the right time.                      |
  \******************************************************* */

define('IN_FS', true);

if (php_sapi_name() !== 'cli') {
    die("Reminder daemon must run in the CLI SAPI only");
}

require_once 'header.php';
include_once BASEDIR . '/includes/class.notify.php';

function send_reminders() {
    global $db, $fs, $proj;
    //we touch the file on every single iteration to avoid
    //the possible restart done by Startremiderdaemon method
    //in class.flyspray.conf


    $notify = & new Notifications;
    $user = & new User(0);
    $now = time();
    $languages = array(); // Is this the right place? Move inside while loop if it's not...

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
        // So that the sender in emails will be the right project, not 'Default project'
        // and also to get the projects default language, if needed.
        $proj = new Project($row['project_id']);
        $jabber_users = array();
        $email_users = array();
        $jabbers = array();
        $emails = array();
        $jabbers_to_send = array();
        $emails_to_send = array();

        // Get the user's notification type, address, desired message and preferred language
        $get_details = $db->Query("SELECT  notify_type, jabber_id, email_address, lang_code
                                 FROM  {users}
                                WHERE  user_id = ?", array($row['to_user_id']));

        while ($subrow = $db->FetchRow($get_details)) {
            if (($fs->prefs['user_notify'] == '1' && $subrow['notify_type'] == '1')
                    OR ( $fs->prefs['user_notify'] == '2')) {
                // $email_users[] = $subrow['email_address'];
                $email_users[] = array('recipient' => $subrow['email_address'],
                    'lang' => $subrow['lang_code'],
                    'message' => $row['reminder_message']);
            } elseif (($fs->prefs['user_notify'] == '1' && $subrow['notify_type'] == '2')
                    OR ( $fs->prefs['user_notify'] == '3')) {
                // $jabber_users[] = $subrow['jabber_id'];
                $jabber_users[] = array('recipient' => $subrow['email_address'],
                    'lang' => $subrow['lang_code'],
                    'message' => $row['reminder_message']);
            }
        }

        // Ok, we've got the recipients collected with their preferred messages and preferred languages.
        // Next, sort them by language so we get also the message subject right. Send emails immediately
        // while the project is still the right one to get also the sender right, just store jabber messages.
        foreach ($email_users as $recipient) {
            if (!empty($recipient['lang'])) {
                $lang = $recipient['lang'];
            } else if (!empty($proj->prefs['lang_code'])) {
                $lang = $proj->prefs['lang_code'];
            } else {
                $lang = $fs->prefs['lang_code'];
            }
            $emails[$lang][] = $recipient;
            if (!in_array($lang, $languages)) {
                $languages[] = $lang;
            }
        }

        foreach ($jabber_users as $recipient) {
            if (!empty($recipient['lang'])) {
                $lang = $recipient['lang'];
            } else if (!empty($proj->prefs['lang_code'])) {
                $lang = $proj->prefs['lang_code'];
            } else {
                $lang = $fs->prefs['lang_code'];
            }
            $jabbers[$lang][] = $recipient;
            if (!in_array($lang, $languages)) {
                $languages[] = $lang;
            }
        }

        // Now, loop trough the possible languages. Oh fuck, I forgot that the message also could
        // be customized for each individual recipient. Have to look at that one later (or now).
        foreach ($languages as $lang) {
            $subject = tL('notifyfromfs', $lang);
            if (isset($emails[$lang])) {
                foreach ($message as $emails[$lang]) {
                    $emails_to_send[] = array($emails[$lang]['recipient'], $emails[$lang]['message']);
                }
                foreach ($email as $emails_to_send) {
                    $notify->SendEmail($email['recipient'], $subject, $email['message']);
                }
            }
            if (isset($jabbers[$lang])) {
                foreach ($message as $jabbers[$lang]) {
                    $jabbers_to_send[] = array($jabbers[$lang]['recipient'], $jabbers[$lang]['message']);
                }
                foreach ($jabber as $jabbers_to_send) {
                    $notify->StoreJabber($jabber['recipient'], $subject, $jabber['message']);
                }
            }
        }
        /*
        $subject = L('notifyfromfs');
        $message = $row['reminder_message'];

        // Pass the recipients and message onto the notification function
        $notify->SendEmail($email_users, $subject, $message);
        $notify->StoreJabber($jabber_users, $subject, $message);
        */
        // Update the database with the time sent
        $update_db = $db->Query("UPDATE  {reminders}
                               SET  last_sent = ?
                             WHERE  reminder_id = ?", array(time(), $row['reminder_id']));
    }
    // send those stored notifications
    $notify->SendJabber();
    unset($notify, $user);
}

if (isset($conf['general']['reminder_daemon']) && ($conf['general']['reminder_daemon'] == 1)) {

    send_reminders();
} else {

    die("reminder daemon is disabled");
}
?>
