-- This script creates Flyspray tables in PostgreSQL RDBMS. It assumes
-- you've already created database and user. 

-- If you don't have DB and/or user set for Flyspray yet, you can create
-- them manually with following commands:
--     $ psql -h your_db_host template1
--     template1=# CREATE DATABASE flyspray ENCODING 'UNICODE';
--     CREATE DATABASE
--     template1=# CREATE USER flyspray with password 'flyspray';
--     CREATE USER
-- If you encounter any problems creating DB/user please check PostgreSQL
-- documentation: "18.2. Creating a Database" 
-- (http://www.postgresql.org/docs/7.4/static/manage-ag-createdb.html)
-- and "17. Database Users and Privileges"
-- (http://www.postgresql.org/docs/7.4/static/user-manag.html)

-- To create tables and fill them with sample data invoke psql:
--     psql -h your_db_host flyspray_database flyspray_user < flyspray-0.9.6.pgsql

-- If you've chosen to use database 'flyspray' with username 'flyspray' and
-- you have PostgreSQL installed on your local machine then just type:
--     psql -h localhost flyspray flyspray < flyspray-0.9.6.pgsql

CREATE TABLE flyspray_groups (
    group_id serial NOT NULL,
    group_name TEXT DEFAULT '' NOT NULL,
    group_desc TEXT DEFAULT '' NOT NULL,
    is_admin integer DEFAULT '0' NOT NULL,
    can_open_jobs integer DEFAULT '0' NOT NULL,
    can_modify_jobs integer DEFAULT '0' NOT NULL,
    can_add_comments integer DEFAULT '0' NOT NULL,
    can_attach_files integer DEFAULT '0' NOT NULL,
    can_vote integer DEFAULT '0' NOT NULL,
    group_open integer DEFAULT '0' NOT NULL
);

CREATE TABLE flyspray_users (
    user_id serial NOT NULL,
    user_name TEXT DEFAULT '' NOT NULL,
    user_pass TEXT DEFAULT '' NOT NULL,
    real_name TEXT DEFAULT '' NOT NULL,
    group_in integer DEFAULT '0' NOT NULL,
    jabber_id TEXT DEFAULT '' NOT NULL,
    email_address TEXT DEFAULT '' NOT NULL,
    notify_type integer DEFAULT '0' NOT NULL,
    account_enabled integer DEFAULT '0' NOT NULL,
    dateformat TEXT not null default '',
    dateformat_extended TEXT not null default ''
);

CREATE TABLE flyspray_projects (
    project_id serial NOT NULL,
    project_title TEXT DEFAULT '' NOT NULL,
    theme_style TEXT DEFAULT '0' NOT NULL,
    show_logo integer DEFAULT '0' NOT NULL,
    inline_images integer NOT NULL DEFAULT 0,
    default_cat_owner integer,
    intro_message TEXT NOT NULL default '',
    project_is_active integer DEFAULT 0 NOT NULL,
    visible_columns text NOT NULL DEFAULT ''
);

CREATE TABLE flyspray_list_category (
    category_id serial NOT NULL,
    parent_id integer NOT NULL DEFAULT 0,
    project_id integer,
    category_name TEXT DEFAULT '' NOT NULL,
    list_position integer DEFAULT '0' NOT NULL,
    show_in_list integer DEFAULT '0' NOT NULL,
    category_owner integer
);

CREATE TABLE flyspray_list_os (
    os_id serial NOT NULL,
    project_id integer DEFAULT '0' NOT NULL,
    os_name TEXT DEFAULT '' NOT NULL,
    list_position integer DEFAULT '0' NOT NULL,
    show_in_list integer DEFAULT '0' NOT NULL
);

CREATE TABLE flyspray_list_resolution (
    resolution_id serial NOT NULL,
    resolution_name TEXT DEFAULT '' NOT NULL,
    list_position integer DEFAULT '0' NOT NULL,
    show_in_list integer DEFAULT '0' NOT NULL
);

CREATE TABLE flyspray_list_version (
    version_id serial NOT NULL,
    project_id integer DEFAULT '0' NOT NULL,
    version_name TEXT DEFAULT '' NOT NULL,
    list_position integer DEFAULT '0' NOT NULL,
    show_in_list integer DEFAULT '0' NOT NULL,
    version_tense INTEGER NOT NULL DEFAULT 2
);

CREATE TABLE flyspray_list_tasktype (
    tasktype_id serial NOT NULL,
    tasktype_name TEXT DEFAULT '' NOT NULL,
    list_position integer DEFAULT '0' NOT NULL,
    show_in_list integer DEFAULT '0' NOT NULL
);

CREATE TABLE flyspray_tasks (
    task_id serial NOT NULL,
    attached_to_project integer NOT NULL DEFAULT 0,
    task_type integer NOT NULL DEFAULT 0,
    date_opened TEXT NOT NULL DEFAULT '',
    opened_by integer NOT NULL DEFAULT 0,
    is_closed integer NOT NULL DEFAULT 0,
    date_closed TEXT DEFAULT '' NOT NULL,
    closed_by integer,
    closure_comment TEXT NOT NULL DEFAULT '',
    item_summary TEXT DEFAULT '' NOT NULL,
    detailed_desc TEXT NOT NULL DEFAULT '',
    item_status integer NOT NULL DEFAULT 0,
    assigned_to integer,
    resolution_reason integer NOT NULL DEFAULT 1,
    product_category integer,
    product_version integer,
    closedby_version integer,
    operating_system integer,
    task_severity INTEGER NOT NULL DEFAULT 0,
    task_priority INTEGER NOT NULL DEFAULT 2,
    last_edited_by integer,
    last_edited_time TEXT NOT NULL DEFAULT 0,
    percent_complete integer NOT NULL DEFAULT 0,
    CONSTRAINT flyspray_tasks_percent_complete CHECK (((percent_complete >= 0) AND (percent_complete <= 100)))
);

CREATE TABLE flyspray_attachments (
    attachment_id serial NOT NULL,
    task_id integer DEFAULT '0' NOT NULL,
    orig_name TEXT DEFAULT '' NOT NULL,
    file_name TEXT DEFAULT '' NOT NULL,
    file_desc TEXT DEFAULT '' NOT NULL,
    file_type TEXT DEFAULT '' NOT NULL,
    file_size integer DEFAULT '0' NOT NULL,
    added_by integer DEFAULT '0' NOT NULL,
    date_added TEXT DEFAULT '' NOT NULL
);

CREATE TABLE flyspray_comments (
    comment_id serial NOT NULL,
    task_id integer DEFAULT '0' NOT NULL,
    date_added TEXT DEFAULT '' NOT NULL,
    user_id integer DEFAULT '0' NOT NULL,
    comment_text TEXT NOT NULL DEFAULT ''
);

CREATE TABLE flyspray_notifications (
    notify_id serial NOT NULL,
    task_id integer DEFAULT '0' NOT NULL,
    user_id integer DEFAULT '0' NOT NULL
);

CREATE TABLE flyspray_prefs (
    pref_id serial NOT NULL,
    pref_name TEXT DEFAULT '' NOT NULL,
    pref_value TEXT DEFAULT '' NOT NULL,
    pref_desc TEXT DEFAULT '' NOT NULL
);

CREATE TABLE flyspray_registrations (
    reg_id serial NOT NULL,
    reg_time TEXT DEFAULT '' NOT NULL,
    confirm_code TEXT DEFAULT '' NOT NULL
);

CREATE TABLE flyspray_related (
    related_id serial NOT NULL,
    this_task integer DEFAULT '0' NOT NULL,
    related_task integer DEFAULT '0' NOT NULL
);

CREATE TABLE flyspray_reminders (
  reminder_id SERIAL NOT NULL,
  task_id integer NOT NULL default '0',
  to_user_id integer NOT NULL default '0',
  from_user_id integer NOT NULL default '0',
  start_time text NOT NULL default '0',
  how_often integer NOT NULL default '0',
  last_sent text NOT NULL default '0',
  reminder_message TEXT NOT NULL default '',
  PRIMARY KEY (reminder_id)
); 

CREATE TABLE flyspray_history (
  history_id serial not null,
  task_id integer NOT NULL default '0',
  user_id integer NOT NULL default '0',
  event_date text NOT NULL default '',
  event_type integer NOT NULL default '0',
  field_changed text NOT NULL default '',
  old_value text NOT NULL default '',
  new_value text NOT NULL default '',
  PRIMARY KEY  (history_id)
);

INSERT INTO flyspray_groups (group_id, group_name, group_desc, is_admin, can_open_jobs, can_modify_jobs, can_add_comments, can_attach_files, can_vote, group_open) VALUES (1, 'Admin', 'Members have unlimited access to all functionality.', 1, 1, 1, 1, 1, 1, 1);
INSERT INTO flyspray_groups (group_id, group_name, group_desc, is_admin, can_open_jobs, can_modify_jobs, can_add_comments, can_attach_files, can_vote, group_open) VALUES (2, 'Developers', 'The core development team', 0, 1, 1, 1, 1, 1, 1);
INSERT INTO flyspray_groups (group_id, group_name, group_desc, is_admin, can_open_jobs, can_modify_jobs, can_add_comments, can_attach_files, can_vote, group_open) VALUES (3, 'Contributors', 'Additional helpers who submit patches', 0, 1, 0, 1, 1, 1, 1);
INSERT INTO flyspray_groups (group_id, group_name, group_desc, is_admin, can_open_jobs, can_modify_jobs, can_add_comments, can_attach_files, can_vote, group_open) VALUES (4, 'Reporters', 'These people can open new jobs only', 0, 1, 0, 0, 0, 0, 1);
INSERT INTO flyspray_groups (group_id, group_name, group_desc, is_admin, can_open_jobs, can_modify_jobs, can_add_comments, can_attach_files, can_vote, group_open) VALUES (5, 'Pending', 'Users who are awaiting approval of their accounts.', 0, 0, 0, 0, 0, 0, 0);

INSERT INTO flyspray_users (user_id, user_name, user_pass, real_name, group_in, jabber_id, email_address, notify_type, account_enabled) VALUES (1, 'super', '4tuKHcjxpFYag', 'Mr Super User', 1, 'id@jabber.example.com', 'address@example.com', 2, 1);

INSERT INTO flyspray_projects (project_id, project_title, theme_style, show_logo, default_cat_owner, intro_message, project_is_active, visible_columns) VALUES (1, 'Flyspray - The Bug Killer!', 'Bluey', 1, 1, 'Please ensure that your browser has cookies enabled if you want this software to work properly...', 1, 'id category tasktype severity summary dateopened status progress');

INSERT INTO flyspray_list_category (category_id, project_id, category_name, list_position, show_in_list, category_owner) VALUES (1, 1, 'Backend / Core', 4, 1, NULL);
INSERT INTO flyspray_list_category (category_id, project_id, category_name, list_position, show_in_list, category_owner) VALUES (2, 1, 'User Interface', 2, 1, NULL);

INSERT INTO flyspray_list_os (os_id, project_id, os_name, list_position, show_in_list) VALUES (1, 1, 'All', 1, 1);
INSERT INTO flyspray_list_os (os_id, project_id, os_name, list_position, show_in_list) VALUES (2, 1, 'Windows', 2, 1);
INSERT INTO flyspray_list_os (os_id, project_id, os_name, list_position, show_in_list) VALUES (3, 1, 'Mac OS', 3, 1);
INSERT INTO flyspray_list_os (os_id, project_id, os_name, list_position, show_in_list) VALUES (4, 1, 'UNIX', 4, 1);

INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list) VALUES (1, 'None', 1, 1);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list) VALUES (2, 'Not a bug', 2, 1);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list) VALUES (3, 'Won''t fix', 3, 1);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list) VALUES (4, 'Won''t implement', 4, 1);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list) VALUES (5, 'Works for me', 5, 1);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list) VALUES (6, 'Duplicate', 6, 1);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list) VALUES (7, 'Deferred', 7, 1);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list) VALUES (8, 'Fixed', 8, 1);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list) VALUES (9, 'Implemented', 9, 1);

INSERT INTO flyspray_list_version (version_id, project_id, version_name, list_position, show_in_list) VALUES (1, 1, 'CVS', 1, 1);
INSERT INTO flyspray_list_version (version_id, project_id, version_name, list_position, show_in_list) VALUES (2, 1, '1.0', 3, 1);

INSERT INTO flyspray_list_tasktype (tasktype_id, tasktype_name, list_position, show_in_list) VALUES (1, 'Bug Report', 1, 1);
INSERT INTO flyspray_list_tasktype (tasktype_id, tasktype_name, list_position, show_in_list) VALUES (2, 'Feature Request', 2, 1);
INSERT INTO flyspray_list_tasktype (tasktype_id, tasktype_name, list_position, show_in_list) VALUES (3, 'Support Request', 3, 1);

INSERT INTO flyspray_tasks (task_id, attached_to_project, task_type, date_opened, opened_by, date_closed, closed_by, item_summary, detailed_desc, item_status, assigned_to, resolution_reason, product_category, product_version, closedby_version, operating_system, task_severity, last_edited_by, last_edited_time, percent_complete) VALUES (1, 1, 1, '1030802400', 1, '1060837949', 1, 'Test bug report', 'This isn''t a real task.  You should close it and report some real ones.', 2, 1, 1, 2, 1, NULL, 1, 5, 1, '1061298567', 40);
INSERT INTO flyspray_tasks (task_id, attached_to_project, task_type, date_opened, opened_by, date_closed, closed_by, item_summary, detailed_desc, item_status, assigned_to, resolution_reason, product_category, product_version, closedby_version, operating_system, task_severity, last_edited_by, last_edited_time, percent_complete) VALUES (2, 2, 1, '1030802400', 1, '1060837949', 1, 'Another bug report', 'First task for the fake project', 2, 1, 1, 2, 1, NULL, 1, 5, 1, '1061298567', 40);

INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (1, 'anon_open', '2', 'Allow anonymous users to open new tasks');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (2, 'theme_style', 'Bluey', 'Theme / Style');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (3, 'jabber_server', '', 'Jabber server');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (4, 'jabber_port', '5222', 'Jabber server port');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (5, 'jabber_username', '', 'Jabber username');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (6, 'jabber_password', '', 'Jabber password');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (7, 'project_title', 'Flyspray - The bug killer!', 'Project title');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (8, 'anon_group', '4', 'Group for anonymous registrations');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (9, 'base_url', 'http://example.com/flyspray/', 'Base URL for this installation');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (10, 'user_notify', '1', 'Force task notifications as');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (11, 'admin_email', 'flyspray@example.com', 'Reply email address for notifications');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (12, 'assigned_groups', '1 2 3  ', 'Members of these groups can be assigned tasks');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (13, 'default_cat_owner', '0', 'Default category owner');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (14, 'lang_code', 'en', 'Language');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (15, 'spam_proof', '1', 'Use confirmation codes for user registrations');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (16, 'anon_view', '1', 'Allow anonymous users to view this BTS');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (17, 'default_project', '1', 'Default project to show tasks from');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (18, 'dateformat', '', 'Default date format for new users and guests used in the task list');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (19, 'dateformat_extended', '', 'Default date format for new users and guests used in task details');

ALTER TABLE ONLY flyspray_groups
    ADD CONSTRAINT flyspray_groups_pkey PRIMARY KEY (group_id);
ALTER TABLE ONLY flyspray_users
    ADD CONSTRAINT flyspray_users_pkey PRIMARY KEY (user_id);
ALTER TABLE ONLY flyspray_projects
    ADD CONSTRAINT flyspray_projects_pkey PRIMARY KEY (project_id);
ALTER TABLE ONLY flyspray_list_category
    ADD CONSTRAINT flyspray_list_category_pkey PRIMARY KEY (category_id);
ALTER TABLE ONLY flyspray_list_os
    ADD CONSTRAINT flyspray_list_os_pkey PRIMARY KEY (os_id);
ALTER TABLE ONLY flyspray_list_resolution
    ADD CONSTRAINT flyspray_list_resolution_pkey PRIMARY KEY (resolution_id);
ALTER TABLE ONLY flyspray_list_version
    ADD CONSTRAINT flyspray_list_version_pkey PRIMARY KEY (version_id);
ALTER TABLE ONLY flyspray_list_tasktype
    ADD CONSTRAINT flyspray_list_tasktype_pkey PRIMARY KEY (tasktype_id);
ALTER TABLE ONLY flyspray_tasks
    ADD CONSTRAINT flyspray_tasks_pkey PRIMARY KEY (task_id);
ALTER TABLE ONLY flyspray_attachments
    ADD CONSTRAINT flyspray_attachments_pkey PRIMARY KEY (attachment_id);
ALTER TABLE ONLY flyspray_comments
    ADD CONSTRAINT flyspray_comments_pkey PRIMARY KEY (comment_id);
ALTER TABLE ONLY flyspray_notifications
    ADD CONSTRAINT flyspray_notifications_pkey PRIMARY KEY (notify_id);
ALTER TABLE ONLY flyspray_prefs
    ADD CONSTRAINT flyspray_prefs_pkey PRIMARY KEY (pref_id);
ALTER TABLE ONLY flyspray_registrations
    ADD CONSTRAINT flyspray_registrations_pkey PRIMARY KEY (reg_id);
ALTER TABLE ONLY flyspray_related
    ADD CONSTRAINT flyspray_related_pkey PRIMARY KEY (related_id);

SELECT pg_catalog.setval ('flyspray_groups_group_id_seq', 5, true);
SELECT pg_catalog.setval ('flyspray_users_user_id_seq', 1, true);
SELECT pg_catalog.setval ('flyspray_projects_project_id_seq', 2, true);
SELECT pg_catalog.setval ('flyspray_list_category_category_id_seq', 7, true);
SELECT pg_catalog.setval ('flyspray_list_os_os_id_seq', 9, true);
SELECT pg_catalog.setval ('flyspray_list_resolution_resolution_id_seq', 9, true);
SELECT pg_catalog.setval ('flyspray_list_version_version_id_seq', 4, true);
SELECT pg_catalog.setval ('flyspray_list_tasktype_tasktype_id_seq', 4, true);
SELECT pg_catalog.setval ('flyspray_tasks_task_id_seq', 2, true);
SELECT pg_catalog.setval ('flyspray_attachments_attachment_id_seq', 1, false);
SELECT pg_catalog.setval ('flyspray_comments_comment_id_seq', 1, false);
SELECT pg_catalog.setval ('flyspray_notifications_notify_id_seq', 1, false);
SELECT pg_catalog.setval ('flyspray_prefs_pref_id_seq', 19, true);
SELECT pg_catalog.setval ('flyspray_registrations_reg_id_seq', 1, false);
SELECT pg_catalog.setval ('flyspray_related_related_id_seq', 1, false);

COMMENT ON TABLE flyspray_groups IS 'User Groups for the Flyspray bug killer';
COMMENT ON TABLE flyspray_users IS 'Users for the Flyspray bug killer';
COMMENT ON TABLE flyspray_projects IS 'Details on multiple Flyspray projects';
COMMENT ON TABLE flyspray_list_os IS 'Operating system list for the Flyspray bug killer';
COMMENT ON TABLE flyspray_list_tasktype IS 'List of task types for Flyspray the bug killer.';
COMMENT ON TABLE flyspray_tasks IS 'Bugs and feature requests for the Flyspray bug killer';
COMMENT ON TABLE flyspray_attachments IS 'List the names and locations of files attached to tasks';
COMMENT ON TABLE flyspray_notifications IS 'Extra task notifications are stored here';
COMMENT ON TABLE flyspray_prefs IS 'Application preferences are set here';
COMMENT ON TABLE flyspray_registrations IS 'Storage for new user registration confirmation codes';
COMMENT ON TABLE flyspray_related IS 'Related task entries';
COMMENT ON TABLE flyspray_reminders IS 'Scheduled reminders about tasks';

-- vim:ft=sql
