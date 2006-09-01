<?php
require('header.php');
?>
<html>
<head>
<title>Flyspray upgrade script</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<link href="themes/<?php echo $fs->prefs['theme_style'];?>/theme.css" rel="stylesheet" type="text/css">
</head>

<body>
<h2 class="subheading">Flyspray Upgrade script - version 0.9.3.1 to version 0.9.4</h2>
<?php
$page = $_GET['page'];
if (!$page) {

// Query to see if this database has been already upgraded to 0.9.4
$checkdb = $fs->dbQuery("SHOW TABLES");
while ($tables_array = $fs->dbFetchArray($checkdb)) {
  list($table) = $tables_array;
  if (ereg("flyspray_notifications", $table)) {
    $upgraded = "yes";
  } else {
  }
}

  if ($upgraded == 'yes') {
    echo "<table class=\"admin\"><tr><td class=\"text\">Your Flyspray database has already been upgraded for use with version 0.9.4.  You can delete this script.<br><br>";
	echo "<a href=\"./\">Take me to Flyspray 0.9.4 now!</a></td></tr><table>";
  } else {
    echo "<table class=\"admin\"><tr><td class=\"text\">This script will upgrade your database for use with Flyspray 0.9.4.";
    echo " You should ensure that your database settings are correct in config.inc.php before continuing.";
    echo "<br><br><a href=\"upgrade_0.9.3.1_to_0.9.4.php?page=2\">Perform upgrade now!</a></td></tr></table>";
  };
  
} elseif ($page == '2') {

// Query to see if this database has been already upgraded to 0.9.4
$checkdb = $fs->dbQuery("SHOW TABLES");
while ($tables_array = $fs->dbFetchArray($checkdb)) {
  list($table) = $tables_array;
  if (ereg("flyspray_notifications", $table)) {
    $upgraded = "yes";
  } else {
  }
}

  if ($upgraded == 'yes') {
    echo "<table class=\"admin\"><tr><td class=\"text\">Your Flyspray database has already been upgraded for use with version 0.9.4.  You can delete this script.<br><br>";
	echo "<a href=\"./\">Take me to Flyspray 0.9.4 now!</a></td></tr><table>";
  } else {

  $upgrade = $fs->dbQuery("CREATE TABLE flyspray_related (
                           related_id mediumint(10) NOT NULL auto_increment,
                           this_task mediumint(10) NOT NULL default '0',
                           related_task mediumint(10) NOT NULL default '0',
                           PRIMARY KEY  (related_id)
                           ) TYPE=MyISAM COMMENT='Related task entries'
                           ");

  $get_related = $fs->dbQuery("SELECT task_id, related_task_id FROM flyspray_tasks WHERE related_task_id != '0'");
  while ($row = $fs->dbFetchArray($get_related)) {
    $convert_related = $fs->dbQuery("INSERT INTO flyspray_related VALUES
                                     ('', '{$row['task_id']}', '{$row['related_task_id']}')
                                    ");
  };

$upgrade = $fs->dbQuery("ALTER TABLE `flyspray_tasks` DROP `related_task_id`");

$upgrade = $fs->dbQuery("CREATE TABLE flyspray_notifications (
                          notify_id mediumint(10) NOT NULL auto_increment,
                          task_id mediumint(10) NOT NULL default '0',
                          user_id mediumint(5) NOT NULL default '0',
                          PRIMARY KEY  (notify_id)
                          ) TYPE=MyISAM COMMENT='Extra task notifications are stored here'");

$upgrade = $fs->dbQuery("ALTER TABLE `flyspray_list_category` ADD `category_owner` MEDIUMINT( 3 ) NOT NULL");

$upgrade = $fs->dbQuery("INSERT INTO `flyspray_prefs` ( `pref_id` , `pref_name` , `pref_value` , `pref_desc` )
                          VALUES (
                            '', 'default_cat_owner', '', 'Default category owner'
                        )");

$upgrade = $fs->dbQuery("INSERT INTO `flyspray_prefs` ( `pref_id` , `pref_name` , `pref_value` , `pref_desc` )
                          VALUES (
                            '', 'lang_code', 'en', 'Language'
                        )");

$upgrade = $fs->dbQuery("INSERT INTO `flyspray_prefs` ( `pref_id` , `pref_name` , `pref_value` , `pref_desc` )
                          VALUES (
                            '', 'spam_proof', '', 'Use confirmation codes for user registrations'
                        )");

$upgrade = $fs->dbQuery("INSERT INTO `flyspray_prefs` ( `pref_id` , `pref_name` , `pref_value` , `pref_desc` )
                          VALUES (
                            '', 'anon_view', '1', 'Allow anonymous users to view this BTS'
                        )");

$upgrade = $fs->dbQuery("CREATE TABLE `flyspray_registrations` (
                          `reg_id` mediumint(10) NOT NULL auto_increment,
                          `reg_time` varchar(12) NOT NULL default '',
                          `confirm_code` varchar(20) NOT NULL default '',
                          PRIMARY KEY  (`reg_id`)
                          ) TYPE=MyISAM COMMENT='Storage for new user registration confirmation codes'
                        ");

$upgrade = $fs->dbQuery("DROP TABLE `flyspray_list_severity`");
$upgrade = $fs->dbQuery("DROP TABLE `flyspray_list_status`");

// Since I made a mistake in the SQL for the previous release, the severities need fixing up for this release
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET task_severity = '1' WHERE task_severity = '2'");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET task_severity = '2' WHERE task_severity = '3'");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET task_severity = '3' WHERE task_severity = '4'");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET task_severity = '4' WHERE task_severity = '5'");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET task_severity = '5' WHERE task_severity = '6'");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET task_severity = '5' WHERE task_severity = '7'");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET task_severity = '5' WHERE task_severity = '8'");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET task_severity = '5' WHERE task_severity = '9'");
$upgrade = $fs->dbQuery("UPDATE flyspray_tasks SET task_severity = '5' WHERE task_severity = '10'");


  echo "<table class=\"admin\"><tr><td class=\"text\">Your Flyspray database is now upgraded for use with version 0.9.4.  You can delete this script.<br><br>";
  echo "<a href=\"./\">Take me to Flyspray 0.9.4 now!</a></td></tr><table>";
};

};
?>

</body>
</html>
