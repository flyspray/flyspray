-- This wonderful pgsql updatescript is brought to you by Gutzmann EDV (Arne Kr?ger feat. Heiko Reese)


-- =============================================================================
--
-- get out of the magic_gpc hell -- Pierre Habouzit :: 2005-10-30 22:00
--
-- this has been generated automatically (vim macros) from the SQL schema, and
-- replaces quotes in *EVERY* textual item
-- this is exhaustive (maybe a bit too much, but It can't harm)
--
-- ========================================================================={{{=

UPDATE flyspray_admin_requests SET
	 reason_given = REPLACE(REPLACE(reason_given, '\\\'', '\''), '\\"', '"'),
	 time_submitted = REPLACE(REPLACE(time_submitted, '\\\'', '\''), '\\"', '"'),
	 time_resolved = REPLACE(REPLACE(time_resolved, '\\\'', '\''), '\\"', '"'),
	 deny_reason = REPLACE(REPLACE(deny_reason, '\\\'', '\''), '\\"', '"');

UPDATE flyspray_assigned SET
	 user_or_group = REPLACE(REPLACE(user_or_group, '\\\'', '\''), '\\"', '"');

UPDATE flyspray_attachments SET
	 orig_name = REPLACE(REPLACE(orig_name, '\\\'', '\''), '\\"', '"'),
	 file_name = REPLACE(REPLACE(file_name,  '\\\'', '\''), '\\"', '"'),
	 file_desc = REPLACE(REPLACE(file_desc, '\\\'', '\''), '\\"', '"'),
	 file_type = REPLACE(REPLACE(file_type, '\\\'', '\''), '\\"', '"'),
	 date_added = REPLACE(REPLACE(date_added,  '\\\'', '\''), '\\"', '"');

UPDATE flyspray_comments SET
	 date_added = REPLACE(REPLACE(date_added, '\\\'', '\''), '\\"', '"'),
	 comment_text = REPLACE(REPLACE(comment_text, '\\\'', '\''), '\\"', '"');

UPDATE flyspray_groups SET
	 group_name = REPLACE(REPLACE(group_name,  '\\\'', '\''), '\\"', '"'),
	 group_desc = REPLACE(REPLACE(group_desc,  '\\\'', '\''), '\\"', '"');

UPDATE flyspray_history SET
	 event_date = REPLACE(REPLACE(event_date, '\\\'', '\''), '\\"', '"'),
	 field_changed = REPLACE(REPLACE(field_changed,  '\\\'', '\''), '\\"', '"'),
	 old_value = REPLACE(REPLACE(old_value,  '\\\'', '\''), '\\"', '"'),
	 new_value = REPLACE(REPLACE(new_value,  '\\\'', '\''), '\\"', '"');

UPDATE flyspray_list_category SET
	 category_name = REPLACE(REPLACE(category_name,  '\\\'', '\''), '\\"', '"');

UPDATE flyspray_list_os SET
	 os_name = REPLACE(REPLACE(os_name, '\\\'', '\''), '\\"', '"');

UPDATE flyspray_list_resolution SET
	 resolution_name = REPLACE(REPLACE(resolution_name,  '\\\'', '\''), '\\"', '"');

UPDATE flyspray_list_tasktype SET
	 tasktype_name = REPLACE(REPLACE(tasktype_name,  '\\\'', '\''), '\\"', '"');

UPDATE flyspray_list_version SET
	 version_name = REPLACE(REPLACE(version_name, '\\\'', '\''), '\\"', '"');

UPDATE flyspray_notification_messages SET
	 message_subject = REPLACE(REPLACE(message_subject,  '\\\'', '\''), '\\"', '"'),
	 message_body = REPLACE(REPLACE(message_body,  '\\\'', '\''), '\\"', '"'),
	 time_created = REPLACE(REPLACE(time_created,  '\\\'', '\''), '\\"', '"');

UPDATE flyspray_notification_recipients SET
	 notify_method = REPLACE(REPLACE(notify_method,  '\\\'', '\''), '\\"', '"'),
	 notify_address = REPLACE(REPLACE(notify_address,  '\\\'', '\''), '\\"', '"');

UPDATE flyspray_prefs SET
	 pref_name = REPLACE(REPLACE(pref_name, '\\\'', '\''), '\\"', '"'),
	 pref_value = REPLACE(REPLACE(pref_value, '\\\'', '\''), '\\"', '"'),
	 pref_desc = REPLACE(REPLACE(pref_desc,  '\\\'', '\''), '\\"', '"');

UPDATE flyspray_projects SET
	 project_title = REPLACE(REPLACE(project_title,  '\\\'', '\''), '\\"', '"'),
	 theme_style = REPLACE(REPLACE(theme_style,  '\\\'', '\''), '\\"', '"'),
	 intro_message = REPLACE(REPLACE(intro_message,  '\\\'', '\''), '\\"', '"'),
	 visible_columns = REPLACE(REPLACE(visible_columns,  '\\\'', '\''), '\\"', '"'),
	 notify_email = REPLACE(REPLACE(notify_email,  '\\\'', '\''), '\\"', '"'),
	 notify_jabber = REPLACE(REPLACE(notify_jabber,  '\\\'', '\''), '\\"', '"');

UPDATE flyspray_registrations SET
	 reg_time = REPLACE(REPLACE(reg_time,  '\\\'', '\''), '\\"', '"'),
	 confirm_code = REPLACE(REPLACE(confirm_code, '\\\'', '\''), '\\"', '"'),
	 user_name = REPLACE(REPLACE(user_name,  '\\\'', '\''), '\\"', '"'),
	 real_name = REPLACE(REPLACE(real_name,  '\\\'', '\''), '\\"', '"'),
	 email_address = REPLACE(REPLACE(email_address,  '\\\'', '\''), '\\"', '"'),
	 jabber_id = REPLACE(REPLACE(jabber_id, '\\\'', '\''), '\\"', '"'),
	 magic_url = REPLACE(REPLACE(magic_url,  '\\\'', '\''), '\\"', '"');

UPDATE flyspray_reminders SET
	 start_time = REPLACE(REPLACE(start_time,  '\\\'', '\''), '\\"', '"'),
	 last_sent = REPLACE(REPLACE(last_sent,  '\\\'', '\''), '\\"', '"'),
	 reminder_message = REPLACE(REPLACE(reminder_message, '\\\'', '\''), '\\"', '"');

UPDATE flyspray_tasks SET
	 date_opened = REPLACE(REPLACE(date_opened,  '\\\'', '\''), '\\"', '"'),
	 date_closed = REPLACE(REPLACE(date_closed,  '\\\'', '\''), '\\"', '"'),
	 closure_comment = REPLACE(REPLACE(closure_comment,  '\\\'', '\''), '\\"', '"'),
	 item_summary = REPLACE(REPLACE(item_summary,  '\\\'', '\''), '\\"', '"'),
	 detailed_desc = REPLACE(REPLACE(detailed_desc, '\\\'', '\''), '\\"', '"'),
	 last_edited_time = REPLACE(REPLACE(last_edited_time, '\\\'', '\''), '\\"', '"'),
	 due_date = REPLACE(REPLACE(due_date,  '\\\'', '\''), '\\"', '"');

UPDATE flyspray_users SET
	 user_name = REPLACE(REPLACE(user_name,  '\\\'', '\''), '\\"', '"'),
	 user_pass = REPLACE(REPLACE(user_pass,  '\\\'', '\''), '\\"', '"'),
	 real_name = REPLACE(REPLACE(real_name, '\\\'', '\''), '\\"', '"'),
	 jabber_id = REPLACE(REPLACE(jabber_id,  '\\\'', '\''), '\\"', '"'),
	 email_address = REPLACE(REPLACE(email_address,  '\\\'', '\''), '\\"', '"'),
	 dateformat = REPLACE(REPLACE(dateformat,  '\\\'', '\''), '\\"', '"'),
	 dateformat_extended = REPLACE(REPLACE(dateformat_extended,  '\\\'', '\''), '\\"', '"'),
	 magic_url = REPLACE(REPLACE(magic_url, '\\\'', '\''), '\\"', '"'),
	 last_search = REPLACE(REPLACE(last_search,  '\\\'', '\''), '\\"', '"');

-- =========================================================================}}}=

-- =============================================================================
--
-- Florian Schmitz, Added on 05 November 05
--
-- RSS/Atom feeds
--
-- (Updated 08 November 2005 by Mac Newbold - rename limit column to max_items,
--  because limit is a reserved word in sql)
--
-- =============================================================================

ALTER TABLE flyspray_projects ADD feed_img_url TEXT;
UPDATE flyspray_projects set feed_img_url = ' ' where feed_img_url is Null;
ALTER TABLE flyspray_projects ALTER COLUMN feed_img_url set NOT NULL;
ALTER TABLE flyspray_projects ALTER COLUMN feed_img_url SET DEFAULT '';

ALTER TABLE flyspray_projects ADD feed_description TEXT;
UPDATE flyspray_projects set feed_description = ' ' where feed_description is Null;
ALTER TABLE flyspray_projects ALTER COLUMN feed_description set NOT NULL;
ALTER TABLE flyspray_projects ALTER COLUMN feed_description SET DEFAULT '';


INSERT INTO flyspray_prefs VALUES (22, 'cache_feeds', '0', '0 = do not cache feeds, 1 = cache feeds on disk, 2 = cache feeds in DB');

CREATE TABLE flyspray_cache (
  id smallint NOT NULL DEFAULT nextval('"flyspray_cache_id_seq"'::text),
  type varchar NOT NULL default '',
  content text NOT NULL,
  topic varchar NOT NULL default '',
  last_updated int NOT NULL default '0',
  project int NOT NULL default 0,
  max_items int NOT NULL default '0',
  PRIMARY KEY  (id)
);

--
-- Name: flyspray_cache_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_cache_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Name: flyspray_admin_cache_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_cache_id_seq', 1, false);

-- Mac Newbold, 08 November 2005
-- Fix closure_comment from '0' to '' so they are properly blank.

UPDATE flyspray_tasks SET closure_comment='' WHERE closure_comment='0';

-- Florian Schmitz, 10 November 2005
-- FS#718

ALTER TABLE flyspray_projects DROP inline_images;

-- Florian Schmitz, 11 November 2005
-- FS#610

ALTER TABLE flyspray_projects DROP show_logo;

-- Florian Schmitz, 13 November 2005

ALTER TABLE flyspray_users ADD COLUMN user_name_new varchar ;
UPDATE flyspray_users SET user_name_new = CAST(user_name AS varchar);
ALTER TABLE flyspray_users DROP COLUMN user_name;
Alter table flyspray_users rename user_name_new to user_name;
UPDATE flyspray_users SET user_name = ' ' where user_name is Null;
ALTER TABLE flyspray_users ALTER COLUMN user_name SET NOT NULL;
ALTER TABLE flyspray_users ALTER COLUMN user_name SET DEFAULT '';

ALTER TABLE flyspray_registrations ADD COLUMN user_name_new varchar ;
UPDATE flyspray_registrations SET user_name_new = CAST(user_name AS varchar);
ALTER TABLE flyspray_registrations DROP COLUMN user_name;
Alter table flyspray_registrations rename user_name_new to user_name;
UPDATE flyspray_registrations SET user_name = ' ' where user_name is Null;
ALTER TABLE flyspray_registrations ALTER COLUMN user_name SET NOT NULL;
ALTER TABLE flyspray_registrations ALTER COLUMN user_name SET DEFAULT '';

-- Tony Collins, 19 November 2005
-- Changed field for FS#329

ALTER TABLE flyspray_tasks ADD COLUMN assigned_to_new varchar ;
UPDATE flyspray_tasks SET assigned_to_new = CAST(assigned_to AS varchar);
ALTER TABLE flyspray_tasks DROP COLUMN assigned_to;
Alter table flyspray_tasks rename assigned_to_new to assigned_to;
UPDATE flyspray_tasks SET assigned_to = ' ' where assigned_to is Null;
ALTER TABLE flyspray_tasks ALTER COLUMN assigned_to SET NOT NULL;
ALTER TABLE flyspray_tasks ALTER COLUMN assigned_to SET DEFAULT '';

-- =============================================================================
--
-- add indexes -- Pierre Habouzit :: 2005-11-19 13:07
--
-- unique to ensure some invariants in the DB rather than in the code
-- others to speed up queries
--
-- =========================================================================={{{
-- lists tables
CREATE INDEX flyspray_list_category_index ON flyspray_list_category (project_id);
CREATE INDEX flyspray_list_os_index ON flyspray_list_os (project_id);
CREATE INDEX flyspray_list_resolution_index ON flyspray_list_resolution (project_id);
CREATE INDEX flyspray_list_tasktype_index ON flyspray_list_tasktype (project_id);
CREATE INDEX flyspray_list_version_index ON flyspray_list_version (project_id, version_tense);

-- join tables
CREATE INDEX flyspray_related_index ON flyspray_related (this_task, related_task);
CREATE INDEX flyspray_dependencies_index ON flyspray_dependencies (task_id, dep_task_id);
CREATE INDEX flyspray_notificationsn_index ON flyspray_notifications (task_id, user_id);

-- user and group related indexes
CREATE INDEX flyspray_users_in_groups_index_unique ON flyspray_users_in_groups (group_id, user_id);
CREATE INDEX flyspray_users_in_groups_index ON flyspray_users_in_groups (user_id);
CREATE INDEX flyspray_groups_index ON flyspray_groups (belongs_to_project);

-- task related indexes
CREATE INDEX flyspray_attachments_index ON flyspray_attachments (task_id, comment_id);
CREATE INDEX flyspray_comments_index ON flyspray_comments (task_id);

CREATE INDEX flyspray_tasks_index ON flyspray_tasks (attached_to_project, task_severity, task_type, product_category, item_status, is_closed, assigned_to, closedby_version, due_date);

-- ==========================================================================}}}

-- Florian Schmitz, 21 November 2005, FS#344

ALTER TABLE flyspray_projects ADD COLUMN notify_subject VARCHAR( 100 ); 
UPDATE flyspray_projects SET notify_subject = ' ' where notify_subject is Null;
ALTER TABLE flyspray_projects ALTER COLUMN notify_subject SET NOT NULL;
ALTER TABLE flyspray_projects ALTER COLUMN notify_subject SET DEFAULT '';
-- Tony Collins, 22 November 2005 (FS#329)

ALTER TABLE flyspray_assigned DROP user_or_group;

ALTER TABLE flyspray_assigned ADD COLUMN assignee_id_new BIGINT ;
UPDATE flyspray_assigned SET assignee_id_new = CAST(assignee_id AS BIGINT);
ALTER TABLE flyspray_assigned DROP COLUMN assignee_id;
Alter table flyspray_assigned rename assignee_id_new to user_id;
ALTER TABLE flyspray_assigned ALTER COLUMN user_id SET DEFAULT '0';
ALTER TABLE flyspray_assigned ALTER COLUMN user_id SET NOT NULL;


CREATE INDEX flyspray_assigned_index ON flyspray_assigned (task_id, user_id);

-- Tony Collins, 23 November 2005 (FS#329)

ALTER TABLE flyspray_groups ADD add_to_assignees INT;
UPDATE flyspray_groups set add_to_assignees = 0 where add_to_assignees is Null;
ALTER TABLE flyspray_groups ALTER COLUMN add_to_assignees set NOT NULL;
ALTER TABLE flyspray_groups ALTER COLUMN add_to_assignees SET DEFAULT 0;

UPDATE flyspray_groups SET add_to_assignees = '1' WHERE assign_others_to_self =1 ;

-- Florian Schmitz, 18 December 2005 (FS#723)
ALTER TABLE flyspray_projects ADD lang_code VARCHAR( 10 ) ;
UPDATE flyspray_projects set lang_code = 'en' where lang_code is Null;
ALTER TABLE flyspray_projects ALTER COLUMN lang_code set NOT NULL;
ALTER TABLE flyspray_projects ALTER COLUMN lang_code SET DEFAULT 'en';

-- Florian Schmitz, 24 December 2005 (FS#287)

CREATE TABLE flyspray_list_status (
  status_id BIGINT NOT NULL default nextval('"flyspray_list_status_seq"'::text)  ,
  status_name varchar(20) NOT NULL default '',
  list_position BIGINT NOT NULL default '0',
  show_in_list BIGINT NOT NULL default '0',
  project_id BIGINT NOT NULL default '0',
  PRIMARY KEY  (status_id)
) ;


--
-- Name: flyspray_list_status_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_list_status_seq
    START WITH 6
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Name: flyspray_list_status_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_list_status_seq', 1, false);

INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (1, 'Unconfirmed', 1, 1, 0);
INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (2, 'New', 2, 1, 0);
INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (3, 'Assigned', 3, 1, 0);
INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (4, 'Researching', 4, 1, 0);
INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (5, 'Waiting on Customer', 5, 1, 0);
INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (6, 'Requires testing', 6, 1, 0);
INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (7, 'Reopened', 7, 1, 0);

-- Florian Schmitz, 5 January 2006
INSERT INTO flyspray_prefs ( pref_id, pref_name , pref_value , pref_desc )
VALUES (23, 'last_update_check', '0', 'Time when the last update check was done.');

-- Florian Schmitz, 14 January 2006
CREATE TABLE flyspray_searches (
id INT NOT NULL default nextval('"flyspray_searches_seq"'::text) ,
user_id INT NOT NULL ,
name VARCHAR( 50 ) NOT NULL ,
search_string TEXT NOT NULL ,
time INT NOT NULL ,
PRIMARY KEY ( id )
) ;

--
-- Name: flyspray_searches_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_searches_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Name: flyspray_searches_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_searches_seq', 1, false);

-- Tony Collins, 22 January 2006
ALTER TABLE flyspray_groups ADD add_votes INT;
UPDATE flyspray_groups set add_votes = 0 where add_votes is Null;
ALTER TABLE flyspray_groups ALTER COLUMN add_votes set NOT NULL;
ALTER TABLE flyspray_groups ALTER COLUMN add_votes SET DEFAULT 0;


CREATE TABLE flyspray_votes (
vote_id INT NOT NULL default nextval('"flyspray_votes_seq"'::text) ,
user_id INT NOT NULL ,
task_id INT NOT NULL ,
date_time INT DEFAULT 0 NOT NULL ,
PRIMARY KEY ( vote_id )
) ;

--
-- Name: flyspray_votes_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_votes_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

--
-- Name: flyspray_votes_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_votes_seq', 1, false);


UPDATE flyspray_groups SET add_votes = '1' WHERE group_id = 1;


-- Gutzmann EDV, 23 January 2006
update flyspray_prefs set pref_value ='0.9.9(devel)' where pref_name = 'fs_ver';

-- Florian Schmitz, 29 January 2006
UPDATE flyspray_tasks SET due_date = 0 WHERE due_date = '';

-- Florian Schmitz, 31 January 2006
ALTER TABLE flyspray_projects ADD comment_closed INT;
ALTER TABLE flyspray_projects ALTER COLUMN comment_closed SET DEFAULT '0';
UPDATE flyspray_projects SET comment_closed = 0 where comment_closed IS NULL;
ALTER TABLE flyspray_projects ALTER COLUMN comment_closed SET NOT NULL;

-- Florian Schmitz, 21 February 2006
ALTER TABLE flyspray_users ADD COLUMN last_search_new TEXT ;
UPDATE flyspray_users SET last_search_new = CAST(last_search AS TEXT);
ALTER TABLE flyspray_users DROP COLUMN last_search;
Alter table flyspray_users rename last_search_new to last_search;
ALTER TABLE flyspray_users ALTER COLUMN last_search SET DEFAULT ' ';
update flyspray_users set last_search = ' ' where last_search is Null;
ALTER TABLE flyspray_users ALTER COLUMN last_search SET NOT NULL ;

-- Florian Schmitz, 28 February 2006, FS#824
ALTER TABLE flyspray_tasks ADD COLUMN closure_comment_new TEXT ;
UPDATE flyspray_tasks SET closure_comment_new = CAST(closure_comment AS TEXT);
ALTER TABLE flyspray_tasks DROP COLUMN closure_comment;
Alter table flyspray_tasks rename closure_comment_new to closure_comment;
ALTER TABLE flyspray_tasks ALTER COLUMN closure_comment SET DEFAULT ' ';
update flyspray_tasks set closure_comment = ' ' where closure_comment is Null;
ALTER TABLE flyspray_tasks ALTER COLUMN closure_comment SET NOT NULL;

-- Florian Schmitz, 2 March 2006, FS#829
ALTER TABLE flyspray_groups ADD edit_own_comments INT;
ALTER TABLE flyspray_groups ALTER COLUMN edit_own_comments SET DEFAULT 0;
UPDATE flyspray_groups SET edit_own_comments = 0 where edit_own_comments IS NULL;
ALTER TABLE flyspray_groups ALTER COLUMN edit_own_comments SET NOT NULL;

-- Florian Schmitz, 2 March 2006, FS#836
ALTER TABLE flyspray_projects ADD COLUMN notify_email_new TEXT;
UPDATE flyspray_projects SET notify_email_new = CAST(notify_email AS TEXT);
ALTER TABLE flyspray_projects DROP COLUMN notify_email;
Alter table flyspray_projects rename notify_email_new to notify_email;
ALTER TABLE flyspray_projects ALTER COLUMN notify_email SET DEFAULT '';
UPDATE flyspray_projects SET notify_email = '' where notify_email IS NULL;
ALTER TABLE flyspray_projects ALTER COLUMN notify_email SET NOT NULL;

ALTER TABLE flyspray_projects ADD COLUMN notify_jabber_new TEXT;
UPDATE flyspray_projects SET notify_jabber_new = CAST(notify_jabber AS TEXT);
ALTER TABLE flyspray_projects DROP COLUMN notify_jabber;
Alter table flyspray_projects rename notify_jabber_new to notify_jabber;
ALTER TABLE flyspray_projects ALTER COLUMN notify_jabber SET DEFAULT '';
UPDATE flyspray_projects SET notify_jabber = '' where notify_jabber IS NULL;
ALTER TABLE flyspray_projects ALTER COLUMN notify_jabber SET NOT NULL;

-- Florian Schmitz, 4 March 2006
UPDATE flyspray_groups SET add_votes = 1 WHERE group_id = 2 OR group_id = 3 OR group_id = 6;

DELETE FROM flyspray_list_status WHERE status_id = 7;

-- Florian Schmitz, 24 March 2006
ALTER TABLE flyspray_comments ADD COLUMN last_edited_time int; 
ALTER TABLE flyspray_comments ALTER COLUMN last_edited_time SET DEFAULT 0;
UPDATE flyspray_comments SET last_edited_time = 0 where last_edited_time IS NULL;
ALTER TABLE flyspray_comments ALTER COLUMN last_edited_time SET NOT NULL;

-- Florian Schmitz, 25 March 2006
ALTER TABLE flyspray_cache ADD UNIQUE (type, topic, project, max_items);

-- FS#750 Per-user option to enable notifications for own changes
ALTER TABLE flyspray_users ADD COLUMN notify_own SMALLINT; 
ALTER TABLE flyspray_users ALTER COLUMN notify_own SET DEFAULT '0';
UPDATE flyspray_users SET notify_own = 0 where notify_own IS NULL;
ALTER TABLE flyspray_users ALTER COLUMN notify_own SET NOT NULL;
UPDATE flyspray_users SET notify_own = notify_type;

-- Florian Schmitz, 26 March 2006
ALTER TABLE flyspray_tasks ADD COLUMN anon_email VARCHAR(100); 
ALTER TABLE flyspray_tasks ALTER COLUMN anon_email SET DEFAULT '';
UPDATE flyspray_tasks SET anon_email = 0 where anon_email IS NULL;
ALTER TABLE flyspray_tasks ALTER COLUMN anon_email SET NOT NULL;
ALTER TABLE flyspray_tasks ADD COLUMN task_token VARCHAR(32);
ALTER TABLE flyspray_tasks ALTER COLUMN task_token SET DEFAULT '0';
UPDATE flyspray_tasks SET task_token = 0 where task_token IS NULL;
ALTER TABLE flyspray_tasks ALTER COLUMN task_token SET NOT NULL;

-- Florian Schmitz, 6 April 2006
ALTER TABLE flyspray_users ADD register_date INT;
ALTER TABLE flyspray_users ALTER COLUMN register_date SET DEFAULT 0;
UPDATE flyspray_users SET register_date = 0 where register_date IS NULL;
ALTER TABLE flyspray_users ALTER COLUMN register_date SET NOT NULL;
 
ALTER TABLE flyspray_users ADD UNIQUE (user_name);

ALTER TABLE flyspray_admin_requests ADD COLUMN time_submitted_new INT;
UPDATE flyspray_admin_requests SET time_submitted = 0 where time_submitted = '';
UPDATE flyspray_admin_requests SET time_submitted_new = CAST(time_submitted AS INT);
ALTER TABLE flyspray_admin_requests DROP COLUMN time_submitted;
Alter table flyspray_admin_requests rename time_submitted_new to time_submitted;
ALTER TABLE flyspray_admin_requests ALTER COLUMN time_submitted SET DEFAULT 0;
UPDATE flyspray_admin_requests SET time_submitted = 0 where time_submitted IS NULL;
ALTER TABLE flyspray_admin_requests ALTER COLUMN time_submitted SET NOT NULL;

ALTER TABLE flyspray_admin_requests ADD COLUMN time_resolved_new INT;
UPDATE flyspray_admin_requests SET time_resolved = 0 where time_resolved = '';
UPDATE flyspray_admin_requests SET time_resolved_new = CAST(time_resolved AS INT);
ALTER TABLE flyspray_admin_requests DROP COLUMN time_resolved;
Alter table flyspray_admin_requests rename time_resolved_new to time_resolved;
ALTER TABLE flyspray_admin_requests ALTER COLUMN time_resolved SET DEFAULT 0;
UPDATE flyspray_admin_requests SET time_resolved = 0 where time_resolved IS NULL;
ALTER TABLE flyspray_admin_requests ALTER COLUMN time_resolved SET NOT NULL;

ALTER TABLE flyspray_attachments ADD COLUMN date_added_new INT;
UPDATE flyspray_attachments SET date_added = 0 where date_added = '';
UPDATE flyspray_attachments SET date_added_new = CAST(date_added AS INT);
ALTER TABLE flyspray_attachments DROP COLUMN date_added;
Alter table flyspray_attachments rename date_added_new to date_added;
ALTER TABLE flyspray_attachments ALTER COLUMN date_added SET DEFAULT 0;
UPDATE flyspray_attachments SET date_added = 0 where date_added IS NULL;
ALTER TABLE flyspray_attachments ALTER COLUMN date_added SET NOT NULL;

ALTER TABLE flyspray_comments ADD COLUMN date_added_new INT;
UPDATE flyspray_comments SET date_added = 0 where date_added = '';
UPDATE flyspray_comments SET date_added_new = CAST(date_added AS INT);
ALTER TABLE flyspray_comments DROP COLUMN date_added;
Alter table flyspray_comments rename date_added_new to date_added;
ALTER TABLE flyspray_comments ALTER COLUMN date_added SET DEFAULT 0;
UPDATE flyspray_comments SET date_added = 0 where date_added IS NULL;
ALTER TABLE flyspray_comments ALTER COLUMN date_added SET NOT NULL;

ALTER TABLE flyspray_comments ADD COLUMN last_edited_time_new INT;
UPDATE flyspray_comments SET last_edited_time_new = CAST(last_edited_time AS INT);
ALTER TABLE flyspray_comments DROP COLUMN last_edited_time;
Alter table flyspray_comments rename last_edited_time_new to last_edited_time;
ALTER TABLE flyspray_comments ALTER COLUMN last_edited_time SET DEFAULT 0;
UPDATE flyspray_comments SET last_edited_time = 0 where last_edited_time IS NULL;
ALTER TABLE flyspray_comments ALTER COLUMN last_edited_time SET NOT NULL;

ALTER TABLE flyspray_history ADD COLUMN event_date_new INT;
UPDATE flyspray_history SET event_date = 0 where event_date = '';
UPDATE flyspray_history SET event_date_new = CAST(event_date AS INT);
ALTER TABLE flyspray_history DROP COLUMN event_date;
Alter table flyspray_history rename event_date_new to event_date;
ALTER TABLE flyspray_history ALTER COLUMN event_date SET DEFAULT 0;
UPDATE flyspray_history SET event_date = 0 where event_date IS NULL;
ALTER TABLE flyspray_history ALTER COLUMN event_date SET NOT NULL;

ALTER TABLE flyspray_notification_messages ADD COLUMN time_created_new INT;
UPDATE flyspray_notification_messages SET time_created = 0 where time_created = '';
UPDATE flyspray_notification_messages SET time_created_new = CAST(time_created AS INT);
ALTER TABLE flyspray_notification_messages DROP COLUMN time_created;
Alter table flyspray_notification_messages rename time_created_new to time_created;
ALTER TABLE flyspray_notification_messages ALTER COLUMN time_created SET DEFAULT 0;
UPDATE flyspray_notification_messages SET time_created = 0 where time_created IS NULL;
ALTER TABLE flyspray_notification_messages ALTER COLUMN time_created SET NOT NULL;

ALTER TABLE flyspray_registrations ADD COLUMN reg_time_new INT;
UPDATE flyspray_registrations SET reg_time = 0 where reg_time = '';
UPDATE flyspray_registrations SET reg_time_new = CAST(reg_time AS INT);
ALTER TABLE flyspray_registrations DROP COLUMN reg_time;
Alter table flyspray_registrations rename reg_time_new to reg_time;
ALTER TABLE flyspray_registrations ALTER COLUMN reg_time SET DEFAULT 0;
UPDATE flyspray_registrations SET reg_time = 0 where reg_time IS NULL;
ALTER TABLE flyspray_registrations ALTER COLUMN reg_time SET NOT NULL;

ALTER TABLE flyspray_reminders ADD COLUMN start_time_new INT;
UPDATE flyspray_reminders SET start_time = 0 where start_time = '';
UPDATE flyspray_reminders SET start_time_new = CAST(start_time AS INT);
ALTER TABLE flyspray_reminders DROP COLUMN start_time;
Alter table flyspray_reminders rename start_time_new to start_time;
ALTER TABLE flyspray_reminders ALTER COLUMN start_time SET DEFAULT 0;
UPDATE flyspray_reminders SET start_time = 0 where start_time IS NULL;
ALTER TABLE flyspray_reminders ALTER COLUMN start_time SET NOT NULL;

ALTER TABLE flyspray_reminders ADD COLUMN last_sent_new INT;
UPDATE flyspray_reminders SET last_sent = 0 where last_sent = '';
UPDATE flyspray_reminders SET last_sent_new = CAST(last_sent AS INT);
ALTER TABLE flyspray_reminders DROP COLUMN last_sent;
Alter table flyspray_reminders rename last_sent_new to last_sent;
ALTER TABLE flyspray_reminders ALTER COLUMN last_sent SET DEFAULT 0;
UPDATE flyspray_reminders SET last_sent = 0 where last_sent IS NULL;
ALTER TABLE flyspray_reminders ALTER COLUMN last_sent SET NOT NULL;

ALTER TABLE flyspray_tasks ADD COLUMN date_closed_new INT;
UPDATE flyspray_tasks SET date_closed = 0 where date_closed = '';
UPDATE flyspray_tasks SET date_closed_new = CAST(date_closed AS INT);
ALTER TABLE flyspray_tasks DROP COLUMN date_closed;
Alter table flyspray_tasks rename date_closed_new to date_closed;
ALTER TABLE flyspray_tasks ALTER COLUMN date_closed SET DEFAULT 0;
UPDATE flyspray_tasks SET date_closed = 0 where date_closed IS NULL;
ALTER TABLE flyspray_tasks ALTER COLUMN date_closed SET NOT NULL;

ALTER TABLE flyspray_tasks ADD COLUMN date_opened_new INT;
UPDATE flyspray_tasks SET date_opened = 0 where date_opened = '';
UPDATE flyspray_tasks SET date_opened_new = CAST(date_opened AS INT);
ALTER TABLE flyspray_tasks DROP COLUMN date_opened;
Alter table flyspray_tasks rename date_opened_new to date_opened;
ALTER TABLE flyspray_tasks ALTER COLUMN date_opened SET DEFAULT 0;
UPDATE flyspray_tasks SET date_opened = 0 where date_opened IS NULL;
ALTER TABLE flyspray_tasks ALTER COLUMN date_opened SET NOT NULL;

ALTER TABLE flyspray_tasks ADD COLUMN due_date_new INT;
UPDATE flyspray_tasks SET due_date = 0 where due_date = '';
UPDATE flyspray_tasks SET due_date_new = CAST(due_date AS INT);
ALTER TABLE flyspray_tasks DROP COLUMN due_date;
Alter table flyspray_tasks rename due_date_new to due_date;
ALTER TABLE flyspray_tasks ALTER COLUMN due_date SET DEFAULT 0;
UPDATE flyspray_tasks SET due_date = 0 where due_date IS NULL;
ALTER TABLE flyspray_tasks ALTER COLUMN due_date SET NOT NULL;

ALTER TABLE flyspray_votes ADD COLUMN date_time_new INT;
UPDATE flyspray_votes SET date_time_new = CAST(date_time AS INT);
ALTER TABLE flyspray_votes DROP COLUMN date_time;
Alter table flyspray_votes rename date_time_new to date_time;
ALTER TABLE flyspray_votes ALTER COLUMN date_time SET DEFAULT 0;
UPDATE flyspray_votes SET date_time = 0 where date_time IS NULL;
ALTER TABLE flyspray_votes ALTER COLUMN date_time SET NOT NULL;

ALTER TABLE flyspray_history ADD COLUMN field_changed_new VARCHAR( 50 );
UPDATE flyspray_history SET field_changed_new = CAST(field_changed AS VARCHAR( 50 ));
ALTER TABLE flyspray_history DROP COLUMN field_changed;
Alter table flyspray_history rename field_changed_new to field_changed;
UPDATE flyspray_history SET field_changed = '' where field_changed IS NULL;
ALTER TABLE flyspray_history ALTER COLUMN field_changed SET NOT NULL;

-- Florian Schmitz, 7 April 2006
ALTER TABLE flyspray_projects ADD notify_reply TEXT;
UPDATE flyspray_projects SET notify_reply = '' where notify_reply IS NULL;
ALTER TABLE flyspray_projects ALTER COLUMN notify_reply SET NOT NULL;
ALTER TABLE flyspray_projects DROP notify_jabber_when ;
ALTER TABLE flyspray_projects DROP notify_email_when ;

ALTER TABLE flyspray_projects ADD notify_types VARCHAR( 100 );
ALTER TABLE flyspray_projects ALTER COLUMN notify_types SET DEFAULT 0;
UPDATE flyspray_projects SET notify_types = 0 where notify_types IS NULL;
ALTER TABLE flyspray_projects ALTER COLUMN notify_types SET NOT NULL;

-- Florian Schmitz, 11 April 2006
ALTER TABLE flyspray_projects ADD auto_assign SMALLINT;
ALTER TABLE flyspray_projects ALTER COLUMN auto_assign SET DEFAULT 0;
UPDATE flyspray_projects SET auto_assign = 0 where auto_assign IS NULL;
ALTER TABLE flyspray_projects ALTER COLUMN auto_assign SET NOT NULL;

-- Florian Schmitz, 14 April 2006
INSERT INTO flyspray_prefs ( pref_id , pref_name , pref_value , pref_desc )
VALUES (24 , 'jabber_ssl', '0', 'Whether or not to use SSL for Jabber connections');

-- Florian Schmitz, 15 April 2006
ALTER TABLE flyspray_tasks DROP assigned_to;

-- Florian Schmitz, 29 April 2006
ALTER TABLE flyspray_projects ADD last_updated INT;
ALTER TABLE flyspray_projects ALTER COLUMN last_updated SET DEFAULT 0;
UPDATE flyspray_projects SET last_updated = 0 where last_updated IS NULL;
ALTER TABLE flyspray_projects ALTER COLUMN last_updated SET NOT NULL;

ALTER TABLE flyspray_groups ADD COLUMN add_to_assignees_new INT;
UPDATE flyspray_groups SET add_to_assignees_new = CAST(add_to_assignees AS INT);
ALTER TABLE flyspray_groups DROP COLUMN add_to_assignees;
Alter table flyspray_groups rename add_to_assignees_new to add_to_assignees;
ALTER TABLE flyspray_groups ALTER COLUMN add_to_assignees SET DEFAULT 0;
UPDATE flyspray_groups SET add_to_assignees = 0 where add_to_assignees IS NULL;
ALTER TABLE flyspray_groups ALTER COLUMN add_to_assignees SET NOT NULL;

ALTER TABLE flyspray_groups ADD COLUMN add_votes_new INT;
UPDATE flyspray_groups SET add_votes_new = CAST(add_votes AS INT);
ALTER TABLE flyspray_groups DROP COLUMN add_votes;
Alter table flyspray_groups rename add_votes_new to add_votes;
ALTER TABLE flyspray_groups ALTER COLUMN add_votes SET DEFAULT 0;
UPDATE flyspray_groups SET add_votes = 0 where add_votes IS NULL;
ALTER TABLE flyspray_groups ALTER COLUMN add_votes SET NOT NULL;

-- Florian Schmitz, 5 May 2006
ALTER TABLE flyspray_cache ADD COLUMN project_id INT;
UPDATE flyspray_cache SET project_id = CAST(project AS INT);
ALTER TABLE flyspray_cache DROP COLUMN project;
ALTER TABLE flyspray_cache ALTER COLUMN project_id SET DEFAULT 0;
UPDATE flyspray_cache SET project_id = 0 where project_id IS NULL;
ALTER TABLE flyspray_cache ALTER COLUMN project_id SET NOT NULL;


-- Florian Schmitz, 7 May 2006
ALTER TABLE flyspray_groups ADD edit_assignments SMALLINT;
ALTER TABLE flyspray_groups ALTER COLUMN edit_assignments SET DEFAULT 0;
UPDATE flyspray_groups SET edit_assignments = 0 where edit_assignments IS NULL;
ALTER TABLE flyspray_groups ALTER COLUMN edit_assignments SET NOT NULL;
UPDATE flyspray_groups SET edit_assignments = 1 WHERE group_id =2;

-- Florian Schmitz, 27 May 2006
ALTER TABLE flyspray_related ADD is_duplicate SMALLINT;
ALTER TABLE flyspray_related ALTER COLUMN is_duplicate SET DEFAULT 0;
UPDATE flyspray_related SET is_duplicate = 0 where is_duplicate IS NULL;
ALTER TABLE flyspray_related ALTER COLUMN is_duplicate SET NOT NULL;


-- Florian Schmitz, 1 June 2006
ALTER TABLE flyspray_list_category ADD lft INT;
ALTER TABLE flyspray_list_category ALTER COLUMN lft SET DEFAULT 0;
UPDATE flyspray_list_category SET lft = 0 where lft IS NULL;
ALTER TABLE flyspray_list_category ALTER COLUMN lft SET NOT NULL;

ALTER TABLE flyspray_list_category ADD rgt INT;
ALTER TABLE flyspray_list_category ALTER COLUMN rgt SET DEFAULT 0;
UPDATE flyspray_list_category SET rgt = 0 where rgt IS NULL;
ALTER TABLE flyspray_list_category ALTER COLUMN rgt SET NOT NULL;

-- Florian Schmitz, 3 June 2006
INSERT INTO flyspray_prefs ( pref_id , pref_name , pref_value , pref_desc )
VALUES (25 , 'notify_registration', '0', 'Whether or not admins get an email on a new user registration');

-- Florian Schmitz, 26 June 2006
create index idx_task_id on flyspray_history(task_id);

COMMIT;

