<?php

/**
 * Notifications
 *
 * @package
 * @version $Id$
 * @copyright 2006 Flyspray.org
 * @notes: This is a mess and should be replaced for 1.0
 */
class Notifications
{
	// Wrapper function for all others
	function create($type, $task_id, $info = null, $to = null, $ntype = NOTIFY_BOTH, $proj_lang = null)
	{
		global $fs;

		if (is_null($to)) {
			$to = $this->address($task_id, $type);
		}

		if (!is_array($to)) {
			settype($to, 'array');
		}

		if (!count($to)) {
			return false;
		}

		$languages = array();
		$emails = array();
		$jabbers = array();
		$onlines = array();

		if (isset($to[0])) {
			foreach ($to[0] as $recipient) {
				if (!empty($recipient['lang']) && $recipient['lang'] != 'project') {
					$lang = $recipient['lang'];
				} else if (!empty($proj_lang) && $proj_lang !='global') {
					$lang = $proj_lang;
				} else {
					$lang = $fs->prefs['lang_code'];
				}
				$emails[$lang][] = $recipient['recipient'];
				if (!in_array($lang, $languages)) {
					$languages[] = $lang;
				}
			}
		}

		if (isset($to[1])) {
			foreach ($to[1] as $recipient) {
				if (!empty($recipient['lang'])) {
					$lang = $recipient['lang'];
				} else if (!empty($proj_lang)) {
					$lang = $proj_lang;
				} else {
					$lang = $fs->prefs['lang_code'];
				}
				$jabbers[$lang][] = $recipient['recipient'];
				if (!in_array($lang, $languages)) {
					$languages[] = $lang;
				}
			}
		}

		/*
		if (isset($to[2])) {
			foreach ($to[2] as $recipient) {
				$lang = $recipient['lang'];
				if ($lang == 'j') {
					echo "<pre>Error 3!</pre>";
				}
				$onlines[$lang][] = $recipient['recipient'];
				if (!in_array($lang, $languages)) {
					$languages[] = $lang;
				}
			}
		}
		*/

		$result = true;
		foreach ($languages as $lang) {
			$msg = $this->generateMsg($type, $task_id, $info, $lang);
			if (isset($emails[$lang]) && ($ntype == NOTIFY_EMAIL || $ntype == NOTIFY_BOTH)) {
				if (!$this->sendEmail($emails[$lang], $msg[0], $msg[1], $task_id)) {
					$result = false;
				}
			}

			if (isset($jabbers[$lang]) && ($ntype == NOTIFY_JABBER || $ntype == NOTIFY_BOTH)) {
				if (!$this->storeJabber($jabbers[$lang], $msg[0], $msg[1])) {
					$result = false;
				}
			}

			// Get rid of undefined offset 2 when notify type is explicitly set,
			// in these cases caller really has not set offset 2. Track down the
			// callers later.
			/*
			if (isset($onlines[$lang]) && ($ntype != NOTIFY_EMAIL && $ntype != NOTIFY_JABBER)) {
				if (!$this->StoreOnline($onlines[$lang], $msg[2], $msg[3], $task_id)) {
					$result = false;
				}
			}
			*/

		}
		return $result;

	} // End of create() function

	/**
	 * @param array|string $to
	 * @param string $subject
	 * @param string $body
	 * @param ? $online
	 * @param ? $task_id
	 *
	 * @todo review
	 */
	function storeOnline($to, $subject, $body, $online, $task_id = null)
	{
		global $db, $fs;

		if (!count($to)) {
			return false;
		}
		$date = time();

		// store notification in table
		$db->query(
			"INSERT INTO {notification_messages} (message_subject, message_body, time_created) VALUES(?, ?, ?)",
			array($online, '', $date)
		);

		$message_id = $db->insert_ID();
		// If message could not be inserted for whatever reason...
		if (!$message_id) {
			return false;
		}

		// make sure every user is only added once
		settype($to, 'array');
		$to = array_unique($to);

		foreach ($to as $jid) {
			// store each recipient in table
			$db->query(
				"INSERT INTO {notification_recipients}(notify_method, message_id, notify_address) VALUES (?, ?, ?)",
				array('o', $message_id, $jid)
			);
		}

		return true;
	}

	static function getUnreadNotifications()
	{
		global $db, $fs, $user;

		$notifications = $db->query(
			'SELECT r.recipient_id, m.message_subject
			FROM {notification_recipients} r
			JOIN {notification_messages} m ON r.message_id = m.message_id
			WHERE r.notify_method = ?
			AND notify_address = ?',
			array('o', $user['user_id'])
		);
		return $db->fetchAllArray($notifications);
	}

	static function NotificationsHaveBeenRead($ids)
	{
		global $db, $fs, $user;

		$readones = join(",", array_map('intval', $ids));
		if($readones !=''){
			$db->query("
				DELETE FROM {notification_recipients}
				WHERE message_id IN ($readones)
				AND notify_method = ?
				AND notify_address = ?",
				array('o', $user['user_id']
				)
			);
		}
	}

	/**
	 * Store Jabber messages for sending later
	 *
	 * @param array|string $to
	 * @param string $subject
	 * @param string $body
	 */
	function storeJabber($to, $subject, $body)
	{
		global $db, $fs;

		if (
			empty($fs->prefs['jabber_server'])
			|| empty($fs->prefs['jabber_port'])
			|| empty($fs->prefs['jabber_username'])
			|| empty($fs->prefs['jabber_password'])
		) {
			return false;
		}

		if (empty($to)) {
			return false;
		}

		$date = time();

		// store notification in table
		$db->query(
			"INSERT INTO {notification_messages} (message_subject, message_body, time_created)
			VALUES (?, ?, ?)",
			array($subject, $body, $date)
		);

		$message_id = $db->insert_ID();
		// If message could not be inserted for whatever reason...
		if (!$message_id) {
			return false;
		}

		settype($to, 'array');

		$duplicates = array();
		foreach ($to as $jid) {
			// make sure every recipient is only added once
			if (in_array($jid, $duplicates)) {
				continue;
			}
			$duplicates[] = $jid;
			// store each recipient in table
			$db->query("INSERT INTO {notification_recipients} (notify_method, message_id, notify_address)
                     VALUES (?, ?, ?)",
			array('j', $message_id, $jid)
			);
		}

		return true;
	}

	static function jabberRequestAuth($email)
	{
		global $fs;

		include_once BASEDIR . '/includes/class.jabber2.php';

		if (
			empty($fs->prefs['jabber_server'])
			|| empty($fs->prefs['jabber_port'])
			|| empty($fs->prefs['jabber_username'])
			|| empty($fs->prefs['jabber_password'])
		) {
			return false;
		}

		$JABBER = new Jabber(
			$fs->prefs['jabber_username'] . '@' . $fs->prefs['jabber_server'],
			$fs->prefs['jabber_password'],
			$fs->prefs['jabber_ssl'],
			$fs->prefs['jabber_port']);
		$JABBER->login();
		$JABBER->send("<presence to='" . Jabber::jspecialchars($email) . "' type='subscribe'/>");
		$JABBER->disconnect();
	}

	/**
	 * send Jabber messages that were stored earlier
	 */
	function sendJabber()
	{
		global $db, $fs;

		include_once BASEDIR . '/includes/class.jabber2.php';

		if (
			empty($fs->prefs['jabber_server'])
			|| empty($fs->prefs['jabber_port'])
			|| empty($fs->prefs['jabber_username'])
			|| empty($fs->prefs['jabber_password'])
		) {
			return false;
		}

		// get listing of all pending jabber notifications
		$result = $db->query("SELECT DISTINCT message_id
                            FROM {notification_recipients}
                            WHERE notify_method='j'");

		if (!$db->countRows($result)) {
			return false;
		}

		$JABBER = new Jabber($fs->prefs['jabber_username'] . '@' . $fs->prefs['jabber_server'],
                   $fs->prefs['jabber_password'],
                   $fs->prefs['jabber_ssl'],
                   $fs->prefs['jabber_port']);
		$JABBER->login();

		// we have notifications to process - connect
		$JABBER->log("We have notifications to process...");
		$JABBER->log("Starting Jabber session:");

		$ids = array();

		while ( $row = $db->fetchRow($result) ) {
			$ids[] = $row['message_id'];
		}

		$desired = join(",", array_map('intval', $ids));
		$JABBER->log("message ids to send = {" . $desired . "}");

			// removed array usage as it's messing up the select
			// I suspect this is due to the variable being comma separated
			// Jamin W. Collins 20050328
			$notifications = $db->query("
				SELECT * FROM {notification_messages}
				WHERE message_id IN ($desired)
				ORDER BY time_created ASC"
			);
		$JABBER->log("number of notifications {" . $db->countRows($notifications) . "}");

		// loop through notifications
		while ( $notification = $db->fetchRow($notifications) ) {
			$subject = $notification['message_subject'];
			$body    = $notification['message_body'];

			$JABBER->log("Processing notification {" . $notification['message_id'] . "}");
			$recipients = $db->query("
				SELECT * FROM {notification_recipients}
				WHERE message_id = ?
				AND notify_method = 'j'",
				array($notification['message_id'])
			);

			// loop through recipients
			while ($recipient = $db->fetchRow($recipients) ) {
				$jid = $recipient['notify_address'];
				$JABBER->log("- attempting send to {" . $jid . "}");

				// send notification
				if ($JABBER->send_message($jid, $body, $subject, 'normal')) {
					// delete entry from notification_recipients
					$result = $db->query(
						"DELETE FROM {notification_recipients}
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
			$result = $db->query(
				"SELECT * FROM {notification_recipients}
				WHERE message_id = ?",
				array($notification['message_id'])
			);

			if ($db->countRows($result) == 0) {
				$JABBER->log("No further recipients for message id {" . $notification['message_id'] . "}");
				// remove notification no more recipients
				$result = $db->query(
					"DELETE FROM {notification_messages}
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
	}

	function sendEmail($to, $subject, $body, $task_id = null)
	{
		global $fs, $proj, $user;

		if (empty($to) || empty($to[0])) {
			return;
		}

		// Do we want to use a remote mail server?
		if (!empty($fs->prefs['smtp_server'])) {

			// connection... SSL, TLS or none
			if ($fs->prefs['email_tls']) {
				$swiftconn = Swift_SmtpTransport::newInstance($fs->prefs['smtp_server'], 587, 'tls');
			} else if ($fs->prefs['email_ssl']) {
				$swiftconn = Swift_SmtpTransport::newInstance($fs->prefs['smtp_server'], 465, 'ssl');
			} else {
				$swiftconn = Swift_SmtpTransport::newInstance($fs->prefs['smtp_server']);
			}

			if ($fs->prefs['smtp_user']) {
				$swiftconn->setUsername($fs->prefs['smtp_user']);
			}

			if ($fs->prefs['smtp_pass']){
				$swiftconn->setPassword($fs->prefs['smtp_pass']);
			}

			if(defined('FS_SMTP_TIMEOUT')) {
				$swiftconn->setTimeout(FS_SMTP_TIMEOUT);
			}
		// Use php's built-in mail() function
		} else {
			$swiftconn = Swift_MailTransport::newInstance();
		}

		// Make plaintext URLs into hyperlinks, but don't disturb existing ones!
		$htmlbody = preg_replace("/(?<![\"\'])(https?:\/\/)([a-z0-9\-.]+\.[a-z\-]+(:[0-9]+)?([\/]([a-z0-9_\/\-.?&%=+#])*)*)/i", '<a href="$1$2">$1$2</a>', $body);
		$htmlbody = str_replace("\n","<br>", $htmlbody);

		// Those constants used were introduced in 5.4.
		if (version_compare(phpversion(), '5.4.0', '<')) {
			$plainbody= html_entity_decode(strip_tags($body));
		} else {
			$plainbody= html_entity_decode(strip_tags($body), ENT_COMPAT | ENT_HTML401, 'utf-8');
		}

		$swift = Swift_Mailer::newInstance($swiftconn);

		if(defined('FS_MAIL_LOGFILE')) {
			$logger = new Swift_Plugins_Loggers_ArrayLogger();
			$swift->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
		}

		$message = new Swift_Message($subject);
		if (isset($fs->prefs['emailNoHTML']) && $fs->prefs['emailNoHTML'] == '1'){
			$message->setBody($plainbody, 'text/plain');
		} else {
			$message->setBody($htmlbody, 'text/html');
			$message->addPart($plainbody, 'text/plain');
		}

		$type = $message->getHeaders()->get('Content-Type');
		$type->setParameter('charset', 'utf-8');

		$message->getHeaders()->addTextHeader('Precedence', 'list');
		$message->getHeaders()->addTextHeader('X-Mailer', 'Flyspray');

		if ($proj->prefs['notify_reply']) {
			$replyto = explode(',', $proj->prefs['notify_reply']);
			$replyto = array_map('trim', $replyto);
			$message->setReplyTo($replyto);
		}

		if (isset($task_id)) {
			$hostdata = parse_url($GLOBALS['baseurl']);
			$inreplyto = sprintf('<FS%d@%s>', $task_id, $hostdata['host']);
			// see http://cr.yp.to/immhf/thread.html this does not seems to work though :(
			$message->getHeaders()->addTextHeader('In-Reply-To', $inreplyto);
			$message->getHeaders()->addTextHeader('References', $inreplyto);
		}

		// accepts string, array, or Swift_Address
		if (is_array($to) && count($to)>1) {
			$message->setTo($fs->prefs['admin_email']);
        		$message->setBcc($to);
		} else {
			$message->setTo($to);
		}
		$message->setFrom(array($fs->prefs['admin_email'] => $proj->prefs['project_title']));
		$swift->send($message);

		if(defined('FS_MAIL_LOGFILE')) {
			if(is_writable(dirname(FS_MAIL_LOGFILE))) {
				if($fh = fopen(FS_MAIL_LOGFILE, 'ab')) {
					fwrite($fh, $logger->dump());
					fwrite($fh, php_uname());
					fclose($fh);
				}
			}
		}

		return true;
	}

	/**
	 * create a message for any occasion
	 *
	 * @param int $type
	 * @param int|null $task_id
	 * @param array|string|int $arg1 depends on notification type
	 * @param string $lang
	 */
	function generateMsg($type, $task_id, $arg1 = '0', $lang = 'en')
	{
		global $db, $fs, $user, $proj;

		if ($task_id) {
			$task_details = Flyspray::getTaskDetails($task_id);

			$proj = new Project($task_details['project_id']);

			// Set the due date correctly
			if ($task_details['due_date'] == '0') {
				$due_date = tL('undecided', $lang);
			} else {
				$due_date = formatDate($task_details['due_date']);
			}

			// Set the due version correctly
			if ($task_details['closedby_version'] == '0') {
				$task_details['due_in_version_name'] = tL('undecided', $lang);
			}
		}

		// Get the string of modification
		$notify_type_msg = array(
			0 => tL('none'),
			NOTIFY_TASK_OPENED => tL('taskopened', $lang),
			NOTIFY_TASK_CHANGED => tL('pm.taskchanged', $lang),
			NOTIFY_TASK_CLOSED => tL('taskclosed', $lang),
			NOTIFY_TASK_REOPENED => tL('pm.taskreopened', $lang),
			NOTIFY_DEP_ADDED => tL('pm.depadded', $lang),
			NOTIFY_DEP_REMOVED => tL('pm.depremoved', $lang),
			NOTIFY_COMMENT_ADDED => tL('commentadded', $lang),
			NOTIFY_ATT_ADDED => tL('attachmentadded', $lang),
			NOTIFY_REL_ADDED => tL('relatedadded', $lang),
			NOTIFY_OWNERSHIP => tL('ownershiptaken', $lang),
			NOTIFY_PM_REQUEST => tL('pmrequest', $lang),
			NOTIFY_PM_DENY_REQUEST => tL('pmrequestdenied', $lang),
			NOTIFY_NEW_ASSIGNEE => tL('newassignee', $lang),
			NOTIFY_REV_DEP => tL('revdepadded', $lang),
			NOTIFY_REV_DEP_REMOVED => tL('revdepaddedremoved', $lang),
			NOTIFY_ADDED_ASSIGNEES => tL('assigneeadded', $lang),
		);

		// Generate the notification message
		if (isset($proj->prefs['notify_subject']) && !$proj->prefs['notify_subject']) {
			$proj->prefs['notify_subject'] = '[%p][#%t] %s';
		}

		if (
			!isset($proj->prefs['notify_subject']) ||
			$type == NOTIFY_CONFIRMATION ||
			$type == NOTIFY_ANON_TASK ||
			$type == NOTIFY_PW_CHANGE ||
			$type == NOTIFY_NEW_USER ||
			$type == NOTIFY_OWN_REGISTRATION
		) {
			$subject = tL('notifyfromfs', $lang);
		} else {
			$subject = strtr(
				$proj->prefs['notify_subject'],
				array(
					'%p' => $proj->prefs['project_title'],
					'%s' => $task_details['item_summary'],
					'%t' => $task_id,
					'%a' => $notify_type_msg[$type],
					'%u' => $user->infos['user_name']
				)
			);
		}

		$subject = strtr($subject, "\n", '');

        /** -----------------------------
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
          |21. User registration        |
          -------------------------------
         */

		$body = tL('donotreply', $lang) . "\n\n";
		$online = '';

		if ($type == NOTIFY_TASK_OPENED) {
			$body .= tL('newtaskopened', $lang) . " \n\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ") \n\n";
			$body .= tL('attachedtoproject', $lang) . ' - ' . $task_details['project_title'] . "\n";
			$body .= tL('summary', $lang) . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('tasktype', $lang) . ' - ' . $task_details['tasktype_name'] . "\n";
			$body .= tL('category', $lang) . ' - ' . $task_details['category_name'] . "\n";
			$body .= tL('status', $lang) . ' - ' . $task_details['status_name'] . "\n";
			$body .= tL('assignedto', $lang) . ' - ' . implode(', ', $task_details['assigned_to_name']) . "\n";
			$body .= tL('operatingsystem', $lang) . ' - ' . $task_details['os_name'] . "\n";
			$body .= tL('severity', $lang) . ' - ' . $task_details['severity_name'] . "\n";
			$body .= tL('priority', $lang) . ' - ' . $task_details['priority_name'] . "\n";
			$body .= tL('reportedversion', $lang) . ' - ' . $task_details['reported_version_name'] . "\n";
			$body .= tL('dueinversion', $lang) . ' - ' . $task_details['due_in_version_name'] . "\n";
			$body .= tL('duedate', $lang) . ' - ' . $due_date . "\n";
			$body .= tL('details', $lang) . ' - ' . $task_details['detailed_desc'] . "\n\n";

			if ($arg1 == 'files') {
				$body .= tL('fileaddedtoo', $lang) . "\n\n";
				$subject .= ' (' . tL('attachmentadded', $lang) . ')';
			}

			$body .= tL('moreinfo', $lang) . "\n";
			$body .= createURL('details', $task_id);

			$online .= tL('newtaskopened', $lang) . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
			$online .= tL('attachedtoproject', $lang) . ' - ' . $task_details['project_title'] . ". ";
			$online .= tL('summary', $lang) . ' - ' . $task_details['item_summary'];
		}

		if ($type == NOTIFY_TASK_CHANGED) {
			$translation = array(
				'priority_name' => tL('priority', $lang),
				'severity_name' => tL('severity', $lang),
				'status_name' => tL('status', $lang),
				'assigned_to_name' => tL('assignedto', $lang),
				'due_in_version_name' => tL('dueinversion', $lang),
				'reported_version_name' => tL('reportedversion', $lang),
				'tasktype_name' => tL('tasktype', $lang),
				'os_name' => tL('operatingsystem', $lang),
				'category_name' => tL('category', $lang),
				'due_date' => tL('duedate', $lang),
				'percent_complete' => tL('percentcomplete', $lang),
				'mark_private' => tL('visibility', $lang),
				'item_summary' => tL('summary', $lang),
				'detailed_desc' => tL('taskedited', $lang),
				'project_title' => tL('attachedtoproject', $lang),
				'estimated_effort' => tL('estimatedeffort', $lang)
			);

			$body .= tL('taskchanged', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ': ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n";

			$online .= tL('taskchanged', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'];

			foreach ($arg1 as $change) {
				if ($change[0] == 'assigned_to_name') {
					$change[1] = implode(', ', $change[1]);
					$change[2] = implode(', ', $change[2]);
				}
				if ($change[0] == 'detailed_desc') {
					$body .= $translation[$change[0]] . ":\n-------\n" . $change[2] . "\n-------\n";
				} else {
					$body .= $translation[$change[0]] . ': ' . ( ($change[1]) ? $change[1] : '[-]' ) . ' -> ' . ( ($change[2]) ? $change[2] : '[-]' ) . "\n";
				}
			}
			$body .= "\n" . tL('moreinfo', $lang) . "\n";
			$body .= createURL('details', $task_id);
		}

		if ($type == NOTIFY_TASK_CLOSED) {
			$body .= tL('notify.taskclosed', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n\n";
			$body .= tL('reasonforclosing', $lang) . ' ' . $task_details['resolution_name'] . "\n";

			if (!empty($task_details['closure_comment'])) {
				$body .= tL('closurecomment', $lang) . ' ' . $task_details['closure_comment'] . "\n\n";
			}

			$body .= tL('moreinfo', $lang) . "\n";
			$body .= createURL('details', $task_id);

			$online .= tL('notify.taskclosed', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		if ($type == NOTIFY_TASK_REOPENED) {
			$body .= tL('notify.taskreopened', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n\n";
			$body .= tL('moreinfo', $lang) . "\n";
			$body .= createURL('details', $task_id);

			$online .= tL('notify.taskreopened', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		if ($type == NOTIFY_DEP_ADDED) {
			$depend_task = Flyspray::getTaskDetails($arg1);

			$body .= tL('newdep', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n";
			$body .= createURL('details', $task_id) . "\n\n\n";
			$body .= tL('newdepis', $lang) . ':' . "\n\n";
			$body .= 'FS#' . $depend_task['task_id'] . ' - ' . $depend_task['item_summary'] . "\n";
			$body .= createURL('details', $depend_task['task_id']);

			$online .= tL('newdep', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		if ($type == NOTIFY_DEP_REMOVED) {
			$depend_task = Flyspray::getTaskDetails($arg1);

			$body .= tL('notify.depremoved', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n";
			$body .= createURL('details', $task_id) . "\n\n\n";
			$body .= tL('removeddepis', $lang) . ':' . "\n\n";
			$body .= 'FS#' . $depend_task['task_id'] . ' - ' . $depend_task['item_summary'] . "\n";
			$body .= createURL('details', $depend_task['task_id']);

			$online .= tL('notify.depremoved', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		if ($type == NOTIFY_COMMENT_ADDED) {
			// Get the comment information
			$result = $db->query("SELECT comment_id, comment_text
			FROM {comments}
			WHERE user_id = ?
			AND task_id = ?
			ORDER BY comment_id DESC", array($user->id, $task_id), 1);
			$comment = $db->fetchRow($result);

			$body .= tL('notify.commentadded', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n\n";
			$body .= "----------\n";
			$body .= $comment['comment_text'] . "\n";
			$body .= "----------\n\n";

			if ($arg1 == 'files') {
				$body .= tL('fileaddedtoo', $lang) . "\n\n";
				$subject .= ' (' . tL('attachmentadded', $lang) . ')';
			}

			$body .= tL('moreinfo', $lang) . "\n";
			$body .= createURL('details', $task_id) . '#comment' . $comment['comment_id'];

			$online .= tL('notify.commentadded', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		if ($type == NOTIFY_ATT_ADDED) {
			$body .= tL('newattachment', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n\n";
			$body .= tL('moreinfo', $lang) . "\n";
			$body .= createURL('details', $task_id);

			$online .= tL('newattachment', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		if ($type == NOTIFY_REL_ADDED) {
			$related_task = Flyspray::getTaskDetails($arg1);

			$body .= tL('notify.relatedadded', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n";
			$body .= createURL('details', $task_id) . "\n\n\n";
			$body .= tL('relatedis', $lang) . ':' . "\n\n";
			$body .= 'FS#' . $related_task['task_id'] . ' - ' . $related_task['item_summary'] . "\n";
			$body .= createURL('details', $related_task['task_id']);

			$online .= tL('notify.relatedadded', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		// Ownership taken
		if ($type == NOTIFY_OWNERSHIP) {
			$body .= implode(', ', $task_details['assigned_to_name']) . ' ' . tL('takenownership', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n\n";
			$body .= tL('moreinfo', $lang) . "\n";
			$body .= createURL('details', $task_id);

			$online .= implode(', ', $task_details['assigned_to_name']) . ' ' . tL('takenownership', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ".";
		}

		// send Confirmation code
		if ($type == NOTIFY_CONFIRMATION) {
			$body .= tL('noticefrom', $lang) . " {$proj->prefs['project_title']}\n\n"
			. tL('addressused', $lang) . "\n\n"
			. " {$arg1[0]}index.php?do=register&magic_url={$arg1[1]} \n\n"
			// In case that spaces in the username have been removed
			. tL('username', $lang) . ': ' . $arg1[2] . "\n"
			. tL('confirmcodeis', $lang) . " $arg1[3] \n\n";

			$online = $body;
		}

		if ($type == NOTIFY_PM_REQUEST) {
			$body .= tL('requiresaction', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho') . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n\n";
			$body .= tL('moreinfo', $lang) . "\n";
			$body .= createURL('details', $task_id);

			$online .= tL('requiresaction', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		if ($type == NOTIFY_PM_DENY_REQUEST) {
			$body .= tL('pmdeny', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n\n";
			$body .= tL('denialreason', $lang) . ':' . "\n";
			$body .= $arg1 . "\n\n";
			$body .= tL('moreinfo', $lang) . "\n";
			$body .= createURL('details', $task_id);

			$online .= tL('pmdeny', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		if ($type == NOTIFY_NEW_ASSIGNEE) {
			$body .= tL('assignedtoyou', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n\n";
			$body .= tL('moreinfo', $lang) . "\n";
			$body .= createURL('details', $task_id);

			$online .= tL('assignedtoyou', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		// reversed dependancy
		if ($type == NOTIFY_REV_DEP) {
			$depend_task = Flyspray::getTaskDetails($arg1);

			$body .= tL('taskwatching', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n";
			$body .= createURL('details', $task_id) . "\n\n\n";
			$body .= tL('isdepfor', $lang) . ':' . "\n\n";
			$body .= 'FS#' . $depend_task['task_id'] . ' - ' . $depend_task['item_summary'] . "\n";
			$body .= createURL('details', $depend_task['task_id']);

			$online .= tL('taskwatching', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		// reversed dependancy - removed
		if ($type == NOTIFY_REV_DEP_REMOVED) {
			$depend_task = Flyspray::getTaskDetails($arg1);

			$body .= tL('taskwatching', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n";
			$body .= createURL('details', $task_id) . "\n\n\n";
			$body .= tL('isnodepfor', $lang) . ':' . "\n\n";
			$body .= 'FS#' . $depend_task['task_id'] . ' - ' . $depend_task['item_summary'] . "\n";
			$body .= createURL('details', $depend_task['task_id']);

			$online .= tL('taskwatching', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		if ($type == NOTIFY_ADDED_ASSIGNEES) {
			$body .= tL('useraddedtoassignees', $lang) . "\n\n";
			$body .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . "\n";
			$body .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . ")\n";
			$body .= createURL('details', $task_id);

			$online .= tL('useraddedtoassignees', $lang) . ". ";
			$online .= 'FS#' . $task_id . ' - ' . $task_details['item_summary'] . ". ";
			$online .= tL('userwho', $lang) . ' - ' . $user->infos['real_name'] . ' (' . $user->infos['user_name'] . "). ";
		}

		if ($type == NOTIFY_ANON_TASK) {
			$body .= tL('thankyouforbug', $lang) . "\n\n";
			$body .= createURL('details', $task_id, null, array('task_token' => $arg1)) . "\n\n";

			$online .= tL('thankyouforbug') . "";
		}

		if ($type == NOTIFY_PW_CHANGE) {
			$body = tL('magicurlmessage', $lang) . " \n"
			. "{$arg1[0]}index.php?do=lostpw&magic_url=$arg1[1]\n\n"
			. tL('messagefrom', $lang) . $arg1[0];
			$online = $body;
		}

		if ($type == NOTIFY_NEW_USER) {
			$body = tL('newuserregistered', $lang) . " \n\n"
			. tL('username', $lang) . ': ' . $arg1[1] . "\n"
			. tL('realname', $lang) . ': ' . $arg1[2] . "\n";
			$online = $body;

			if ($arg1[6]) {
				$body .= tL('password', $lang) . ': ' . $arg1[5] . "\n";
			}

			$body .= tL('emailaddress', $lang) . ': ' . $arg1[3] . "\n";
			$body .= tL('jabberid', $lang) . ':' . $arg1[4] . "\n\n";
			$body .= tL('messagefrom', $lang) . $arg1[0];
		}

		// New user him/herself
		if ($type == NOTIFY_OWN_REGISTRATION) {
			$body = tL('youhaveregistered', $lang) . " \n\n"
			. tL('username', $lang) . ': ' . $arg1[1] . "\n"
			. tL('realname', $lang) . ': ' . $arg1[2] . "\n";
			$online = $body;

			if ($arg1[6]) {
				$body .= tL('password', $lang) . ': ' . $arg1[5] . "\n";
			}

			$body .= tL('emailaddress', $lang) . ': ' . $arg1[3] . "\n";
			$body .= tL('jabberid', $lang) . ':' . $arg1[4] . "\n\n";

			// Add something here to tell the user whether the registration must
			// first be accepted by Administrators or not. And if it had and was
			// rejected, the reason. Check first what happening when requests are
			// either denied or accepted.
			$body .= tL('messagefrom', $lang) . $arg1[0];
		}

		$body .= "\n\n" . tL('disclaimer', $lang);

		return array(Notifications::fixMsgData($subject), Notifications::fixMsgData($body), $online);
	}

	/**
	 *
	 */
	public static function assignRecipients($recipients, &$emails, &$jabbers, &$onlines, $ignoretype = false)
	{
		global $db, $fs, $user;

		if (!is_array($recipients)) {
			return false;
		}

		foreach ($recipients as $recipient) {
			if ($recipient['user_id'] == $user->id && !$user->infos['notify_own']) {
				continue;
			}

			if (
				($fs->prefs['user_notify'] == '1' && ($recipient['notify_type'] == NOTIFY_EMAIL || $recipient['notify_type'] == NOTIFY_BOTH))
				|| $fs->prefs['user_notify'] == '2'
				|| $ignoretype
			) {
				if (isset($recipient['email_address']) && !empty($recipient['email_address'])) {
					$emails[$recipient['email_address']] = array('recipient' => $recipient['email_address'], 'lang' => $recipient['lang_code']);
				}
			}

			if (
				($fs->prefs['user_notify'] == '1' && ($recipient['notify_type'] == NOTIFY_JABBER || $recipient['notify_type'] == NOTIFY_BOTH))
				|| $fs->prefs['user_notify'] == '3'
				|| $ignoretype
			) {
				if (isset($recipient['jabber_id']) && !empty($recipient['jabber_id']) && $recipient['jabber_id']) {
					$jabbers[$recipient['jabber_id']] = array('recipient' => $recipient['jabber_id'], 'lang' => $recipient['lang_code']);
				}
			}

			/*
			if ($fs->prefs['user_notify'] == '1' && $recipient['notify_online']) {
				$onlines[$recipient['user_id']] = array('recipient' => $recipient['user_id'], 'lang' => $recipient['lang_code']);
			}
			*/
		}
	}

	/**
	 * Create an address list for specific users
	 */
	function specificAddresses($users, $ignoretype = false)
	{
		global $db, $fs, $user;

		$emails = array();
		$jabbers = array();
		$onlines = array();

		if (!is_array($users)) {
			settype($users, 'array');
		}

		if (count($users) < 1) {
			return array();
		}

		$sql = $db->query(
			'SELECT u.user_id, u.email_address, u.jabber_id, u.notify_online, u.notify_type, u.notify_own, u.lang_code
			FROM {users} u
			WHERE' . substr(str_repeat(' user_id = ? OR ', count($users)), 0, -3),
			array_values($users)
		);

		self::assignRecipients($db->fetchAllArray($sql), $emails, $jabbers, $onlines, $ignoretype);
		return array($emails, $jabbers, $onlines);
	}

	/**
	 * Create a standard address list of users (assignees, notif tab and proj addresses)
	 */
	function address($task_id, $type)
	{
		global $db, $fs, $proj, $user;

		$users = array();
		$emails = array();
		$jabbers = array();
		$onlines = array();

		$task_details = Flyspray::getTaskDetails($task_id);

		// Get list of users from the notification tab
		$get_users = $db->query('
			SELECT * FROM {notifications} n
			LEFT JOIN {users} u ON n.user_id = u.user_id
			WHERE n.task_id = ?',
			array($task_id)
		);
		self::assignRecipients($db->fetchAllArray($get_users), $emails, $jabbers, $onlines);

		// Get list of assignees
		$get_users = $db->query('
			SELECT * FROM {assigned} a
			LEFT JOIN {users} u ON a.user_id = u.user_id
			WHERE a.task_id = ?',
			array($task_id)
		);
		self::assignRecipients($db->fetchAllArray($get_users), $emails, $jabbers, $onlines);

		// Now, we add the project contact addresses...
		// ...but only if the task is public
		if (
			$task_details['mark_private'] != '1'
			&& in_array($type, Flyspray::int_explode(' ', $proj->prefs['notify_types']))
		) {
			// FIXME! Have to find users preferred language here too,
			// must fetch from database. But the address could also be a mailing
			// list address and user not exist in database, use fs->prefs in that case,

			$proj_emails = preg_split('/[\s,;]+/', $proj->prefs['notify_email'], -1, PREG_SPLIT_NO_EMPTY);
			$desired = implode("','", $proj_emails);
			if ($desired !='') {
				$get_users = $db->query("
					SELECT DISTINCT u.user_id, u.email_address, u.jabber_id,
					u.notify_online, u.notify_type, u.notify_own, u.lang_code
					FROM {users} u
					WHERE u.email_address IN ('$desired')"
				);

				self::assignRecipients($db->fetchAllArray($get_users), $emails, $jabbers, $onlines);
			}

			$proj_jids = explode(',', $proj->prefs['notify_jabber']);
			$desired = implode("','", $proj_jids);
			if($desired!='') {
				$get_users = $db->query("
					SELECT DISTINCT u.user_id, u.email_address, u.jabber_id,
					u.notify_online, u.notify_type, u.notify_own, u.lang_code
					FROM {users} u
					WHERE u.jabber_id IN ('$desired')"
				);
				self::assignRecipients($db->fetchAllArray($get_users), $emails, $jabbers, $onlines);
			}

			// Now, handle notification addresses that are not assigned to any user...
			foreach ($proj_emails as $email) {
				if (!array_key_exists($email, $emails)) {
					$emails[$email] = array('recipient' => $email, 'lang' => $fs->prefs['lang_code']);
				}
			}

			foreach ($proj_jids as $jabber) {
				if (!array_key_exists($jabber, $jabbers)) {
					$jabbers[$jabber] = array('recipient' => $jabber, 'lang' => $fs->prefs['lang_code']);
				}
			}

			/*
			echo "<pre>";
			echo var_dump($proj_emails);
			echo var_dump($proj_jids);
			echo "</pre>";
			*/

			// End of checking if a task is private
		}

		// Send back three arrays containing the notification addresses
		return array($emails, $jabbers, $onlines);
	}

	/**
	 * fixMsgData
	 * a 0.9.9.x ONLY workaround for the "truncated email problem"
	 * based on code Henri Sivonen (http://hsivonen.iki.fi)
	 * @param mixed $data
	 * @access public
	 * @return string
	 */
	function fixMsgData($data)
	{
		// at the first step, remove all NUL bytes
		// users with broken databases encoding can give us this :(
		$data = str_replace(chr(0), '', $data);

		// then remove all invalid utf8 secuences
		$UTF8_BAD =
			'([\x00-\x7F]'.                          # ASCII (including control chars)
			'|[\xC2-\xDF][\x80-\xBF]'.               # non-overlong 2-byte
			'|\xE0[\xA0-\xBF][\x80-\xBF]'.           # excluding overlongs
			'|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'.    # straight 3-byte
			'|\xED[\x80-\x9F][\x80-\xBF]'.           # excluding surrogates
			'|\xF0[\x90-\xBF][\x80-\xBF]{2}'.        # planes 1-3
			'|[\xF1-\xF3][\x80-\xBF]{3}'.            # planes 4-15
			'|\xF4[\x80-\x8F][\x80-\xBF]{2}'.        # plane 16
			'|(.{1}))';                              # invalid byte

		$valid_data = '';

		while (preg_match('/'.$UTF8_BAD.'/S', $data, $matches)) {
			if (!isset($matches[2])) {
				$valid_data .= $matches[0];
			} else {
				$valid_data .= '?';
			}
			$data = substr($data, strlen($matches[0]));
		}
		return $valid_data;
	}

} // end of Notifications class
