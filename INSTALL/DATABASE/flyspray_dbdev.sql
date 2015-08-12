-- phpMyAdmin SQL Dump
-- version 4.3.11
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Mer 12 Août 2015 à 14:39
-- Version du serveur :  5.6.24
-- Version de PHP :  5.6.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `flyspray_dbdev`
--

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_admin_requests`
--

CREATE TABLE IF NOT EXISTS `flyspray_admin_requests` (
  `request_id` int(5) NOT NULL,
  `project_id` int(5) NOT NULL DEFAULT '0',
  `task_id` int(5) NOT NULL DEFAULT '0',
  `submitted_by` int(5) NOT NULL DEFAULT '0',
  `request_type` int(2) NOT NULL DEFAULT '0',
  `reason_given` text,
  `time_submitted` int(11) NOT NULL DEFAULT '0',
  `resolved_by` int(5) NOT NULL DEFAULT '0',
  `time_resolved` int(11) NOT NULL DEFAULT '0',
  `deny_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_assigned`
--

CREATE TABLE IF NOT EXISTS `flyspray_assigned` (
  `assigned_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL DEFAULT '0',
  `user_id` int(5) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_attachments`
--

CREATE TABLE IF NOT EXISTS `flyspray_attachments` (
  `attachment_id` int(5) NOT NULL,
  `task_id` int(10) NOT NULL DEFAULT '0',
  `comment_id` int(10) NOT NULL DEFAULT '0',
  `orig_name` varchar(255) NOT NULL,
  `file_name` varchar(30) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `file_size` int(20) NOT NULL DEFAULT '0',
  `added_by` int(3) NOT NULL DEFAULT '0',
  `date_added` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_cache`
--

CREATE TABLE IF NOT EXISTS `flyspray_cache` (
  `id` int(6) NOT NULL,
  `type` varchar(4) NOT NULL,
  `content` longtext NOT NULL,
  `topic` int(11) NOT NULL,
  `last_updated` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `max_items` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_comments`
--

CREATE TABLE IF NOT EXISTS `flyspray_comments` (
  `comment_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL DEFAULT '0',
  `date_added` int(11) NOT NULL DEFAULT '0',
  `user_id` int(3) NOT NULL DEFAULT '0',
  `comment_text` text,
  `last_edited_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_dependencies`
--

CREATE TABLE IF NOT EXISTS `flyspray_dependencies` (
  `depend_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL DEFAULT '0',
  `dep_task_id` int(10) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_effort`
--

CREATE TABLE IF NOT EXISTS `flyspray_effort` (
  `effort_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL DEFAULT '0',
  `date_added` int(11) NOT NULL DEFAULT '0',
  `user_id` int(3) NOT NULL DEFAULT '0',
  `start_timestamp` int(11) DEFAULT NULL,
  `end_timestamp` int(11) DEFAULT NULL,
  `effort` int(15) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_fields`
--

CREATE TABLE IF NOT EXISTS `flyspray_fields` (
  `fields_id` int(11) NOT NULL,
  `fields_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `fields_type` int(11) NOT NULL DEFAULT '1',
  `version_tense` int(11) NOT NULL DEFAULT '0',
  `default_value` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `force_default` int(11) NOT NULL DEFAULT '0',
  `list_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `value_required` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_groups`
--

CREATE TABLE IF NOT EXISTS `flyspray_groups` (
  `group_id` int(3) NOT NULL,
  `group_name` varchar(20) NOT NULL,
  `group_desc` varchar(150) NOT NULL,
  `project_id` int(3) NOT NULL DEFAULT '0',
  `is_admin` int(1) NOT NULL DEFAULT '0',
  `manage_project` int(1) NOT NULL DEFAULT '0',
  `view_tasks` int(1) NOT NULL DEFAULT '0',
  `open_new_tasks` int(1) NOT NULL DEFAULT '0',
  `modify_own_tasks` int(1) NOT NULL DEFAULT '0',
  `modify_all_tasks` int(1) NOT NULL DEFAULT '0',
  `view_comments` int(1) NOT NULL DEFAULT '0',
  `add_comments` int(1) NOT NULL DEFAULT '0',
  `edit_comments` int(1) NOT NULL DEFAULT '0',
  `edit_own_comments` int(1) NOT NULL DEFAULT '0',
  `delete_comments` int(1) NOT NULL DEFAULT '0',
  `create_attachments` int(1) NOT NULL DEFAULT '0',
  `delete_attachments` int(1) NOT NULL DEFAULT '0',
  `view_history` int(1) NOT NULL DEFAULT '0',
  `close_own_tasks` int(1) NOT NULL DEFAULT '0',
  `close_other_tasks` int(1) NOT NULL DEFAULT '0',
  `assign_to_self` int(1) NOT NULL DEFAULT '0',
  `assign_others_to_self` int(1) NOT NULL DEFAULT '0',
  `add_to_assignees` int(1) NOT NULL DEFAULT '0',
  `view_reports` int(1) NOT NULL DEFAULT '0',
  `add_votes` int(1) NOT NULL DEFAULT '0',
  `edit_assignments` int(1) NOT NULL DEFAULT '0',
  `show_as_assignees` int(1) NOT NULL DEFAULT '0',
  `view_effort` int(1) NOT NULL DEFAULT '0',
  `track_effort` int(1) NOT NULL DEFAULT '0',
  `group_open` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_history`
--

CREATE TABLE IF NOT EXISTS `flyspray_history` (
  `history_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL DEFAULT '0',
  `user_id` int(3) NOT NULL DEFAULT '0',
  `event_date` int(11) NOT NULL DEFAULT '0',
  `event_type` int(2) NOT NULL DEFAULT '0',
  `field_changed` varchar(50) NOT NULL,
  `old_value` text,
  `new_value` text
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_links`
--

CREATE TABLE IF NOT EXISTS `flyspray_links` (
  `link_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL DEFAULT '0',
  `comment_id` int(11) NOT NULL DEFAULT '0',
  `url` text,
  `added_by` int(11) NOT NULL DEFAULT '0',
  `date_time` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_list_category`
--

CREATE TABLE IF NOT EXISTS `flyspray_list_category` (
  `category_id` int(3) NOT NULL,
  `category_name` varchar(30) NOT NULL,
  `show_in_list` int(1) NOT NULL DEFAULT '0',
  `category_owner` int(3) NOT NULL DEFAULT '0',
  `lft` int(10) unsigned NOT NULL DEFAULT '0',
  `rgt` int(10) unsigned NOT NULL DEFAULT '0',
  `list_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_list_lists`
--

CREATE TABLE IF NOT EXISTS `flyspray_list_lists` (
  `lists_id` int(11) NOT NULL,
  `lists_name` varchar(40) NOT NULL,
  `lists_type` bigint(20) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `show_in_list` int(1) NOT NULL DEFAULT '0',
  `list_position` int(3) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_list_os`
--

CREATE TABLE IF NOT EXISTS `flyspray_list_os` (
  `os_id` int(3) NOT NULL,
  `project_id` int(3) NOT NULL DEFAULT '0',
  `os_name` varchar(40) NOT NULL,
  `list_position` int(3) NOT NULL DEFAULT '0',
  `show_in_list` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_list_resolution`
--

CREATE TABLE IF NOT EXISTS `flyspray_list_resolution` (
  `resolution_id` int(3) NOT NULL,
  `resolution_name` varchar(30) NOT NULL,
  `list_position` int(3) NOT NULL DEFAULT '0',
  `show_in_list` int(1) NOT NULL DEFAULT '0',
  `project_id` int(3) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_list_standard`
--

CREATE TABLE IF NOT EXISTS `flyspray_list_standard` (
  `standard_id` int(3) NOT NULL,
  `standard_name` varchar(40) NOT NULL,
  `list_position` int(3) NOT NULL DEFAULT '0',
  `show_in_list` int(1) NOT NULL DEFAULT '0',
  `lists_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_list_status`
--

CREATE TABLE IF NOT EXISTS `flyspray_list_status` (
  `status_id` int(3) NOT NULL,
  `status_name` varchar(40) NOT NULL,
  `list_position` int(3) NOT NULL DEFAULT '0',
  `show_in_list` int(1) NOT NULL DEFAULT '0',
  `project_id` int(3) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_list_tasktype`
--

CREATE TABLE IF NOT EXISTS `flyspray_list_tasktype` (
  `tasktype_id` int(3) NOT NULL,
  `tasktype_name` varchar(40) NOT NULL,
  `list_position` int(3) NOT NULL DEFAULT '0',
  `show_in_list` int(1) NOT NULL DEFAULT '0',
  `project_id` int(3) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_list_version`
--

CREATE TABLE IF NOT EXISTS `flyspray_list_version` (
  `version_id` int(3) NOT NULL,
  `project_id` int(3) NOT NULL DEFAULT '0',
  `version_name` varchar(40) NOT NULL,
  `list_position` int(3) NOT NULL DEFAULT '0',
  `show_in_list` int(1) NOT NULL DEFAULT '0',
  `version_tense` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_notifications`
--

CREATE TABLE IF NOT EXISTS `flyspray_notifications` (
  `notify_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL DEFAULT '0',
  `user_id` int(5) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_notification_messages`
--

CREATE TABLE IF NOT EXISTS `flyspray_notification_messages` (
  `message_id` int(10) NOT NULL,
  `message_subject` text,
  `message_body` text,
  `time_created` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_notification_recipients`
--

CREATE TABLE IF NOT EXISTS `flyspray_notification_recipients` (
  `recipient_id` int(10) NOT NULL,
  `message_id` int(10) NOT NULL DEFAULT '0',
  `notify_method` varchar(1) NOT NULL,
  `notify_address` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_prefs`
--

CREATE TABLE IF NOT EXISTS `flyspray_prefs` (
  `pref_id` int(1) NOT NULL,
  `pref_name` varchar(20) NOT NULL,
  `pref_value` varchar(250) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_projects`
--

CREATE TABLE IF NOT EXISTS `flyspray_projects` (
  `project_id` int(3) NOT NULL,
  `project_title` varchar(100) NOT NULL,
  `theme_style` varchar(20) NOT NULL DEFAULT '0',
  `default_cat_owner` int(3) NOT NULL DEFAULT '0',
  `intro_message` text,
  `project_is_active` int(1) NOT NULL DEFAULT '0',
  `visible_columns` varchar(255) NOT NULL,
  `visible_fields` varchar(255) NOT NULL,
  `others_view` int(1) NOT NULL DEFAULT '0',
  `anon_open` int(1) NOT NULL DEFAULT '0',
  `notify_email` text,
  `notify_jabber` text,
  `notify_reply` text,
  `notify_types` varchar(100) NOT NULL DEFAULT '0',
  `feed_img_url` text,
  `feed_description` text,
  `notify_subject` varchar(100) NOT NULL DEFAULT '',
  `lang_code` varchar(10) NOT NULL,
  `comment_closed` int(1) NOT NULL DEFAULT '0',
  `auto_assign` int(1) NOT NULL DEFAULT '0',
  `last_updated` int(11) NOT NULL DEFAULT '0',
  `default_task` text,
  `default_entry` varchar(8) NOT NULL DEFAULT 'index',
  `disp_intro` int(1) DEFAULT '0',
  `use_effort_tracking` int(1) DEFAULT '0',
  `default_due_version` varchar(40) NOT NULL DEFAULT 'Undecided'
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_registrations`
--

CREATE TABLE IF NOT EXISTS `flyspray_registrations` (
  `reg_id` int(10) NOT NULL,
  `reg_time` int(11) NOT NULL DEFAULT '0',
  `confirm_code` varchar(20) NOT NULL,
  `user_name` varchar(32) NOT NULL,
  `real_name` varchar(100) NOT NULL,
  `email_address` varchar(100) NOT NULL,
  `jabber_id` varchar(100) NOT NULL,
  `notify_type` int(1) NOT NULL DEFAULT '0',
  `magic_url` varchar(40) NOT NULL,
  `time_zone` int(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_related`
--

CREATE TABLE IF NOT EXISTS `flyspray_related` (
  `related_id` int(10) NOT NULL,
  `this_task` int(10) NOT NULL DEFAULT '0',
  `related_task` int(10) NOT NULL DEFAULT '0',
  `is_duplicate` int(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_reminders`
--

CREATE TABLE IF NOT EXISTS `flyspray_reminders` (
  `reminder_id` int(10) NOT NULL,
  `task_id` int(10) NOT NULL DEFAULT '0',
  `to_user_id` int(3) NOT NULL DEFAULT '0',
  `from_user_id` int(3) NOT NULL DEFAULT '0',
  `start_time` int(11) NOT NULL DEFAULT '0',
  `how_often` int(12) NOT NULL DEFAULT '0',
  `last_sent` int(11) NOT NULL DEFAULT '0',
  `reminder_message` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_searches`
--

CREATE TABLE IF NOT EXISTS `flyspray_searches` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `search_string` text,
  `time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_tags`
--

CREATE TABLE IF NOT EXISTS `flyspray_tags` (
  `task_id` int(5) DEFAULT NULL,
  `tag` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_tasks`
--

CREATE TABLE IF NOT EXISTS `flyspray_tasks` (
  `task_id` int(10) NOT NULL,
  `project_id` int(3) NOT NULL DEFAULT '0',
  `task_type` int(3) NOT NULL DEFAULT '0',
  `date_opened` int(11) NOT NULL DEFAULT '0',
  `opened_by` int(3) NOT NULL DEFAULT '0',
  `is_closed` int(1) NOT NULL DEFAULT '0',
  `date_closed` int(11) NOT NULL DEFAULT '0',
  `closed_by` int(3) NOT NULL DEFAULT '0',
  `closure_comment` text,
  `item_summary` varchar(100) NOT NULL,
  `detailed_desc` text,
  `item_status` int(3) NOT NULL DEFAULT '0',
  `resolution_reason` int(3) NOT NULL DEFAULT '1',
  `product_category` int(3) NOT NULL DEFAULT '0',
  `product_version` int(3) NOT NULL DEFAULT '0',
  `closedby_version` int(3) NOT NULL DEFAULT '0',
  `operating_system` int(3) NOT NULL DEFAULT '0',
  `task_severity` int(3) NOT NULL DEFAULT '0',
  `task_priority` int(3) NOT NULL DEFAULT '0',
  `last_edited_by` int(3) NOT NULL DEFAULT '0',
  `last_edited_time` int(11) NOT NULL DEFAULT '0',
  `percent_complete` int(3) NOT NULL DEFAULT '0',
  `mark_private` int(1) NOT NULL DEFAULT '0',
  `due_date` int(11) NOT NULL DEFAULT '0',
  `anon_email` varchar(100) NOT NULL DEFAULT '',
  `task_token` varchar(32) NOT NULL DEFAULT '0',
  `supertask_id` int(10) DEFAULT '0',
  `list_order` int(3) DEFAULT '0',
  `estimated_effort` int(3) DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_users`
--

CREATE TABLE IF NOT EXISTS `flyspray_users` (
  `user_id` int(3) NOT NULL,
  `user_name` varchar(32) NOT NULL,
  `user_pass` varchar(40) DEFAULT NULL,
  `real_name` varchar(100) NOT NULL,
  `jabber_id` varchar(100) NOT NULL,
  `email_address` varchar(100) NOT NULL,
  `notify_type` int(1) NOT NULL DEFAULT '0',
  `notify_own` int(6) NOT NULL DEFAULT '0',
  `account_enabled` int(1) NOT NULL DEFAULT '0',
  `dateformat` varchar(30) NOT NULL DEFAULT '',
  `dateformat_extended` varchar(30) NOT NULL DEFAULT '',
  `magic_url` varchar(40) NOT NULL DEFAULT '',
  `tasks_perpage` int(3) NOT NULL DEFAULT '0',
  `register_date` int(11) NOT NULL DEFAULT '0',
  `time_zone` int(6) NOT NULL DEFAULT '0',
  `login_attempts` int(11) NOT NULL DEFAULT '0',
  `lock_until` int(11) NOT NULL DEFAULT '0',
  `lang_code` varchar(10) NOT NULL DEFAULT '0',
  `oauth_uid` varchar(255) NOT NULL DEFAULT '0',
  `oauth_provider` varchar(10) NOT NULL DEFAULT '',
  `profile_image` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_users_in_groups`
--

CREATE TABLE IF NOT EXISTS `flyspray_users_in_groups` (
  `record_id` int(5) NOT NULL,
  `user_id` int(5) NOT NULL DEFAULT '0',
  `group_id` int(3) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_user_emails`
--

CREATE TABLE IF NOT EXISTS `flyspray_user_emails` (
  `id` int(5) DEFAULT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  `oauth_uid` varchar(255) NOT NULL DEFAULT '0',
  `oauth_provider` varchar(10) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `flyspray_votes`
--

CREATE TABLE IF NOT EXISTS `flyspray_votes` (
  `vote_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `task_id` int(11) NOT NULL DEFAULT '0',
  `date_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `flyspray_admin_requests`
--
ALTER TABLE `flyspray_admin_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Index pour la table `flyspray_assigned`
--
ALTER TABLE `flyspray_assigned`
  ADD PRIMARY KEY (`assigned_id`), ADD UNIQUE KEY `flyspray_task_user` (`task_id`,`user_id`), ADD KEY `flyspray_task_id_assigned` (`task_id`,`user_id`);

--
-- Index pour la table `flyspray_attachments`
--
ALTER TABLE `flyspray_attachments`
  ADD PRIMARY KEY (`attachment_id`), ADD KEY `flyspray_task_id_attachments` (`task_id`,`comment_id`);

--
-- Index pour la table `flyspray_cache`
--
ALTER TABLE `flyspray_cache`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `flyspray_cache_type` (`type`,`topic`,`project_id`,`max_items`), ADD KEY `flyspray_cache_type_topic` (`type`,`topic`);

--
-- Index pour la table `flyspray_comments`
--
ALTER TABLE `flyspray_comments`
  ADD PRIMARY KEY (`comment_id`), ADD KEY `flyspray_task_id_comments` (`task_id`);

--
-- Index pour la table `flyspray_dependencies`
--
ALTER TABLE `flyspray_dependencies`
  ADD PRIMARY KEY (`depend_id`), ADD UNIQUE KEY `flyspray_task_id_deps` (`task_id`,`dep_task_id`);

--
-- Index pour la table `flyspray_effort`
--
ALTER TABLE `flyspray_effort`
  ADD PRIMARY KEY (`effort_id`), ADD KEY `flyspray_task_id_effort` (`task_id`);

--
-- Index pour la table `flyspray_fields`
--
ALTER TABLE `flyspray_fields`
  ADD PRIMARY KEY (`fields_id`), ADD KEY `fields_project` (`project_id`), ADD KEY `fields_listid` (`list_id`), ADD KEY `fields_type` (`fields_type`);

--
-- Index pour la table `flyspray_groups`
--
ALTER TABLE `flyspray_groups`
  ADD PRIMARY KEY (`group_id`), ADD UNIQUE KEY `flyspray_group_name` (`group_name`,`project_id`), ADD KEY `flyspray_belongs_to_project` (`project_id`);

--
-- Index pour la table `flyspray_history`
--
ALTER TABLE `flyspray_history`
  ADD PRIMARY KEY (`history_id`), ADD KEY `flyspray_idx_task_id` (`task_id`);

--
-- Index pour la table `flyspray_links`
--
ALTER TABLE `flyspray_links`
  ADD PRIMARY KEY (`link_id`), ADD KEY `flyspray_task_id_links` (`task_id`);

--
-- Index pour la table `flyspray_list_category`
--
ALTER TABLE `flyspray_list_category`
  ADD PRIMARY KEY (`category_id`);

--
-- Index pour la table `flyspray_list_lists`
--
ALTER TABLE `flyspray_list_lists`
  ADD PRIMARY KEY (`lists_id`), ADD KEY `lists_pr_id` (`project_id`);

--
-- Index pour la table `flyspray_list_os`
--
ALTER TABLE `flyspray_list_os`
  ADD PRIMARY KEY (`os_id`), ADD KEY `flyspray_project_id_os` (`project_id`);

--
-- Index pour la table `flyspray_list_resolution`
--
ALTER TABLE `flyspray_list_resolution`
  ADD PRIMARY KEY (`resolution_id`), ADD KEY `flyspray_project_id_res` (`project_id`);

--
-- Index pour la table `flyspray_list_standard`
--
ALTER TABLE `flyspray_list_standard`
  ADD PRIMARY KEY (`standard_id`), ADD KEY `lists_id` (`lists_id`);

--
-- Index pour la table `flyspray_list_status`
--
ALTER TABLE `flyspray_list_status`
  ADD PRIMARY KEY (`status_id`), ADD KEY `flyspray_project_id_status` (`project_id`);

--
-- Index pour la table `flyspray_list_tasktype`
--
ALTER TABLE `flyspray_list_tasktype`
  ADD PRIMARY KEY (`tasktype_id`), ADD KEY `flyspray_project_id_tt` (`project_id`);

--
-- Index pour la table `flyspray_list_version`
--
ALTER TABLE `flyspray_list_version`
  ADD PRIMARY KEY (`version_id`), ADD KEY `flyspray_project_id_version` (`project_id`,`version_tense`);

--
-- Index pour la table `flyspray_notifications`
--
ALTER TABLE `flyspray_notifications`
  ADD PRIMARY KEY (`notify_id`), ADD UNIQUE KEY `flyspray_task_id_notifs` (`task_id`,`user_id`);

--
-- Index pour la table `flyspray_notification_messages`
--
ALTER TABLE `flyspray_notification_messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Index pour la table `flyspray_notification_recipients`
--
ALTER TABLE `flyspray_notification_recipients`
  ADD PRIMARY KEY (`recipient_id`);

--
-- Index pour la table `flyspray_prefs`
--
ALTER TABLE `flyspray_prefs`
  ADD PRIMARY KEY (`pref_id`);

--
-- Index pour la table `flyspray_projects`
--
ALTER TABLE `flyspray_projects`
  ADD PRIMARY KEY (`project_id`);

--
-- Index pour la table `flyspray_registrations`
--
ALTER TABLE `flyspray_registrations`
  ADD PRIMARY KEY (`reg_id`);

--
-- Index pour la table `flyspray_related`
--
ALTER TABLE `flyspray_related`
  ADD PRIMARY KEY (`related_id`), ADD UNIQUE KEY `flyspray_this_task` (`this_task`,`related_task`,`is_duplicate`);

--
-- Index pour la table `flyspray_reminders`
--
ALTER TABLE `flyspray_reminders`
  ADD PRIMARY KEY (`reminder_id`);

--
-- Index pour la table `flyspray_searches`
--
ALTER TABLE `flyspray_searches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `flyspray_tasks`
--
ALTER TABLE `flyspray_tasks`
  ADD PRIMARY KEY (`task_id`), ADD KEY `flyspray_attached_to_project` (`project_id`), ADD KEY `flyspray_task_severity` (`task_severity`), ADD KEY `flyspray_task_type` (`task_type`), ADD KEY `flyspray_product_category` (`product_category`), ADD KEY `flyspray_item_status` (`item_status`), ADD KEY `flyspray_is_closed` (`is_closed`), ADD KEY `flyspray_closedby_version` (`closedby_version`), ADD KEY `flyspray_due_date` (`due_date`), ADD KEY `flyspray_task_project_super` (`project_id`,`supertask_id`,`list_order`), ADD KEY `flyspray_task_super` (`supertask_id`,`list_order`);

--
-- Index pour la table `flyspray_users`
--
ALTER TABLE `flyspray_users`
  ADD PRIMARY KEY (`user_id`), ADD UNIQUE KEY `flyspray_user_name` (`user_name`);

--
-- Index pour la table `flyspray_users_in_groups`
--
ALTER TABLE `flyspray_users_in_groups`
  ADD PRIMARY KEY (`record_id`), ADD UNIQUE KEY `flyspray_group_id_uig` (`group_id`,`user_id`), ADD KEY `flyspray_user_id_uig` (`user_id`);

--
-- Index pour la table `flyspray_votes`
--
ALTER TABLE `flyspray_votes`
  ADD PRIMARY KEY (`vote_id`), ADD KEY `flyspray_task_id_votes` (`task_id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `flyspray_admin_requests`
--
ALTER TABLE `flyspray_admin_requests`
  MODIFY `request_id` int(5) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_assigned`
--
ALTER TABLE `flyspray_assigned`
  MODIFY `assigned_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_attachments`
--
ALTER TABLE `flyspray_attachments`
  MODIFY `attachment_id` int(5) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_cache`
--
ALTER TABLE `flyspray_cache`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_comments`
--
ALTER TABLE `flyspray_comments`
  MODIFY `comment_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_dependencies`
--
ALTER TABLE `flyspray_dependencies`
  MODIFY `depend_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_effort`
--
ALTER TABLE `flyspray_effort`
  MODIFY `effort_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_fields`
--
ALTER TABLE `flyspray_fields`
  MODIFY `fields_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=93;
--
-- AUTO_INCREMENT pour la table `flyspray_groups`
--
ALTER TABLE `flyspray_groups`
  MODIFY `group_id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT pour la table `flyspray_history`
--
ALTER TABLE `flyspray_history`
  MODIFY `history_id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT pour la table `flyspray_links`
--
ALTER TABLE `flyspray_links`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_list_category`
--
ALTER TABLE `flyspray_list_category`
  MODIFY `category_id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT pour la table `flyspray_list_lists`
--
ALTER TABLE `flyspray_list_lists`
  MODIFY `lists_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=70;
--
-- AUTO_INCREMENT pour la table `flyspray_list_os`
--
ALTER TABLE `flyspray_list_os`
  MODIFY `os_id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pour la table `flyspray_list_resolution`
--
ALTER TABLE `flyspray_list_resolution`
  MODIFY `resolution_id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT pour la table `flyspray_list_standard`
--
ALTER TABLE `flyspray_list_standard`
  MODIFY `standard_id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=35;
--
-- AUTO_INCREMENT pour la table `flyspray_list_status`
--
ALTER TABLE `flyspray_list_status`
  MODIFY `status_id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT pour la table `flyspray_list_tasktype`
--
ALTER TABLE `flyspray_list_tasktype`
  MODIFY `tasktype_id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT pour la table `flyspray_list_version`
--
ALTER TABLE `flyspray_list_version`
  MODIFY `version_id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT pour la table `flyspray_notifications`
--
ALTER TABLE `flyspray_notifications`
  MODIFY `notify_id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT pour la table `flyspray_notification_messages`
--
ALTER TABLE `flyspray_notification_messages`
  MODIFY `message_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_notification_recipients`
--
ALTER TABLE `flyspray_notification_recipients`
  MODIFY `recipient_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_prefs`
--
ALTER TABLE `flyspray_prefs`
  MODIFY `pref_id` int(1) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=38;
--
-- AUTO_INCREMENT pour la table `flyspray_projects`
--
ALTER TABLE `flyspray_projects`
  MODIFY `project_id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT pour la table `flyspray_registrations`
--
ALTER TABLE `flyspray_registrations`
  MODIFY `reg_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_related`
--
ALTER TABLE `flyspray_related`
  MODIFY `related_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_reminders`
--
ALTER TABLE `flyspray_reminders`
  MODIFY `reminder_id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `flyspray_searches`
--
ALTER TABLE `flyspray_searches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT pour la table `flyspray_tasks`
--
ALTER TABLE `flyspray_tasks`
  MODIFY `task_id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT pour la table `flyspray_users`
--
ALTER TABLE `flyspray_users`
  MODIFY `user_id` int(3) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT pour la table `flyspray_users_in_groups`
--
ALTER TABLE `flyspray_users_in_groups`
  MODIFY `record_id` int(5) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT pour la table `flyspray_votes`
--
ALTER TABLE `flyspray_votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `flyspray_list_standard`
--
ALTER TABLE `flyspray_list_standard`
ADD CONSTRAINT `forkey_lists_id_standard` FOREIGN KEY (`lists_id`) REFERENCES `flyspray_list_lists` (`lists_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
