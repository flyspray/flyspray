--
-- PostgreSQL database dump
--

SET client_encoding = 'UNICODE';

--
-- Name: flyspray_admin_requests; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_admin_requests (
    request_id bigint DEFAULT nextval('"flyspray_admin_requests_request_id_seq"'::text) NOT NULL,
    project_id bigint DEFAULT 0::numeric NOT NULL,
    task_id bigint DEFAULT 0::numeric NOT NULL,
    submitted_by bigint DEFAULT 0::numeric NOT NULL,
    request_type bigint DEFAULT 0::numeric NOT NULL,
    time_submitted int DEFAULT 0::numeric NOT NULL,
    resolved_by bigint DEFAULT 0::numeric NOT NULL,
    time_resolved int DEFAULT 0::numeric NOT NULL,
    reason_given text NOT NULL,
    deny_reason text DEFAULT ''::text NOT NULL
);


--
-- Name: flyspray_admin_requests_request_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_admin_requests_request_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_admin_requests_request_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_admin_requests_request_id_seq', 1, false);


--
-- Name: flyspray_assigned; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_assigned (
    assigned_id bigint DEFAULT nextval('"flyspray_assigned_assigned_id_seq"'::text) NOT NULL,
    task_id bigint DEFAULT 0 NOT NULL,
    user_id bigint DEFAULT 0 NOT NULL
);


--
-- Name: flyspray_assigned_assigned_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--
CREATE SEQUENCE flyspray_assigned_assigned_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;

SELECT pg_catalog.setval('flyspray_assigned_assigned_id_seq', 1, false);


--
-- Name: flyspray_attachments; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_attachments (
    attachment_id bigint DEFAULT nextval('"flyspray_attachments_attachment_id_seq"'::text) NOT NULL,
    task_id bigint DEFAULT 0::numeric NOT NULL,
    orig_name text DEFAULT ''::text NOT NULL,
    file_name text DEFAULT ''::text NOT NULL,
    file_desc text DEFAULT ''::text NOT NULL,
    file_type text DEFAULT ''::text NOT NULL,
    file_size bigint DEFAULT 0::numeric NOT NULL,
    added_by bigint DEFAULT 0::numeric NOT NULL,
    date_added int DEFAULT 0::numeric NOT NULL,
    comment_id bigint NOT NULL
);


--
-- Name: flyspray_attachments_attachment_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_attachments_attachment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_attachments_attachment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_attachments_attachment_id_seq', 1, false);

--
-- Name: flyspray_cache; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_cache (
  id smallint NOT NULL DEFAULT nextval('"flyspray_cache_id_seq"'::text),
  type varchar NOT NULL default '',
  content text NOT NULL,
  topic varchar NOT NULL default '',
  last_updated int NOT NULL default '0',
  project_id int NOT NULL default 0,
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

--
-- Name: flyspray_comments; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_comments (
    comment_id bigint DEFAULT nextval('"flyspray_comments_comment_id_seq"'::text) NOT NULL,
    task_id bigint DEFAULT 0::numeric NOT NULL,
    date_added int DEFAULT 0::numeric NOT NULL,
    user_id bigint DEFAULT 0::numeric NOT NULL,
    comment_text text NOT NULL,
    last_edited_time int DEFAULT 0::numeric NOT NULL
);


--
-- Name: flyspray_comments_comment_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_comments_comment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_comments_comment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_comments_comment_id_seq', 1, false);


--
-- Name: flyspray_dependencies; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_dependencies (
    depend_id bigint DEFAULT nextval('"flyspray_dependencies_depend_id_seq"'::text) NOT NULL,
    task_id bigint DEFAULT 0::numeric NOT NULL,
    dep_task_id bigint DEFAULT 0::numeric NOT NULL
);


--
-- Name: flyspray_dependencies_depend_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_dependencies_depend_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_dependencies_depend_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_dependencies_depend_id_seq', 1, false);


--
-- Name: flyspray_groups; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_groups (
    group_id bigint DEFAULT nextval('"flyspray_groups_group_id_seq"'::text) NOT NULL,
    group_name text DEFAULT ''::text NOT NULL,
    group_desc text DEFAULT ''::text NOT NULL,
    belongs_to_project bigint DEFAULT 0::numeric NOT NULL,
    is_admin bigint DEFAULT 0::numeric NOT NULL,
    manage_project bigint DEFAULT 0::numeric NOT NULL,
    view_tasks bigint DEFAULT 0::numeric NOT NULL,
    open_new_tasks bigint DEFAULT 0::numeric NOT NULL,
    modify_own_tasks bigint DEFAULT 0::numeric NOT NULL,
    modify_all_tasks bigint DEFAULT 0::numeric NOT NULL,
    view_comments bigint DEFAULT 0::numeric NOT NULL,
    add_comments bigint DEFAULT 0::numeric NOT NULL,
    edit_comments bigint DEFAULT 0::numeric NOT NULL,
    delete_comments bigint DEFAULT 0::numeric NOT NULL,
    view_attachments bigint DEFAULT 0::numeric NOT NULL,
    create_attachments bigint DEFAULT 0::numeric NOT NULL,
    delete_attachments bigint DEFAULT 0::numeric NOT NULL,
    view_history bigint DEFAULT 0::numeric NOT NULL,
    close_own_tasks bigint DEFAULT 0::numeric NOT NULL,
    close_other_tasks bigint DEFAULT 0::numeric NOT NULL,
    assign_to_self bigint DEFAULT 0::numeric NOT NULL,
    assign_others_to_self bigint DEFAULT 0::numeric NOT NULL,
    view_reports bigint DEFAULT 0::numeric NOT NULL,
    group_open bigint DEFAULT 0::numeric NOT NULL,
    add_to_assignees int DEFAULT 0::numeric NOT NULL,
    add_votes int DEFAULT 0::numeric NOT NULL,
    edit_own_comments int DEFAULT 0::numeric NOT NULL,
    edit_assignments int DEFAULT 0::numeric NOT NULL
);


--
-- Name: flyspray_groups_group_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_groups_group_id_seq
    START WITH 7
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_groups_group_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_groups_group_id_seq', 7, false);


--
-- Name: flyspray_history; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_history (
    history_id bigint DEFAULT nextval('"flyspray_history_history_id_seq"'::text) NOT NULL,
    task_id bigint DEFAULT 0::numeric NOT NULL,
    user_id bigint DEFAULT 0::numeric NOT NULL,
    event_date int DEFAULT 0::numeric NOT NULL,
    event_type int DEFAULT 0::numeric NOT NULL,
    field_changed VARCHAR( 50 ) NOT NULL,
    old_value text NOT NULL,
    new_value text NOT NULL
);


--
-- Name: flyspray_history_history_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_history_history_id_seq
    START WITH 2
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_history_history_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_history_history_id_seq', 2, false);


--
-- Name: flyspray_list_category; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_list_category (
    category_id bigint DEFAULT nextval('"flyspray_list_category_category_id_seq"'::text) NOT NULL,
    project_id bigint DEFAULT 0::numeric NOT NULL,
    category_name text DEFAULT ''::text NOT NULL,
    show_in_list bigint DEFAULT 0::numeric NOT NULL,
    category_owner bigint DEFAULT 0::numeric NOT NULL,
    lft bigint DEFAULT 0::numeric NOT NULL,
    rgt bigint DEFAULT 0::numeric NOT NULL
);


--
-- Name: flyspray_list_category_category_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_list_category_category_id_seq
    START WITH 3
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_list_category_category_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_list_category_category_id_seq', 3, false);


--
-- Name: flyspray_list_os; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_list_os (
    os_id bigint DEFAULT nextval('"flyspray_list_os_os_id_seq"'::text) NOT NULL,
    project_id bigint DEFAULT 0::numeric NOT NULL,
    os_name text DEFAULT ''::text NOT NULL,
    list_position bigint DEFAULT 0::numeric NOT NULL,
    show_in_list bigint DEFAULT 0::numeric NOT NULL
);


--
-- Name: flyspray_list_os_os_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_list_os_os_id_seq
    START WITH 6
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_list_os_os_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_list_os_os_id_seq', 6, false);


--
-- Name: flyspray_list_resolution; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_list_resolution (
    resolution_id bigint DEFAULT nextval('"flyspray_list_resolution_resolution_id_seq"'::text) NOT NULL,
    resolution_name text DEFAULT ''::text NOT NULL,
    list_position bigint DEFAULT 0::numeric NOT NULL,
    show_in_list bigint DEFAULT 0::numeric NOT NULL,
    project_id bigint NOT NULL
);


--
-- Name: flyspray_list_resolution_resolution_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_list_resolution_resolution_id_seq
    START WITH 9
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_list_resolution_resolution_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_list_resolution_resolution_id_seq', 9, false);


--
-- Name: flyspray_list_status; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

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

--
-- Name: flyspray_list_tasktype; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_list_tasktype (
    tasktype_id bigint DEFAULT nextval('"flyspray_list_tasktype_tasktype_id_seq"'::text) NOT NULL,
    tasktype_name text DEFAULT ''::text NOT NULL,
    list_position bigint DEFAULT 0::numeric NOT NULL,
    show_in_list bigint DEFAULT 0::numeric NOT NULL,
    project_id bigint NOT NULL
);


--
-- Name: flyspray_list_tasktype_tasktype_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_list_tasktype_tasktype_id_seq
    START WITH 3
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_list_tasktype_tasktype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_list_tasktype_tasktype_id_seq', 3, false);


--
-- Name: flyspray_list_version; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_list_version (
    version_id bigint DEFAULT nextval('"flyspray_list_version_version_id_seq"'::text) NOT NULL,
    project_id bigint DEFAULT 0::numeric NOT NULL,
    version_name text DEFAULT ''::text NOT NULL,
    list_position bigint DEFAULT 0::numeric NOT NULL,
    show_in_list bigint DEFAULT 0::numeric NOT NULL,
    version_tense bigint DEFAULT 0::numeric NOT NULL
);


--
-- Name: flyspray_list_version_version_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_list_version_version_id_seq
    START WITH 2
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_list_version_version_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_list_version_version_id_seq', 2, false);


--
-- Name: flyspray_notification_messages; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_notification_messages (
    message_id bigint DEFAULT nextval('"flyspray_notification_messages_message_id_seq"'::text) NOT NULL,
    message_subject text DEFAULT ''::text NOT NULL,
    message_body text DEFAULT ''::text NOT NULL,
    time_created int DEFAULT 0::numeric NOT NULL
);


--
-- Name: flyspray_notification_messages_message_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_notification_messages_message_id_seq
    START WITH 2
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_notification_messages_message_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_notification_messages_message_id_seq', 2, false);


--
-- Name: flyspray_notification_recipients; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_notification_recipients (
    recipient_id bigint DEFAULT nextval('"flyspray_notification_recipients_recipient_id_seq"'::text) NOT NULL,
    message_id bigint NOT NULL,
    notify_method text DEFAULT ''::text NOT NULL,
    notify_address text DEFAULT ''::text NOT NULL
);


--
-- Name: flyspray_notification_recipients_recipient_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_notification_recipients_recipient_id_seq
    START WITH 2
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_notification_recipients_recipient_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_notification_recipients_recipient_id_seq', 2, false);


--
-- Name: flyspray_notifications; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_notifications (
    notify_id bigint DEFAULT nextval('"flyspray_notifications_notify_id_seq"'::text) NOT NULL,
    task_id bigint DEFAULT 0::numeric NOT NULL,
    user_id bigint DEFAULT 0::numeric NOT NULL
);


--
-- Name: flyspray_notifications_notify_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_notifications_notify_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_notifications_notify_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_notifications_notify_id_seq', 1, false);


--
-- Name: flyspray_prefs; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_prefs (
    pref_id bigint DEFAULT nextval('"flyspray_prefs_pref_id_seq"'::text) NOT NULL,
    pref_name text DEFAULT ''::text NOT NULL,
    pref_value text DEFAULT ''::text NOT NULL,
    pref_desc text DEFAULT ''::text NOT NULL
);


--
-- Name: flyspray_prefs_pref_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_prefs_pref_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_prefs_pref_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_prefs_pref_id_seq', 22, true);


--
-- Name: flyspray_projects; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_projects (
    project_id bigint DEFAULT nextval('"flyspray_projects_project_id_seq"'::text) NOT NULL,
    project_title text DEFAULT ''::text NOT NULL,
    theme_style text DEFAULT '0'::text NOT NULL,
    default_cat_owner bigint DEFAULT 0::numeric NOT NULL,
    intro_message text NOT NULL,
    project_is_active bigint DEFAULT 0::numeric NOT NULL,
    visible_columns text DEFAULT ''::text NOT NULL,
    others_view bigint DEFAULT 0::numeric NOT NULL,
    anon_open bigint DEFAULT 0::numeric NOT NULL,
    notify_email text DEFAULT ''::text NOT NULL,
    notify_jabber text DEFAULT ''::text NOT NULL,
    feed_img_url text DEFAULT ''::text NOT NULL,
    feed_description text DEFAULT ''::text NOT NULL,
    notify_subject VARCHAR( 100 ) DEFAULT '' NOT NULL,
    lang_code VARCHAR( 10 ) DEFAULT '' NOT NULL,
    comment_closed INT DEFAULT 0::numeric NOT NULL,
    notify_reply text DEFAULT ''::text NOT NULL,
    notify_types VARCHAR( 100 ) DEFAULT '' NOT NULL,
    auto_assign SMALLINT DEFAULT 0::numeric NOT NULL,
    last_updated INT DEFAULT 0::numeric NOT NULL
);


--
-- Name: flyspray_projects_project_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_projects_project_id_seq
    START WITH 2
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_projects_project_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_projects_project_id_seq', 2, false);


--
-- Name: flyspray_registrations; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_registrations (
    reg_id bigint DEFAULT nextval('"flyspray_registrations_reg_id_seq"'::text) NOT NULL,
    reg_time int DEFAULT 0::numeric NOT NULL,
    confirm_code text DEFAULT ''::text NOT NULL,
    user_name varchar DEFAULT '' NOT NULL,
    real_name text DEFAULT ''::text NOT NULL,
    email_address text DEFAULT ''::text NOT NULL,
    jabber_id text DEFAULT ''::text NOT NULL,
    notify_type bigint DEFAULT 0::numeric NOT NULL,
    magic_url text DEFAULT ''::text NOT NULL
);


--
-- Name: flyspray_registrations_reg_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_registrations_reg_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_registrations_reg_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_registrations_reg_id_seq', 1, false);


--
-- Name: flyspray_related; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_related (
    related_id bigint DEFAULT nextval('"flyspray_related_related_id_seq"'::text) NOT NULL,
    this_task bigint DEFAULT 0::numeric NOT NULL,
    related_task bigint DEFAULT 0::numeric NOT NULL,
    is_duplicate SMALLINT DEFAULT 0::numeric NOT NULL
);


--
-- Name: flyspray_related_related_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_related_related_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_related_related_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_related_related_id_seq', 1, false);


--
-- Name: flyspray_reminders; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_reminders (
    reminder_id bigint DEFAULT nextval('"flyspray_reminders_reminder_id_seq"'::text) NOT NULL,
    task_id bigint DEFAULT 0::numeric NOT NULL,
    to_user_id bigint DEFAULT 0::numeric NOT NULL,
    from_user_id bigint DEFAULT 0::numeric NOT NULL,
    start_time int DEFAULT 0::numeric NOT NULL,
    how_often bigint DEFAULT 0::numeric NOT NULL,
    last_sent int DEFAULT 0::numeric NOT NULL,
    reminder_message text Default ''::text NOT NULL
);


--
-- Name: flyspray_reminders_reminder_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_reminders_reminder_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_reminders_reminder_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_reminders_reminder_id_seq', 1, false);


--
-- Name: flyspray_searches; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

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


--
-- Name: flyspray_tasks; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_tasks (
    task_id bigint DEFAULT nextval('"flyspray_tasks_task_id_seq"'::text) NOT NULL,
    attached_to_project bigint DEFAULT 0::numeric NOT NULL,
    task_type bigint DEFAULT 0::numeric NOT NULL,
    date_opened int DEFAULT 0::numeric NOT NULL,
    opened_by bigint DEFAULT 0::numeric NOT NULL,
    is_closed bigint DEFAULT 0::numeric NOT NULL,
    date_closed int DEFAULT 0::numeric NOT NULL,
    closed_by bigint DEFAULT 0::numeric NOT NULL,
    closure_comment text DEFAULT ''::text NOT NULL,
    item_summary text DEFAULT ''::text NOT NULL,
    detailed_desc text NOT NULL,
    item_status bigint DEFAULT 0::numeric NOT NULL,
    resolution_reason bigint DEFAULT 1::numeric NOT NULL,
    product_category bigint DEFAULT 0::numeric NOT NULL,
    product_version bigint DEFAULT 0::numeric NOT NULL,
    closedby_version bigint DEFAULT 0::numeric NOT NULL,
    operating_system bigint DEFAULT 0::numeric NOT NULL,
    task_severity bigint DEFAULT 0::numeric NOT NULL,
    task_priority bigint DEFAULT 0::numeric NOT NULL,
    last_edited_by bigint DEFAULT 0::numeric NOT NULL,
    last_edited_time text DEFAULT '0'::text NOT NULL,
    percent_complete bigint DEFAULT 0::numeric NOT NULL,
    mark_private bigint DEFAULT 0::numeric NOT NULL,
    due_date int DEFAULT 0::numeric NOT NULL,
    anon_email VARCHAR(100) DEFAULT '' NOT NULL,
    task_token VARCHAR(32) DEFAULT 0 NOT NULL
);


--
-- Name: flyspray_tasks_task_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_tasks_task_id_seq
    START WITH 2
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_tasks_task_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_tasks_task_id_seq', 2, false);


--
-- Name: flyspray_users; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_users (
    user_id bigint DEFAULT nextval('"flyspray_users_user_id_seq"'::text) NOT NULL,
    user_name varchar DEFAULT '' NOT NULL,
    user_pass text DEFAULT ''::text NOT NULL,
    real_name text DEFAULT ''::text NOT NULL,
    jabber_id text DEFAULT ''::text NOT NULL,
    email_address text DEFAULT ''::text NOT NULL,
    notify_type bigint DEFAULT 0::numeric NOT NULL,
    account_enabled bigint DEFAULT 0::numeric NOT NULL,
    dateformat text DEFAULT ''::text NOT NULL,
    dateformat_extended text DEFAULT ''::text NOT NULL,
    magic_url text DEFAULT ''::text NOT NULL,
    last_search text DEFAULT ''::text NOT NULL,
    tasks_perpage integer NOT NULL,
    notify_own SMALLINT DEFAULT 0::numeric NOT NULL,
    register_date INT DEFAULT 0::numeric NOT NULL
);


--
-- Name: flyspray_users_in_groups; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

CREATE TABLE flyspray_users_in_groups (
    record_id bigint DEFAULT nextval('"flyspray_users_in_groups_record_id_seq"'::text) NOT NULL,
    user_id bigint DEFAULT 0::numeric NOT NULL,
    group_id bigint DEFAULT 0::numeric NOT NULL
);


--
-- Name: flyspray_users_in_groups_record_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_users_in_groups_record_id_seq
    START WITH 2
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_users_in_groups_record_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_users_in_groups_record_id_seq', 2, false);


--
-- Name: flyspray_users_user_id_seq; Type: SEQUENCE; Schema: public; Owner: cr
--

CREATE SEQUENCE flyspray_users_user_id_seq
    START WITH 2
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: flyspray_users_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: cr
--

SELECT pg_catalog.setval('flyspray_users_user_id_seq', 2, false);


--
-- Name: flyspray_votes; Type: TABLE; Schema: public; Owner: cr; Tablespace:
--

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


--
-- Data for Name: flyspray_admin_requests; Type: TABLE DATA; Schema: public; Owner: cr
--



--
-- Data for Name: flyspray_assigned; Type: TABLE DATA; Schema: public; Owner: cr
--



--
-- Data for Name: flyspray_attachments; Type: TABLE DATA; Schema: public; Owner: cr
--



--
-- Data for Name: flyspray_comments; Type: TABLE DATA; Schema: public; Owner: cr
--



--
-- Data for Name: flyspray_dependencies; Type: TABLE DATA; Schema: public; Owner: cr
--



--
-- Data for Name: flyspray_groups; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_groups (group_id, group_name, group_desc, belongs_to_project, is_admin, manage_project, view_tasks, open_new_tasks, modify_own_tasks, modify_all_tasks, view_comments, add_comments, edit_comments, delete_comments, view_attachments, create_attachments, delete_attachments, view_history, close_own_tasks, close_other_tasks, assign_to_self, assign_others_to_self, view_reports, group_open, add_to_assignees, add_votes, edit_own_comments, edit_assignments) VALUES (1, 'Admin', 'Members have unlimited access to all functionality.', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
INSERT INTO flyspray_groups (group_id, group_name, group_desc, belongs_to_project, is_admin, manage_project, view_tasks, open_new_tasks, modify_own_tasks, modify_all_tasks, view_comments, add_comments, edit_comments, delete_comments, view_attachments, create_attachments, delete_attachments, view_history, close_own_tasks, close_other_tasks, assign_to_self, assign_others_to_self, view_reports, group_open, add_to_assignees, add_votes, edit_own_comments, edit_assignments) VALUES (2, 'Developers', 'Global Developers for all projects', 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
INSERT INTO flyspray_groups (group_id, group_name, group_desc, belongs_to_project, is_admin, manage_project, view_tasks, open_new_tasks, modify_own_tasks, modify_all_tasks, view_comments, add_comments, edit_comments, delete_comments, view_attachments, create_attachments, delete_attachments, view_history, close_own_tasks, close_other_tasks, assign_to_self, assign_others_to_self, view_reports, group_open, add_to_assignees, add_votes, edit_own_comments, edit_assignments) VALUES (3, 'Reporters', 'Open new tasks / add comments in all projects', 0, 0, 0, 1, 1, 0, 0, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0);
INSERT INTO flyspray_groups (group_id, group_name, group_desc, belongs_to_project, is_admin, manage_project, view_tasks, open_new_tasks, modify_own_tasks, modify_all_tasks, view_comments, add_comments, edit_comments, delete_comments, view_attachments, create_attachments, delete_attachments, view_history, close_own_tasks, close_other_tasks, assign_to_self, assign_others_to_self, view_reports, group_open, add_to_assignees, add_votes, edit_own_comments, edit_assignments) VALUES (4, 'Basic', 'Members can login, relying upon Project permissions only', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0);
INSERT INTO flyspray_groups (group_id, group_name, group_desc, belongs_to_project, is_admin, manage_project, view_tasks, open_new_tasks, modify_own_tasks, modify_all_tasks, view_comments, add_comments, edit_comments, delete_comments, view_attachments, create_attachments, delete_attachments, view_history, close_own_tasks, close_other_tasks, assign_to_self, assign_others_to_self, view_reports, group_open, add_to_assignees, add_votes, edit_own_comments, edit_assignments) VALUES (5, 'Pending', 'Users who are awaiting approval of their accounts.', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO flyspray_groups (group_id, group_name, group_desc, belongs_to_project, is_admin, manage_project, view_tasks, open_new_tasks, modify_own_tasks, modify_all_tasks, view_comments, add_comments, edit_comments, delete_comments, view_attachments, create_attachments, delete_attachments, view_history, close_own_tasks, close_other_tasks, assign_to_self, assign_others_to_self, view_reports, group_open, add_to_assignees, add_votes, edit_own_comments, edit_assignments) VALUES (6, 'Project Managers', 'Permission to do anything related to the Default Project.', 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);


--
-- Data for Name: flyspray_history; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_history (history_id, task_id, user_id, event_date, event_type, field_changed, old_value, new_value) VALUES (1, 1, 1, '1130024797', 1, '', '', '');


--
-- Data for Name: flyspray_list_category; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_list_category (category_id, project_id, category_name, show_in_list, category_owner, lft, rgt) VALUES (1, 0, 'root', 0, 0, 1, 2);
INSERT INTO flyspray_list_category (category_id, project_id, category_name, show_in_list, category_owner, lft, rgt) VALUES (2, 1, 'root', 0, 0, 1, 6);
INSERT INTO flyspray_list_category (category_id, project_id, category_name, show_in_list, category_owner, lft, rgt) VALUES (3, 1, 'Backend / Core', 1, 0, 2, 3);
INSERT INTO flyspray_list_category (category_id, project_id, category_name, show_in_list, category_owner, lft, rgt) VALUES (4, 1, 'User Interface', 1, 0, 4, 5);


--
-- Data for Name: flyspray_list_os; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_list_os (os_id, project_id, os_name, list_position, show_in_list) VALUES (1, 1, 'All', 1, 1);
INSERT INTO flyspray_list_os (os_id, project_id, os_name, list_position, show_in_list) VALUES (2, 1, 'Windows', 2, 1);
INSERT INTO flyspray_list_os (os_id, project_id, os_name, list_position, show_in_list) VALUES (3, 1, 'Linux', 3, 1);
INSERT INTO flyspray_list_os (os_id, project_id, os_name, list_position, show_in_list) VALUES (4, 1, 'Mac OS', 4, 1);
INSERT INTO flyspray_list_os (os_id, project_id, os_name, list_position, show_in_list) VALUES (5, 1, 'UNIX', 4, 1);


--
-- Data for Name: flyspray_list_resolution; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list, project_id) VALUES (1, 'Not a bug', 1, 1, 0);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list, project_id) VALUES (2, 'Won''t fix', 2, 1, 0);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list, project_id) VALUES (3, 'Won''t implement', 3, 1, 0);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list, project_id) VALUES (4, 'Works for me', 4, 1, 0);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list, project_id) VALUES (5, 'Duplicate', 5, 1, 0);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list, project_id) VALUES (6, 'Deferred', 6, 1, 0);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list, project_id) VALUES (7, 'Fixed', 7, 1, 0);
INSERT INTO flyspray_list_resolution (resolution_id, resolution_name, list_position, show_in_list, project_id) VALUES (8, 'Implemented', 8, 1, 0);


--
-- Data for Name: flyspray_list_status; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (1, 'Unconfirmed', 1, 1, 0);
INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (2, 'New', 2, 1, 0);
INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (3, 'Assigned', 3, 1, 0);
INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (4, 'Researching', 4, 1, 0);
INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (5, 'Waiting on Customer', 5, 1, 0);
INSERT INTO flyspray_list_status (status_id, status_name, list_position, show_in_list, project_id) VALUES (6, 'Requires testing', 6, 1, 0);


--
-- Data for Name: flyspray_list_tasktype; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_list_tasktype (tasktype_id, tasktype_name, list_position, show_in_list, project_id) VALUES (1, 'Bug Report', 1, 1, 0);
INSERT INTO flyspray_list_tasktype (tasktype_id, tasktype_name, list_position, show_in_list, project_id) VALUES (2, 'Feature Request', 2, 1, 0);


--
-- Data for Name: flyspray_list_version; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_list_version (version_id, project_id, version_name, list_position, show_in_list, version_tense) VALUES (1, 1, 'Devel', 1, 1, 2);


--
-- Data for Name: flyspray_notification_messages; Type: TABLE DATA; Schema: public; Owner: cr
--



--
-- Data for Name: flyspray_notification_recipients; Type: TABLE DATA; Schema: public; Owner: cr
--



--
-- Data for Name: flyspray_notifications; Type: TABLE DATA; Schema: public; Owner: cr
--



--
-- Data for Name: flyspray_prefs; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (1, 'fs_ver', '0.9.9(devel)', 'Current Flyspray version');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (2, 'jabber_server', '', 'Jabber server');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (3, 'jabber_port', '5222', 'Jabber server port');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (4, 'jabber_username', '', 'Jabber username');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (5, 'jabber_password', '', 'Jabber password');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (6, 'anon_group', '4', 'Group for anonymous registrations');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (8, 'user_notify', '1', 'Force task notifications as');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (9, 'admin_email', 'flyspray@example.com', 'Reply email address for notifications');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (10, 'assigned_groups', '1 2 3', 'Members of these groups can be assigned tasks');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (11, 'lang_code', 'en', 'Language');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (12, 'spam_proof', '1', 'Use confirmation codes for user registrations');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (13, 'default_project', '1', 'Default project id');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (14, 'dateformat', '', 'Default date format for new users and guests used in the task list');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (15, 'dateformat_extended', '', 'Default date format for new users and guests used in task details');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (16, 'anon_reg', '1', 'Allow new user registrations');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (17, 'global_theme', 'Bluey', 'Theme to use when viewing all projects');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (18, 'visible_columns', 'id project category tasktype severity summary status progress', 'Columns visible when viewing all projects');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (19, 'smtp_server', '', 'Remote mail server');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (20, 'smtp_user', '', 'Username to access the remote mail server');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (21, 'smtp_pass', '', 'Password to access the remote mail server');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (22, 'cache_feeds', '0', '0 = do not cache feeds, 1 = cache feeds on disk, 2 = cache feeds in DB');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (23, 'last_update_check', '0', 'Time when the last update check was done.');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (24, 'jabber_ssl', '0', 'Whether or not to use SSL for Jabber connections');
INSERT INTO flyspray_prefs (pref_id, pref_name, pref_value, pref_desc) VALUES (25, 'notify_registration', '0', 'Whether or not admins get an email on a new user registration');

--
-- Data for Name: flyspray_projects; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_projects (project_id, project_title, theme_style, default_cat_owner, intro_message, project_is_active, visible_columns, others_view, anon_open, notify_email, notify_jabber) VALUES (1, 'Default Project', 'Bluey', 0, 'Welcome to your first Flyspray project!  We hope that Flyspray provides you with many hours of increased productivity.  If you have any issues, please read <a href="http://flyspray.rocks.cc/manual/toc">the documentation</a> and <a href="http://flyspray.rocks.cc/faq">Frequently Asked Questions</a>. <br /><br />You can customise this message by clicking the <b>Manage Project</b> link in the menu above', 1, 'id category tasktype severity summary status progress', 1, 0, '', '');


--
-- Data for Name: flyspray_registrations; Type: TABLE DATA; Schema: public; Owner: cr
--



--
-- Data for Name: flyspray_related; Type: TABLE DATA; Schema: public; Owner: cr
--



--
-- Data for Name: flyspray_reminders; Type: TABLE DATA; Schema: public; Owner: cr
--



--
-- Data for Name: flyspray_tasks; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_tasks (task_id, attached_to_project, task_type, date_opened, opened_by, is_closed, date_closed, closed_by, closure_comment, item_summary, detailed_desc, item_status,  resolution_reason, product_category, product_version, closedby_version, operating_system, task_severity, task_priority, last_edited_by, last_edited_time, percent_complete, mark_private, due_date) VALUES (1, 1, 1, '1130024797', 1, 0, 0, 1, '0', 'Sample Task', 'This isn''t a real task.  You should close it and start opening some real tasks.', 2, 1, 3, 1, 0, 1, 1, 2, 0, 0, 0, 0, 0);


--
-- Data for Name: flyspray_users; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_users (user_id, user_name, user_pass, real_name, jabber_id, email_address, notify_type, account_enabled, dateformat, dateformat_extended, magic_url, last_search, tasks_perpage) VALUES (1, 'super', '4tuKHcjxpFYag', 'Mr Super User', 'super@example.com', 'super@example.com', 0, 1, '', '', '', '', 25);


--
-- Data for Name: flyspray_users_in_groups; Type: TABLE DATA; Schema: public; Owner: cr
--

INSERT INTO flyspray_users_in_groups (record_id, user_id, group_id) VALUES (1, 1, 1);


--
-- Name: flyspray_admin_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_admin_requests
    ADD CONSTRAINT flyspray_admin_requests_pkey PRIMARY KEY (request_id);


--
-- Name: flyspray_assigned_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_assigned
    ADD CONSTRAINT flyspray_assigned_pkey PRIMARY KEY (assigned_id);


--
-- Name: flyspray_attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_attachments
    ADD CONSTRAINT flyspray_attachments_pkey PRIMARY KEY (attachment_id);


--
-- Name: flyspray_comments_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_comments
    ADD CONSTRAINT flyspray_comments_pkey PRIMARY KEY (comment_id);


--
-- Name: flyspray_dependencies_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_dependencies
    ADD CONSTRAINT flyspray_dependencies_pkey PRIMARY KEY (depend_id);


--
-- Name: flyspray_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_groups
    ADD CONSTRAINT flyspray_groups_pkey PRIMARY KEY (group_id);


--
-- Name: flyspray_history_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_history
    ADD CONSTRAINT flyspray_history_pkey PRIMARY KEY (history_id);


--
-- Name: flyspray_list_category_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_list_category
    ADD CONSTRAINT flyspray_list_category_pkey PRIMARY KEY (category_id);


--
-- Name: flyspray_list_os_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_list_os
    ADD CONSTRAINT flyspray_list_os_pkey PRIMARY KEY (os_id);


--
-- Name: flyspray_list_resolution_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_list_resolution
    ADD CONSTRAINT flyspray_list_resolution_pkey PRIMARY KEY (resolution_id);


--
-- Name: flyspray_list_tasktype_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_list_tasktype
    ADD CONSTRAINT flyspray_list_tasktype_pkey PRIMARY KEY (tasktype_id);


--
-- Name: flyspray_list_version_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_list_version
    ADD CONSTRAINT flyspray_list_version_pkey PRIMARY KEY (version_id);


--
-- Name: flyspray_notification_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_notification_messages
    ADD CONSTRAINT flyspray_notification_messages_pkey PRIMARY KEY (message_id);


--
-- Name: flyspray_notification_recipients_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_notification_recipients
    ADD CONSTRAINT flyspray_notification_recipients_pkey PRIMARY KEY (recipient_id);


--
-- Name: flyspray_notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_notifications
    ADD CONSTRAINT flyspray_notifications_pkey PRIMARY KEY (notify_id);


--
-- Name: flyspray_prefs_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_prefs
    ADD CONSTRAINT flyspray_prefs_pkey PRIMARY KEY (pref_id);


--
-- Name: flyspray_projects_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_projects
    ADD CONSTRAINT flyspray_projects_pkey PRIMARY KEY (project_id);


--
-- Name: flyspray_registrations_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_registrations
    ADD CONSTRAINT flyspray_registrations_pkey PRIMARY KEY (reg_id);


--
-- Name: flyspray_related_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_related
    ADD CONSTRAINT flyspray_related_pkey PRIMARY KEY (related_id);


--
-- Name: flyspray_reminders_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_reminders
    ADD CONSTRAINT flyspray_reminders_pkey PRIMARY KEY (reminder_id);


--
-- Name: flyspray_tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_tasks
    ADD CONSTRAINT flyspray_tasks_pkey PRIMARY KEY (task_id);


--
-- Name: flyspray_users_in_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_users_in_groups
    ADD CONSTRAINT flyspray_users_in_groups_pkey PRIMARY KEY (record_id);


--
-- Name: flyspray_users_pkey; Type: CONSTRAINT; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE ONLY flyspray_users
    ADD CONSTRAINT flyspray_users_pkey PRIMARY KEY (user_id);


--
-- Name: flyspray_assigned_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_assigned_index ON flyspray_assigned (task_id, user_id);

--
-- Name: flyspray_attachments_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_attachments_index ON flyspray_attachments (task_id, comment_id);

--
-- Name: flyspray_cache; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE flyspray_cache ADD UNIQUE (type, topic, project_id, max_items);

--
-- Name: flyspray_comments_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_comments_index ON flyspray_comments (task_id);

--
-- Name: flyspray_dependencies_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_dependencies_index ON flyspray_dependencies (task_id, dep_task_id);

--
-- Name: flyspray_groups_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_groups_index ON flyspray_groups (belongs_to_project);

--
-- Name: flyspray_history; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX idx_task_id on flyspray_history(task_id);

--
-- Name: flyspray_list_category_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_list_category_index ON flyspray_list_category (project_id);

--
-- Name: flyspray_list_os_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_list_os_index ON flyspray_list_os (project_id);

--
-- Name: flyspray_list_resolution_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_list_resolution_index ON flyspray_list_resolution (project_id);

--
-- Name: flyspray_list_tasktype_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_list_tasktype_index ON flyspray_list_tasktype (project_id);

--
-- Name: flyspray_users; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

ALTER TABLE flyspray_users ADD UNIQUE (user_name);

--
-- Name: flyspray_list_version_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_list_version_index ON flyspray_list_version (project_id, version_tense);

--
-- Name: flyspray_notificationsn_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_notificationsn_index ON flyspray_notifications (task_id, user_id);

--
-- Name: flyspray_related_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_related_index ON flyspray_related (this_task, related_task);

--
-- Name: flyspray_tasks_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_tasks_index ON flyspray_tasks (attached_to_project, task_severity, task_type, product_category, item_status, is_closed, closedby_version, due_date);

--
-- Name: flyspray_users_in_groups_index_unique; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_users_in_groups_index_unique ON flyspray_users_in_groups (group_id, user_id);

--
-- Name: flyspray_users_in_groups_index; Type: INDEX; Schema: public; Owner: cr; Tablespace:
--

CREATE INDEX flyspray_users_in_groups_index ON flyspray_users_in_groups (user_id);

--
-- PostgreSQL database dump complete
--
commit;
