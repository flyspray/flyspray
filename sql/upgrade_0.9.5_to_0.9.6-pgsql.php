<?php

include('../header.php');

?>
<html>
<head>
<title>Flyspray upgrade script</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<link href="themes/Bluey/theme.css" rel="stylesheet" type="text/css">
</head>

<body>
<h2 class="subheading">Flyspray Upgrade script - version 0.9.5 to version 0.9.6</h2>
<?php
$page = $_GET['page'];
if (!$page) {

// Query to see if this database has been already upgraded to 0.9.6
$checkdb = $fs->dbQuery("SHOW TABLES");
while ($tables_array = $fs->dbFetchArray($checkdb)) {
  list($table) = $tables_array;
  if (ereg("flyspray_history", $table)) {
    $upgraded = "yes";
  } else {
  }
}

  if ($upgraded == 'yes') {
    echo "<table class=\"admin\"><tr><td class=\"text\">Your POSTGRESQL Flyspray database has already been upgraded for use with version 0.9.6.  You can delete this script.<br><br>";
	echo "<a href=\"../\">Take me to Flyspray 0.9.6 now!</a></td></tr><table>";

  } else {
    echo "<table class=\"admin\"><tr><td class=\"text\">This script will upgrade your POSTGRESQL database for use with Flyspray 0.9.6.";
    echo " You should ensure that your database settings are correct in <b>flyspray.conf.php</b> before continuing.";
    echo "<br><br><a href=\"" . $_SERVER['PHP_SELF'] . "?page=2\">Perform upgrade now!</a></td></tr></table>";
  };

} elseif ($page == '2') {

// Query to see if this database has been already upgraded to 0.9.6
$checkdb = $fs->dbQuery("SHOW TABLES");
while ($tables_array = $fs->dbFetchArray($checkdb)) {
  list($table) = $tables_array;
  if (ereg("flyspray_history", $table)) {
    $upgraded = "yes";
  } else {
  }
}

  if ($upgraded == 'yes') {
    echo "<table class=\"admin\"><tr><td class=\"text\">Your POSTGRESQL Flyspray database has already been upgraded for use with version 0.9.6.  You can delete this script.<br><br>";
	echo "<a href=\"../\">Take me to Flyspray 0.9.6 now!</a></td></tr><table>";

  } else {

$upgrade = $fs->dbQuery("CREATE TABLE flyspray_reminders (
  reminder_id	    SERIAL NOT NULL,
  task_id	    INTEGER NOT NULL default '0',
  to_user_id	    INTEGER NOT NULL default '0',
  from_user_id	    INTEGER NOT NULL default '0',
  start_time	    TEXT NOT NULL default '0',
  how_often	    INTEGER NOT NULL default '0',
  last_sent	    TEXT NOT NULL default '0',
  reminder_message  TEXT NOT NULL,
  PRIMARY KEY (reminder_id)
); ");

$upgrade = $fs->dbQuery("ALTER TABLE flyspray_tasks ADD is_closed INTEGER;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_tasks ALTER COLUMN is_closed SET DEFAULT 0;");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET is_closed = 0;");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET is_closed = 1 WHERE item_status = 8;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_tasks ALTER COLUMN is_closed SET NOT NULL;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_projects ADD inline_images INTEGER;");
$upgrade = $fs->dbQuery("UPDATE flyspray_projects SET inline_images = 0;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_projects ALTER COLUMN inline_images SET NOT NULL;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_tasks ADD closure_comment TEXT;");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET closure_comment = '';");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_tasks ALTER COLUMN closure_comment SET NOT NULL;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_tasks ALTER COLUMN closure_comment SET DEFAULT '';");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_users ADD dateformat TEXT;");
$upgrade = $fs->dbQuery("UPDATE flyspray_users SET dateformat = '';");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_users ALTER COLUMN dateformat SET NOT NULL;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_users ALTER COLUMN dateformat SET DEFAULT '';");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_users ADD dateformat_extended TEXT;");
$upgrade = $fs->dbQuery("UPDATE flyspray_users SET dateformat_extended = '';");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_users ALTER COLUMN dateformat_extended SET NOT NULL;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_users ALTER COLUMN dateformat_extended SET DEFAULT '';");
$upgrade = $fs->dbQuery("INSERT INTO flyspray_prefs(pref_name, pref_value, pref_desc) VALUES ('dateformat', '', 'Default date format for new users and guests used in the task list');");
$upgrade = $fs->dbQuery("INSERT INTO flyspray_prefs(pref_name, pref_value, pref_desc) VALUES ('dateformat_extended', '', 'Default date format for new users and guests used in task details');");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_list_category ADD parent_id INTEGER;");
$upgrade = $fs->dbQuery("UPDATE flyspray_list_category SET parent_id = 0;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_list_category ALTER COLUMN parent_id SET NOT NULL;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_list_category ALTER COLUMN parent_id SET DEFAULT 0;");
$upgrade = $fs->dbQuery("CREATE TABLE flyspray_history (
  history_id	SERIAL NOT NULL,
  task_id	INTEGER NOT NULL default '0',
  user_id	INTEGER NOT NULL default '0',
  event_date	TEXT NOT NULL default '',
  event_type	INTEGER NOT NULL default '0',
  field_changed TEXT NOT NULL default '',
  old_value	TEXT NOT NULL default '',
  new_value	TEXT NOT NULL default '',
  PRIMARY KEY (history_id)
);");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_projects ADD visible_columns TEXT;");
$upgrade = $fs->dbQuery("UPDATE flyspray_projects SET visible_columns = 'id category tasktype severity summary dateopened status progress';");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_projects ALTER COLUMN visible_columns SET NOT NULL;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_list_version ADD version_tense INTEGER;");
$upgrade = $fs->dbQuery("UPDATE flyspray_list_version SET version_tense = '2';");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_list_version ALTER COLUMN version_tense SET NOT NULL;");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET last_edited_time=date_opened WHERE last_edited_time='0' OR last_edited_time='' OR last_edited_time IS NULL;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_tasks ADD COLUMN task_priority INTEGER;");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET task_priority = 2;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_tasks ALTER COLUMN task_priority SET DEFAULT 2;");
$upgrade = $fs->dbQuery("ALTER TABLE flyspray_tasks ALTER COLUMN task_priority SET NOT NULL;");


// Initialise the history table with basic information we 
// Gather from the existing tables.

//Tasks opened
$init_history = $fs->dbQuery("INSERT INTO flyspray_history (task_id, user_id, event_date, event_type)
                    SELECT task_id, opened_by AS user_id, date_opened AS event_date, 1 AS event_type
                    FROM flyspray_tasks");

//Tasks closed
$init_history = $fs->dbQuery("INSERT INTO flyspray_history (task_id, user_id, event_date, event_type, new_value)
                    SELECT task_id, closed_by AS user_id, date_closed AS event_date, 2 AS event_type, resolution_reason AS new_value
                    FROM flyspray_tasks
                    WHERE is_closed = 1");

//Tasks edited
$init_history = $fs->dbQuery("INSERT INTO flyspray_history (task_id, user_id, event_date, event_type)
                    SELECT task_id, last_edited_by AS user_id, last_edited_time AS event_date, 3 AS event_type
                    FROM flyspray_tasks
                    WHERE last_edited_by <> 0");

//Comments added
$init_history = $fs->dbQuery("INSERT INTO flyspray_history (task_id, user_id, event_date, event_type, new_value)
                    SELECT t.task_id, c.user_id AS user_id, c.date_added AS event_date, 4 AS event_type, c.comment_id AS new_value
                    FROM flyspray_tasks t
                    RIGHT JOIN flyspray_comments c ON t.task_id = c.task_id");

//Attachments added
$init_history = $fs->dbQuery("INSERT INTO flyspray_history (task_id, user_id, event_date, event_type, new_value)
                    SELECT t.task_id, a.added_by AS user_id, a.date_added AS event_date, 7 AS event_type, a.attachment_id AS new_value
                    FROM flyspray_tasks t
                    RIGHT JOIN flyspray_attachments a ON t.task_id = a.task_id");



  
echo "<table class=\"admin\"><tr><td class=\"text\">Your  POSTGRESQL Flyspray database is now upgraded for use with version 0.9.6.  You can delete this script.<br><br>";
echo "<a href=\"../\">Take me to Flyspray 0.9.6 now!</a></td></tr><table>";

// End of checking if upgrade is already done
};

// End of pages
};
?>

</body>
</html>
