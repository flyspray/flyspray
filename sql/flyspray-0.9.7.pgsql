CREATE SEQUENCE "flyspray_admin_requests_request_id_seq" START WITH 1;
CREATE TABLE  flyspray_admin_requests (
	request_id INT8  NOT NULL DEFAULT nextval('"flyspray_admin_requests_request_id_seq"'::text),
	project_id NUMERIC(5) NOT NULL default '0',
	task_id NUMERIC(5) NOT NULL default '0',
	submitted_by NUMERIC(5) NOT NULL default '0',
	request_type NUMERIC(2) NOT NULL default '0',
	time_submitted TEXT NOT NULL default '',
	resolved_by NUMERIC(5) NOT NULL default '0',
	time_resolved TEXT NOT NULL default '',
	PRIMARY KEY  (request_id)
);

CREATE SEQUENCE "flyspray_attachments_attachment_id_seq" START WITH 1;
CREATE TABLE  flyspray_attachments (
	attachment_id INT8  NOT NULL DEFAULT nextval('"flyspray_attachments_attachment_id_seq"'::text),
	task_id NUMERIC(10) NOT NULL default '0',
	orig_name TEXT NOT NULL default '',
	file_name TEXT NOT NULL default '',
	file_desc TEXT NOT NULL default '',
	file_type TEXT NOT NULL default '',
	file_size NUMERIC(20) NOT NULL default '0',
	added_by NUMERIC(3) NOT NULL default '0',
	date_added TEXT NOT NULL default '',
	PRIMARY KEY  (attachment_id)
);

CREATE SEQUENCE "flyspray_comments_comment_id_seq" START WITH 1;
CREATE TABLE  flyspray_comments (
	comment_id INT8  NOT NULL DEFAULT nextval('"flyspray_comments_comment_id_seq"'::text),
	task_id NUMERIC(10) NOT NULL default '0',
	date_added TEXT NOT NULL default '',
	user_id NUMERIC(3) NOT NULL default '0',
	comment_text TEXT NOT NULL,
	PRIMARY KEY  (comment_id)
);

CREATE SEQUENCE "flyspray_dependencies_depend_id_seq" START WITH 1;
CREATE TABLE  flyspray_dependencies (
	depend_id INT8  NOT NULL DEFAULT nextval('"flyspray_dependencies_depend_id_seq"'::text),
	task_id NUMERIC(10) NOT NULL default '0',
	dep_task_id NUMERIC(10) NOT NULL default '0',
	PRIMARY KEY  (depend_id)
);

CREATE SEQUENCE "flyspray_groups_group_id_seq" START WITH 7;
CREATE TABLE  flyspray_groups (
	group_id INT8  NOT NULL DEFAULT nextval('"flyspray_groups_group_id_seq"'::text),
	group_name TEXT NOT NULL default '',
	group_desc TEXT NOT NULL default '',
	belongs_to_project NUMERIC(3) NOT NULL default '0',
	is_admin NUMERIC(1) NOT NULL default '0',
	manage_project NUMERIC(1) NOT NULL default '0',
	view_tasks NUMERIC(1) NOT NULL default '0',
	open_new_tasks NUMERIC(1) NOT NULL default '0',
	modify_own_tasks NUMERIC(1) NOT NULL default '0',
	modify_all_tasks NUMERIC(1) NOT NULL default '0',
	view_comments NUMERIC(1) NOT NULL default '0',
	add_comments NUMERIC(1) NOT NULL default '0',
	edit_comments NUMERIC(1) NOT NULL default '0',
	delete_comments NUMERIC(1) NOT NULL default '0',
	view_attachments NUMERIC(1) NOT NULL default '0',
	create_attachments NUMERIC(1) NOT NULL default '0',
	delete_attachments NUMERIC(1) NOT NULL default '0',
	view_history NUMERIC(1) NOT NULL default '0',
	close_own_tasks NUMERIC(1) NOT NULL default '0',
	close_other_tasks NUMERIC(1) NOT NULL default '0',
	assign_to_self NUMERIC(1) NOT NULL default '0',
	assign_others_to_self NUMERIC(1) NOT NULL default '0',
	view_reports NUMERIC(1) NOT NULL default '0',
	group_open NUMERIC(1) NOT NULL default '0',
	PRIMARY KEY  (group_id)
);

INSERT INTO flyspray_groups VALUES (1, 'Admin', 'Members have unlimited access to all functionality.', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
INSERT INTO flyspray_groups VALUES (2, 'Developers', 'Global Developers for all projects', 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
INSERT INTO flyspray_groups VALUES (3, 'Reporters', 'Open new tasks / add comments in all projects', 0, 0, 0, 1, 1, 0, 0, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1);
INSERT INTO flyspray_groups VALUES (4, 'Basic', 'Members can login, relying upon Project permissions only', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1);
INSERT INTO flyspray_groups VALUES (5, 'Pending', 'Users who are awaiting approval of their accounts.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO flyspray_groups VALUES (6, 'Project Managers', 'Permission to do anything related to the Default Project.', 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1);

CREATE SEQUENCE "flyspray_history_history_id_seq" START WITH 2;
CREATE TABLE  flyspray_history (
	history_id INT8  NOT NULL DEFAULT nextval('"flyspray_history_history_id_seq"'::text),
	task_id NUMERIC(10) NOT NULL default '0',
	user_id NUMERIC(3) NOT NULL default '0',
	event_date text NOT NULL,
	event_type NUMERIC(2) NOT NULL default '0',
	field_changed text NOT NULL,
	old_value text NOT NULL,
	new_value text NOT NULL,
	PRIMARY KEY  (history_id)
);

INSERT INTO flyspray_history VALUES (1, 1, 1, '1103430560', 1, '', '', '');

CREATE SEQUENCE "flyspray_list_category_category_id_seq" START WITH 3;
CREATE TABLE  flyspray_list_category (
	category_id INT8  NOT NULL DEFAULT nextval('"flyspray_list_category_category_id_seq"'::text),
	project_id NUMERIC(3) NOT NULL default '0',
	category_name TEXT NOT NULL default '',
	list_position NUMERIC(3) NOT NULL default '0',
	show_in_list NUMERIC(1) NOT NULL default '0',
	category_owner NUMERIC(3) NOT NULL default '0',
	parent_id NUMERIC(1) NOT NULL default '0',
	PRIMARY KEY  (category_id)
);

INSERT INTO flyspray_list_category VALUES (1, 1, 'Backend / Core', 1, 1, 0, 0);
INSERT INTO flyspray_list_category VALUES (2, 1, 'User Interface', 2, 1, 0, 0);

CREATE SEQUENCE "flyspray_list_os_os_id_seq" START WITH 6;
CREATE TABLE  flyspray_list_os (
	os_id INT8  NOT NULL DEFAULT nextval('"flyspray_list_os_os_id_seq"'::text),
	project_id NUMERIC(3) NOT NULL default '0',
	os_name TEXT NOT NULL default '',
	list_position NUMERIC(3) NOT NULL default '0',
	show_in_list NUMERIC(1) NOT NULL default '0',
	PRIMARY KEY  (os_id)
);

INSERT INTO flyspray_list_os VALUES (1, 1, 'All', 1, 1);
INSERT INTO flyspray_list_os VALUES (2, 1, 'Windows', 2, 1);
INSERT INTO flyspray_list_os VALUES (3, 1, 'Linux', 3, 1);
INSERT INTO flyspray_list_os VALUES (4, 1, 'Mac OS', 4, 1);
INSERT INTO flyspray_list_os VALUES (5, 1, 'UNIX', 4, 1);

CREATE SEQUENCE "flyspray_list_resolution_resolution_id_seq" START WITH 10;
CREATE TABLE  flyspray_list_resolution (
	resolution_id INT8  NOT NULL DEFAULT nextval('"flyspray_list_resolution_resolution_id_seq"'::text),
	resolution_name TEXT NOT NULL default '',
	list_position NUMERIC(3) NOT NULL default '0',
	show_in_list NUMERIC(1) NOT NULL default '0',
	PRIMARY KEY  (resolution_id)
);

INSERT INTO flyspray_list_resolution VALUES (1, 'None', 1, 1);
INSERT INTO flyspray_list_resolution VALUES (2, 'Not a bug', 2, 1);
INSERT INTO flyspray_list_resolution VALUES (3, 'Won''t fix', 3, 1);
INSERT INTO flyspray_list_resolution VALUES (4, 'Won''t implement', 4, 1);
INSERT INTO flyspray_list_resolution VALUES (5, 'Works for me', 5, 1);
INSERT INTO flyspray_list_resolution VALUES (6, 'Duplicate', 6, 1);
INSERT INTO flyspray_list_resolution VALUES (7, 'Deferred', 7, 1);
INSERT INTO flyspray_list_resolution VALUES (8, 'Fixed', 8, 1);
INSERT INTO flyspray_list_resolution VALUES (9, 'Implemented', 9, 1);

CREATE SEQUENCE "flyspray_list_tasktype_tasktype_id_seq" START WITH 4;
CREATE TABLE  flyspray_list_tasktype (
	tasktype_id INT8  NOT NULL DEFAULT nextval('"flyspray_list_tasktype_tasktype_id_seq"'::text),
	tasktype_name TEXT NOT NULL default '',
	list_position NUMERIC(3) NOT NULL default '0',
	show_in_list NUMERIC(1) NOT NULL default '0',
	PRIMARY KEY  (tasktype_id)
);

INSERT INTO flyspray_list_tasktype VALUES (1, 'Bug Report', 1, 1);
INSERT INTO flyspray_list_tasktype VALUES (2, 'Feature Request', 2, 1);
INSERT INTO flyspray_list_tasktype VALUES (3, 'Support Request', 3, 1);

CREATE SEQUENCE "flyspray_list_version_version_id_seq" START WITH 3;
CREATE TABLE  flyspray_list_version (
	version_id INT8  NOT NULL DEFAULT nextval('"flyspray_list_version_version_id_seq"'::text),
	project_id NUMERIC(3) NOT NULL default '0',
	version_name TEXT NOT NULL default '',
	list_position NUMERIC(3) NOT NULL default '0',
	show_in_list NUMERIC(1) NOT NULL default '0',
	version_tense NUMERIC(1) NOT NULL default '0',
	PRIMARY KEY  (version_id)
);

INSERT INTO flyspray_list_version VALUES (1, 1, 'CVS', 1, 1, 2);
INSERT INTO flyspray_list_version VALUES (2, 1, '1.0', 2, 1, 3);

CREATE SEQUENCE "flyspray_notifications_notify_id_seq" START WITH 1;
CREATE TABLE  flyspray_notifications (
	notify_id INT8  NOT NULL DEFAULT nextval('"flyspray_notifications_notify_id_seq"'::text),
	task_id NUMERIC(10) NOT NULL default '0',
	user_id NUMERIC(5) NOT NULL default '0',
	PRIMARY KEY  (notify_id)
);

CREATE SEQUENCE "flyspray_prefs_pref_id_seq" START WITH 17;
CREATE TABLE  flyspray_prefs (
	pref_id INT8  NOT NULL DEFAULT nextval('"flyspray_prefs_pref_id_seq"'::text),
	pref_name TEXT NOT NULL default '',
	pref_value TEXT NOT NULL default '',
	pref_desc TEXT NOT NULL default '',
	PRIMARY KEY  (pref_id)
);

INSERT INTO flyspray_prefs VALUES (1, 'fs_ver', '0.9.7', 'Current Flyspray version');
INSERT INTO flyspray_prefs VALUES (2, 'jabber_server', '', 'Jabber server');
INSERT INTO flyspray_prefs VALUES (3, 'jabber_port', '5222', 'Jabber server port');
INSERT INTO flyspray_prefs VALUES (4, 'jabber_username', '', 'Jabber username');
INSERT INTO flyspray_prefs VALUES (5, 'jabber_password', '', 'Jabber password');
INSERT INTO flyspray_prefs VALUES (6, 'anon_group', '4', 'Group for anonymous registrations');
INSERT INTO flyspray_prefs VALUES (7, 'base_url', 'http://example.com/flyspray/', 'Base URL for this installation');
INSERT INTO flyspray_prefs VALUES (8, 'user_notify', '1', 'Force task notifications as');
INSERT INTO flyspray_prefs VALUES (9, 'admin_email', 'flyspray@example.com', 'Reply email address for notifications');
INSERT INTO flyspray_prefs VALUES (10, 'assigned_groups', '1 2 3', 'Members of these groups can be assigned tasks');
INSERT INTO flyspray_prefs VALUES (11, 'lang_code', 'en', 'Language');
INSERT INTO flyspray_prefs VALUES (12, 'spam_proof', '1', 'Use confirmation codes for user registrations');
INSERT INTO flyspray_prefs VALUES (13, 'default_project', '1', 'Default project id');
INSERT INTO flyspray_prefs VALUES (14, 'dateformat', '', 'Default date format for new users and guests used in the task list');
INSERT INTO flyspray_prefs VALUES (15, 'dateformat_extended', '', 'Default date format for new users and guests used in task details');
INSERT INTO flyspray_prefs VALUES (16, 'anon_reg', '1', 'Allow new user registrations');

CREATE SEQUENCE "flyspray_projects_project_id_seq" START WITH 2;
CREATE TABLE  flyspray_projects (
	project_id INT8  NOT NULL DEFAULT nextval('"flyspray_projects_project_id_seq"'::text),
	project_title TEXT NOT NULL default '',
	theme_style TEXT NOT NULL default '0',
	show_logo NUMERIC(1) NOT NULL default '0',
	inline_images NUMERIC(1) NOT NULL default '0',
	default_cat_owner NUMERIC(3) NOT NULL default '0',
	intro_message TEXT NOT NULL,
	project_is_active NUMERIC(1) NOT NULL default '0',
	visible_columns TEXT NOT NULL default '',
	others_view NUMERIC(1) NOT NULL default '0',
	anon_open NUMERIC(1) NOT NULL default '0',
	PRIMARY KEY  (project_id)
);

INSERT INTO flyspray_projects VALUES (1, 'Default Project', 'Bluey', 1, 0, 0, 'This message can be customised under the <b>Projects</b> admin menu...', 1, 'id category tasktype severity summary status progress', 1, 0);

CREATE SEQUENCE "flyspray_registrations_reg_id_seq" START WITH 1;
CREATE TABLE  flyspray_registrations (
	reg_id INT8  NOT NULL DEFAULT nextval('"flyspray_registrations_reg_id_seq"'::text),
	reg_time TEXT NOT NULL default '',
	confirm_code TEXT NOT NULL default '',
	user_name TEXT NOT NULL default '',
	real_name TEXT NOT NULL default '',
	email_address TEXT NOT NULL default '',
	jabber_id TEXT NOT NULL default '',
	notify_type NUMERIC(1) NOT NULL default '0',
	magic_url TEXT NOT NULL default '',
	PRIMARY KEY  (reg_id)
);

CREATE SEQUENCE "flyspray_related_related_id_seq" START WITH 1;
CREATE TABLE  flyspray_related (
	related_id INT8  NOT NULL DEFAULT nextval('"flyspray_related_related_id_seq"'::text),
	this_task NUMERIC(10) NOT NULL default '0',
	related_task NUMERIC(10) NOT NULL default '0',
	PRIMARY KEY  (related_id)
);

CREATE SEQUENCE "flyspray_reminders_reminder_id_seq" START WITH 1;
CREATE TABLE  flyspray_reminders (
	reminder_id INT8  NOT NULL DEFAULT nextval('"flyspray_reminders_reminder_id_seq"'::text),
	task_id NUMERIC(10) NOT NULL default '0',
	to_user_id NUMERIC(3) NOT NULL default '0',
	from_user_id NUMERIC(3) NOT NULL default '0',
	start_time TEXT NOT NULL default '0',
	how_often NUMERIC(12) NOT NULL default '0',
	last_sent TEXT NOT NULL default '0',
	reminder_message TEXT NOT NULL,
	PRIMARY KEY  (reminder_id)
);

CREATE SEQUENCE "flyspray_tasks_task_id_seq" START WITH 2;
CREATE TABLE  flyspray_tasks (
	task_id INT8  NOT NULL DEFAULT nextval('"flyspray_tasks_task_id_seq"'::text),
	attached_to_project NUMERIC(3) NOT NULL default '0',
	task_type NUMERIC(3) NOT NULL default '0',
	date_opened TEXT NOT NULL default '',
	opened_by NUMERIC(3) NOT NULL default '0',
	is_closed NUMERIC(1) NOT NULL default '0',
	date_closed TEXT NOT NULL default '',
	closed_by NUMERIC(3) NOT NULL default '0',
	closure_comment TEXT NOT NULL,
	item_summary TEXT NOT NULL default '',
	detailed_desc TEXT NOT NULL,
	item_status NUMERIC(3) NOT NULL default '0',
	assigned_to NUMERIC(3) NOT NULL default '0',
	resolution_reason NUMERIC(3) NOT NULL default '1',
	product_category NUMERIC(3) NOT NULL default '0',
	product_version NUMERIC(3) NOT NULL default '0',
	closedby_version NUMERIC(3) NOT NULL default '0',
	operating_system NUMERIC(3) NOT NULL default '0',
	task_severity NUMERIC(3) NOT NULL default '0',
	task_priority NUMERIC(3) NOT NULL default '0',
	last_edited_by NUMERIC(3) NOT NULL default '0',
	last_edited_time TEXT NOT NULL default '0',
	percent_complete NUMERIC(3) NOT NULL default '0',
	mark_private NUMERIC(1) NOT NULL default '0',
	PRIMARY KEY  (task_id)
);

INSERT INTO flyspray_tasks VALUES (1, 1, 1, '1103430560', 1, 0, '', 1, ' ', 'Sample Task', 'This isn''t a real task.  You should close it and start opening some real tasks.', 2, 0, 1, 1, 1, 0, 1, 1, 2, 0, '', 0, 0);

CREATE SEQUENCE "flyspray_users_user_id_seq" START WITH 2;
CREATE TABLE  flyspray_users (
	user_id INT8  NOT NULL DEFAULT nextval('"flyspray_users_user_id_seq"'::text),
	user_name TEXT NOT NULL default '',
	user_pass TEXT NOT NULL default '',
	real_name TEXT NOT NULL default '',
	group_in NUMERIC(3) NOT NULL default '0',
	jabber_id TEXT NOT NULL default '',
	email_address TEXT NOT NULL default '',
	notify_type NUMERIC(1) NOT NULL default '0',
	account_enabled NUMERIC(1) NOT NULL default '0',
	dateformat TEXT NOT NULL default '',
	dateformat_extended TEXT NOT NULL default '',
	magic_url TEXT NOT NULL default '',
	PRIMARY KEY  (user_id)
);

INSERT INTO flyspray_users VALUES (1, 'super', '4tuKHcjxpFYag', 'Mr Super User', 1, 'super@example.com', 'super@example.com', 0, 1, '', '', '');

CREATE SEQUENCE "flyspray_users_in_groups_record_id_seq" START WITH 2;
CREATE TABLE  flyspray_users_in_groups (
	record_id INT8  NOT NULL DEFAULT nextval('"flyspray_users_in_groups_record_id_seq"'::text),
	user_id NUMERIC(5) NOT NULL default '0',
	group_id NUMERIC(3) NOT NULL default '0',
	PRIMARY KEY  (record_id)
);

INSERT INTO flyspray_users_in_groups VALUES (1, 1, 1)
