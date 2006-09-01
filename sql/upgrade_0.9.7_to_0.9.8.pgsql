-- Added on 07 March 05
INSERT INTO flyspray_prefs (pref_name, pref_value, pref_desc) VALUES ('global_theme', 'Bluey', 'Theme to use when viewing all projects');
INSERT INTO flyspray_prefs (pref_name, pref_value, pref_desc) VALUES ('visible_columns', 'id project category tasktype severity summary status progress', 'Columns visible when viewing all projects');

ALTER TABLE flyspray_list_tasktype ADD project_id BIGINT;
UPDATE flyspray_list_tasktype SET project_id = 0;
ALTER TABLE flyspray_list_tasktype ALTER project_id SET NOT NULL;

ALTER TABLE flyspray_list_resolution ADD project_id BIGINT;
UPDATE flyspray_list_resolution SET project_id = 0;
ALTER TABLE flyspray_list_resolution ALTER project_id SET NOT NULL;

ALTER TABLE flyspray_admin_requests ADD reason_given TEXT;
UPDATE flyspray_admin_requests SET reason_given = '';
ALTER TABLE flyspray_admin_requests ALTER reason_given SET NOT NULL;

-- added 20050320 by Jamin W. Collins - CHANGED on 29 March 05 
CREATE SEQUENCE "flyspray_notification_messages_message_id_seq" START WITH 2;
CREATE TABLE flyspray_notification_messages (
	message_id INT8  NOT NULL  DEFAULT nextval('"flyspray_notification_messages_message_id_seq"'::text),
	message_subject  TEXT   NOT NULL DEFAULT '',
	message_body   TEXT    NOT NULL DEFAULT '',
	time_created  TEXT,
	PRIMARY KEY (message_id)
);

CREATE SEQUENCE "flyspray_notification_recipients_recipient_id_seq" START WITH 2;
CREATE TABLE flyspray_notification_recipients (
	recipient_id INT8  NOT NULL  DEFAULT nextval('"flyspray_notification_recipients_recipient_id_seq"'::text),
	message_id  BIGINT  NOT NULL,
	notify_method      TEXT   NOT NULL DEFAULT '',
	notify_address      TEXT  NOT NULL DEFAULT '',
	PRIMARY KEY (recipient_id)
);

-- Added 27 March 05 
ALTER TABLE flyspray_projects ADD notify_email TEXT;
ALTER TABLE flyspray_projects ALTER notify_email SET DEFAULT '';
UPDATE flyspray_projects SET notify_email = '' WHERE notify_email IS NULL;
ALTER TABLE flyspray_projects ALTER notify_email SET NOT NULL;

ALTER TABLE flyspray_projects ADD notify_email_when BIGINT;
ALTER TABLE flyspray_projects ALTER notify_email_when SET DEFAULT 0;
UPDATE flyspray_projects SET notify_email_when = 0 WHERE notify_email_when IS NULL;
ALTER TABLE flyspray_projects ALTER notify_email_when SET NOT NULL;

ALTER TABLE flyspray_projects ADD notify_jabber TEXT;
ALTER TABLE flyspray_projects ALTER notify_jabber SET DEFAULT '';
UPDATE flyspray_projects SET notify_jabber = '' WHERE notify_jabber IS NULL;
ALTER TABLE flyspray_projects ALTER notify_jabber SET NOT NULL;

ALTER TABLE flyspray_projects ADD notify_jabber_when BIGINT;
ALTER TABLE flyspray_projects ALTER notify_jabber_when SET DEFAULT 0;
UPDATE flyspray_projects SET notify_jabber_when = 0 WHERE notify_jabber_when IS NULL;
ALTER TABLE flyspray_projects ALTER notify_jabber_when SET NOT NULL;

-- Added 3 April 05 
INSERT INTO flyspray_prefs (pref_name, pref_value, pref_desc) VALUES ('smtp_server', '', 'Remote mail server');
INSERT INTO flyspray_prefs (pref_name, pref_value, pref_desc) VALUES ('smtp_user', '', 'Username to access the remote mail server');
INSERT INTO flyspray_prefs (pref_name, pref_value, pref_desc) VALUES ('smtp_pass', '', 'Password to access the remote mail server');

-- Added 5 April 05 
ALTER TABLE flyspray_tasks ADD due_date TEXT;
ALTER TABLE flyspray_tasks ALTER due_date SET DEFAULT '';
UPDATE flyspray_tasks SET due_date = '' WHERE due_date IS NULL;
ALTER TABLE flyspray_tasks ALTER due_date SET NOT NULL;

-- Added 15 Apr 05 
ALTER TABLE flyspray_admin_requests ADD deny_reason TEXT;
ALTER TABLE flyspray_admin_requests ALTER deny_reason SET DEFAULT '';
UPDATE flyspray_admin_requests SET deny_reason = '' WHERE deny_reason IS NULL;
ALTER TABLE flyspray_admin_requests ALTER deny_reason SET NOT NULL;

-- Added 01 May 05
INSERT INTO flyspray_prefs (pref_name, pref_value, pref_desc)
VALUES ('funky_urls', '0', 
'Should we use address rewriting? Not all webservers support this!');

-- Added 14 May 05
CREATE TABLE flyspray_assigned (
    assigned_id SERIAL,
    task_id INT8 NOT NULL DEFAULT 0,
    assignee_id INT8 NOT NULL DEFAULT 0,
    user_or_group VARCHAR(1) NOT NULL DEFAULT 0,
    PRIMARY KEY ( assigned_id )
);

-- Added 15 May 05
ALTER TABLE flyspray_attachments ADD comment_id INT8;
UPDATE flyspray_attachments set comment_id = 0;
ALTER TABLE flyspray_attachments ALTER comment_id SET NOT NULL;

-- Added 26 June 2005
ALTER TABLE flyspray_tasks ALTER closure_comment DROP NOT NULL;

-- Added 19 Jul 05
ALTER TABLE flyspray_users ADD last_search TEXT;
DELETE FROM flyspray_prefs WHERE pref_name = 'anon_view' ;
DELETE FROM flyspray_prefs WHERE pref_name = 'theme_style' ;
DELETE FROM flyspray_prefs WHERE pref_name = 'base_url' ;
DELETE FROM flyspray_prefs WHERE pref_name = 'project_title' ;
DELETE FROM flyspray_prefs WHERE pref_name = 'default_cat_owner' ;
ALTER TABLE flyspray_users DROP group_in ;
ALTER TABLE flyspray_users ADD tasks_perpage INT4;
UPDATE flyspray_users SET tasks_perpage = '25' ;
ALTER TABLE flyspray_users ALTER tasks_perpage set NOT NULL ;

-- Added 21 Jul 05
-- altering flyspray_prefs.pref_value unnecessary, already stored as TEXT

-- Added 7 Aug 05
ALTER TABLE flyspray_tasks ALTER closure_comment DROP NOT NULL;

-- Added 20 August 05 by Jeffery Fernandez <developer@jefferyfernandez.id.au> for updating the Flyspray version.
UPDATE flyspray_prefs SET pref_value = '0.9.8' WHERE pref_name = 'fs_ver';

-- Added 22 December 05 by Florian Schmitz, FS#760
ALTER TABLE flyspray_list_category ADD COLUMN parent_id_new bigint ;
UPDATE flyspray_list_category SET parent_id_new = CAST(parent_id AS bigint);
ALTER TABLE flyspray_list_category DROP COLUMN parent_id;
Alter table flyspray_list_category rename parent_id_new to parent_id;

