ALTER TABLE flyspray_groups ADD belongs_to_project NUMERIC(3);
ALTER TABLE flyspray_groups ALTER belongs_to_project SET DEFAULT '0';
UPDATE flyspray_groups SET belongs_to_project = '0' WHERE belongs_to_project IS NULL;
ALTER TABLE flyspray_groups ALTER belongs_to_project SET NOT NULL;
ALTER TABLE flyspray_groups ADD manage_project NUMERIC(1);
ALTER TABLE flyspray_groups ALTER manage_project SET DEFAULT '0';
UPDATE flyspray_groups SET manage_project = '0' WHERE manage_project IS NULL;
ALTER TABLE flyspray_groups ALTER manage_project SET NOT NULL;
ALTER TABLE flyspray_groups ADD view_tasks NUMERIC(1);
ALTER TABLE flyspray_groups ALTER view_tasks SET DEFAULT '0';
UPDATE flyspray_groups SET view_tasks = '0' WHERE view_tasks IS NULL;
ALTER TABLE flyspray_groups ALTER view_tasks SET NOT NULL;
ALTER TABLE flyspray_groups ADD modify_own_tasks NUMERIC(1);
ALTER TABLE flyspray_groups ALTER modify_own_tasks SET DEFAULT '0';
UPDATE flyspray_groups SET modify_own_tasks = '0' WHERE modify_own_tasks IS NULL;
ALTER TABLE flyspray_groups ALTER modify_own_tasks SET NOT NULL;
ALTER TABLE flyspray_groups ADD modify_all_tasks NUMERIC(1);
ALTER TABLE flyspray_groups ALTER modify_all_tasks SET DEFAULT '0';
UPDATE flyspray_groups SET modify_all_tasks = '0' WHERE modify_all_tasks IS NULL;
ALTER TABLE flyspray_groups ALTER modify_all_tasks SET NOT NULL;
UPDATE flyspray_groups SET modify_all_tasks = can_modify_jobs;
ALTER TABLE flyspray_groups DROP COLUMN can_modify_jobs;
ALTER TABLE flyspray_groups ADD open_new_tasks NUMERIC(1);
ALTER TABLE flyspray_groups ALTER open_new_tasks SET DEFAULT '0';
UPDATE flyspray_groups SET open_new_tasks = '0' WHERE open_new_tasks IS NULL;
ALTER TABLE flyspray_groups ALTER open_new_tasks SET NOT NULL;
UPDATE flyspray_groups SET open_new_tasks = can_open_jobs;
ALTER TABLE flyspray_groups DROP COLUMN can_open_jobs;
ALTER TABLE flyspray_groups ADD view_comments NUMERIC(1);
ALTER TABLE flyspray_groups ALTER view_comments SET DEFAULT '0';
UPDATE flyspray_groups SET view_comments = '0' WHERE view_comments IS NULL;
ALTER TABLE flyspray_groups ALTER view_comments SET NOT NULL;
ALTER TABLE flyspray_groups ADD edit_comments NUMERIC(1);
ALTER TABLE flyspray_groups ALTER edit_comments SET DEFAULT '0';
UPDATE flyspray_groups SET edit_comments = '0' WHERE edit_comments IS NULL;
ALTER TABLE flyspray_groups ALTER edit_comments SET NOT NULL;
ALTER TABLE flyspray_groups ADD delete_comments NUMERIC(1);
ALTER TABLE flyspray_groups ALTER delete_comments SET DEFAULT '0';
UPDATE flyspray_groups SET delete_comments = '0' WHERE delete_comments IS NULL;
ALTER TABLE flyspray_groups ALTER delete_comments SET NOT NULL;
ALTER TABLE flyspray_groups ADD add_comments NUMERIC(1);
ALTER TABLE flyspray_groups ALTER add_comments SET DEFAULT '0';
UPDATE flyspray_groups SET add_comments = '0' WHERE add_comments IS NULL;
ALTER TABLE flyspray_groups ALTER add_comments SET NOT NULL;
UPDATE flyspray_groups SET add_comments = can_add_comments;
ALTER TABLE flyspray_groups DROP COLUMN can_add_comments;
ALTER TABLE flyspray_groups ADD view_attachments NUMERIC(1);
ALTER TABLE flyspray_groups ALTER view_attachments SET DEFAULT '0';
UPDATE flyspray_groups SET view_attachments = '0' WHERE view_attachments IS NULL;
ALTER TABLE flyspray_groups ALTER view_attachments SET NOT NULL;
ALTER TABLE flyspray_groups ADD create_attachments NUMERIC(1);
ALTER TABLE flyspray_groups ALTER create_attachments SET DEFAULT '0';
UPDATE flyspray_groups SET create_attachments = '0' WHERE create_attachments IS NULL;
ALTER TABLE flyspray_groups ALTER create_attachments SET NOT NULL;
UPDATE flyspray_groups SET create_attachments = can_attach_files;
ALTER TABLE flyspray_groups DROP COLUMN can_attach_files;
ALTER TABLE flyspray_groups ADD delete_attachments NUMERIC(1);
ALTER TABLE flyspray_groups ALTER delete_attachments SET DEFAULT '0';
UPDATE flyspray_groups SET delete_attachments = '0' WHERE delete_attachments IS NULL;
ALTER TABLE flyspray_groups ALTER delete_attachments SET NOT NULL;
ALTER TABLE flyspray_groups ADD view_history NUMERIC(1);
ALTER TABLE flyspray_groups ALTER view_history SET DEFAULT '0';
UPDATE flyspray_groups SET view_history = '0' WHERE view_history IS NULL;
ALTER TABLE flyspray_groups ALTER view_history SET NOT NULL;
ALTER TABLE flyspray_groups ADD close_own_tasks NUMERIC(1);
ALTER TABLE flyspray_groups ALTER close_own_tasks SET DEFAULT '0';
UPDATE flyspray_groups SET close_own_tasks = '0' WHERE close_own_tasks IS NULL;
ALTER TABLE flyspray_groups ALTER close_own_tasks SET NOT NULL;
UPDATE flyspray_groups SET close_own_tasks = can_vote;
ALTER TABLE flyspray_groups DROP COLUMN can_vote;
ALTER TABLE flyspray_groups ADD close_other_tasks NUMERIC(1);
ALTER TABLE flyspray_groups ALTER close_other_tasks SET DEFAULT '0';
UPDATE flyspray_groups SET close_other_tasks = '0' WHERE close_other_tasks IS NULL;
ALTER TABLE flyspray_groups ALTER close_other_tasks SET NOT NULL;
ALTER TABLE flyspray_groups ADD assign_to_self NUMERIC(1);
ALTER TABLE flyspray_groups ALTER assign_to_self SET DEFAULT '0';
UPDATE flyspray_groups SET assign_to_self = '0' WHERE assign_to_self IS NULL;
ALTER TABLE flyspray_groups ALTER assign_to_self SET NOT NULL;
ALTER TABLE flyspray_groups ADD assign_others_to_self NUMERIC(1);
ALTER TABLE flyspray_groups ALTER assign_others_to_self SET DEFAULT '0';
UPDATE flyspray_groups SET assign_others_to_self = '0' WHERE assign_others_to_self IS NULL;
ALTER TABLE flyspray_groups ALTER assign_others_to_self SET NOT NULL;
ALTER TABLE flyspray_groups ADD view_reports NUMERIC(1);
ALTER TABLE flyspray_groups ALTER view_reports SET DEFAULT '0';
UPDATE flyspray_groups SET view_reports = '0' WHERE view_reports IS NULL;
ALTER TABLE flyspray_groups ALTER view_reports SET NOT NULL;
UPDATE flyspray_groups SET manage_project = '1',
	 view_tasks = '1',
	 modify_own_tasks = '1',
	 view_comments = '1',
	 edit_comments = '1',
	 delete_comments = '1',
	 view_attachments = '1',
	 delete_attachments = '1',
	 view_history = '1',
	 close_other_tasks = '1',
	 assign_to_self = '1',
	 assign_others_to_self = '1',
	 view_reports = '1' WHERE group_id = '1';
UPDATE flyspray_groups SET view_tasks = '1',
	 modify_own_tasks = '1',
	 view_comments = '1',
	 edit_comments = '1',
	 delete_comments = '1',
	 view_attachments = '1',
	 delete_attachments = '1',
	 view_history = '1',
	 close_other_tasks = '1',
	 assign_to_self = '1',
	 assign_others_to_self = '1' WHERE group_id = '2';
UPDATE flyspray_groups SET view_tasks = '1',
	 modify_own_tasks = '1',
	 view_comments = '1',
	 view_attachments = '1',
	 view_history = '1',
	 assign_to_self = '1' WHERE group_id = '3';
UPDATE flyspray_groups SET view_tasks = '1',
	 view_comments = '1',
	 add_comments = '1',
	 view_attachments = '1' WHERE group_id = '4';
CREATE SEQUENCE "flyspray_users_in_groups_record_id_seq" START WITH 1;
CREATE TABLE flyspray_users_in_groups (
	record_id INT8 NOT NULL DEFAULT nextval('"flyspray_users_in_groups_record_id_seq"'::text),
	user_id NUMERIC(5) NOT NULL default '0',
	group_id NUMERIC(3) NOT NULL default '0',
	PRIMARY KEY (record_id)
);
ALTER TABLE flyspray_projects ADD others_view NUMERIC(1);
ALTER TABLE flyspray_projects ALTER others_view SET DEFAULT '0';
UPDATE flyspray_projects SET others_view = '0' WHERE others_view IS NULL;
ALTER TABLE flyspray_projects ALTER others_view SET NOT NULL;
INSERT INTO flyspray_groups (group_name, group_desc, belongs_to_project, is_admin, manage_project, view_tasks, open_new_tasks, modify_own_tasks, modify_all_tasks, view_comments, add_comments, edit_comments, delete_comments, view_attachments, create_attachments, delete_attachments, view_history, close_own_tasks, close_other_tasks, assign_to_self, assign_others_to_self, view_reports, group_open) VALUES ('Basic', 'Members can login, relying upon Project permissions only', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '1');
CREATE SEQUENCE "flyspray_admin_requests_request_id_seq" START WITH 1;
CREATE TABLE flyspray_admin_requests (
	request_id INT8 NOT NULL DEFAULT nextval('"flyspray_admin_requests_request_id_seq"'::text),
	project_id NUMERIC(5) NOT NULL default '0',
	task_id NUMERIC(5) NOT NULL default '0',
	submitted_by NUMERIC(5) NOT NULL default '0',
	request_type NUMERIC(2) NOT NULL default '0',
	time_submitted TEXT NOT NULL default '',
	resolved_by NUMERIC(5) NOT NULL default '0',
	time_resolved TEXT NOT NULL default '',
	PRIMARY KEY (request_id)
);
CREATE SEQUENCE "flyspray_dependencies_depend_id_seq" START WITH 2;
CREATE TABLE flyspray_dependencies (
	depend_id INT8 NOT NULL DEFAULT nextval('"flyspray_dependencies_depend_id_seq"'::text),
	task_id NUMERIC(10) NOT NULL default '0',
	dep_task_id NUMERIC(10) NOT NULL default '0',
	PRIMARY KEY (depend_id)
);
ALTER TABLE flyspray_users ADD magic_url TEXT;
ALTER TABLE flyspray_users ALTER magic_url SET DEFAULT '';
UPDATE flyspray_users SET magic_url = '' WHERE magic_url IS NULL;
ALTER TABLE flyspray_users ALTER magic_url SET NOT NULL;
ALTER TABLE flyspray_registrations ADD user_name TEXT;
ALTER TABLE flyspray_registrations ALTER user_name SET DEFAULT '';
UPDATE flyspray_registrations SET user_name = '' WHERE user_name IS NULL;
ALTER TABLE flyspray_registrations ALTER user_name SET NOT NULL;
ALTER TABLE flyspray_registrations ADD real_name TEXT;
ALTER TABLE flyspray_registrations ALTER real_name SET DEFAULT '';
UPDATE flyspray_registrations SET real_name = '' WHERE real_name IS NULL;
ALTER TABLE flyspray_registrations ALTER real_name SET NOT NULL;
ALTER TABLE flyspray_registrations ADD email_address TEXT;
ALTER TABLE flyspray_registrations ALTER email_address SET DEFAULT '';
UPDATE flyspray_registrations SET email_address = '' WHERE email_address IS NULL;
ALTER TABLE flyspray_registrations ALTER email_address SET NOT NULL;
ALTER TABLE flyspray_registrations ADD jabber_id TEXT;
ALTER TABLE flyspray_registrations ALTER jabber_id SET DEFAULT '';
UPDATE flyspray_registrations SET jabber_id = '' WHERE jabber_id IS NULL;
ALTER TABLE flyspray_registrations ALTER jabber_id SET NOT NULL;
ALTER TABLE flyspray_registrations ADD notify_type NUMERIC(1);
ALTER TABLE flyspray_registrations ALTER notify_type SET DEFAULT '0';
UPDATE flyspray_registrations SET notify_type = '0' WHERE notify_type IS NULL;
ALTER TABLE flyspray_registrations ALTER notify_type SET NOT NULL;
ALTER TABLE flyspray_registrations ADD magic_url TEXT;
ALTER TABLE flyspray_registrations ALTER magic_url SET DEFAULT '';
UPDATE flyspray_registrations SET magic_url = '' WHERE magic_url IS NULL;
ALTER TABLE flyspray_registrations ALTER magic_url SET NOT NULL;
ALTER TABLE flyspray_projects ADD anon_open NUMERIC(1);
ALTER TABLE flyspray_projects ALTER anon_open SET DEFAULT '0';
UPDATE flyspray_projects SET anon_open = '0' WHERE anon_open IS NULL;
ALTER TABLE flyspray_projects ALTER anon_open SET NOT NULL;
INSERT INTO flyspray_prefs (pref_name, pref_value, pref_desc) VALUES ('anon_reg', '1', 'Allow new user registrations');
INSERT INTO flyspray_prefs (pref_name, pref_value, pref_desc) VALUES ('fs_ver', '0.9.7', 'Current Flyspray Version');
DELETE FROM flyspray_prefs WHERE pref_id = '1';
DELETE FROM flyspray_prefs WHERE pref_id = '2';
DELETE FROM flyspray_prefs WHERE pref_id = '7';
DELETE FROM flyspray_prefs WHERE pref_id = '13';
DELETE FROM flyspray_prefs WHERE pref_id = '16';
ALTER TABLE flyspray_tasks ADD mark_private NUMERIC(1);
ALTER TABLE flyspray_tasks ALTER mark_private SET DEFAULT '0';
UPDATE flyspray_tasks SET mark_private = '0' WHERE mark_private IS NULL;
ALTER TABLE flyspray_tasks ALTER mark_private SET NOT NULL