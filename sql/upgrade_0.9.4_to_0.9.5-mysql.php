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
<h2 class="subheading">Flyspray Upgrade script - version 0.9.4 to version 0.9.5</h2>
<?php
$page = $_GET['page'];
if (!$page) {

// Query to see if this database has been already upgraded to 0.9.5
$checkdb = $fs->dbQuery("SHOW TABLES");
while ($tables_array = $fs->dbFetchArray($checkdb)) {
  list($table) = $tables_array;
  if (ereg("flyspray_projects", $table)) {
    $upgraded = "yes";
  } else {
  }
}

  if ($upgraded == 'yes') {
    echo "<table class=\"admin\"><tr><td class=\"text\">Your Flyspray database has already been upgraded for use with version 0.9.5.  You can delete this script.<br><br>";
	echo "<a href=\"../\">Take me to Flyspray 0.9.5 now!</a></td></tr><table>";

  } else {
    echo "<table class=\"admin\"><tr><td class=\"text\">This script will upgrade your database for use with Flyspray 0.9.5.";
    echo " You should ensure that your database settings are correct in header.php before continuing.";
    echo "<br><br><a href=\"" . $_SERVER['PHP_SELF'] . "?page=2\">Perform upgrade now!</a></td></tr></table>";
    //echo $_SERVER['PHP_SELF'];
  };

} elseif ($page == '2') {

// Query to see if this database has been already upgraded to 0.9.5
$checkdb = $fs->dbQuery("SHOW TABLES");
while ($tables_array = $fs->dbFetchArray($checkdb)) {
  list($table) = $tables_array;
  if (ereg("flyspray_projects", $table)) {
    $upgraded = "yes";
  } else {
  }
}

  if ($upgraded == 'yes') {
    echo "<table class=\"admin\"><tr><td class=\"text\">Your Flyspray database has already been upgraded for use with version 0.9.5.  You can delete this script.<br><br>";
	echo "<a href=\"../\">Take me to Flyspray 0.9.5 now!</a></td></tr><table>";

  } else {

$upgrade = $fs->dbQuery("CREATE TABLE flyspray_projects (
                           project_id mediumint(3) NOT NULL auto_increment,
                           project_title varchar(100) NOT NULL default '',
                           theme_style varchar(20) NOT NULL default '0',
                           show_logo MEDIUMINT(1) NOT NULL default '0',
                           default_cat_owner MEDIUMINT(3) NOT NULL default '0',
                           intro_message longtext NOT NULL,
                           project_is_active MEDIUMINT(1) NOT NULL default '0',
                           PRIMARY KEY  (project_id)
                           ) TYPE=MyISAM COMMENT='Details on multiple Flyspray projects'
                        ");

$insert_project = $fs->dbQuery("INSERT INTO flyspray_projects
                                  VALUES (
                                          1,
                                          '{$fs->prefs['project_title']}',
                                          'Bluey',
                                          1,
                                          1,
                                          'Please ensure that your browser has cookies enabled if you want this software to work properly...',
                                          1
                                          )
                                ");

$upgrade = $fs->dbQuery("ALTER TABLE `flyspray_tasks`
                           ADD `attached_to_project` MEDIUMINT( 3 ) NOT NULL AFTER `task_id`
                       ");

$convert = $fs->dbQuery("UPDATE flyspray_tasks SET attached_to_project = '1'");

$upgrade = $fs->dbQuery("INSERT INTO `flyspray_prefs` ( `pref_id` , `pref_name` , `pref_value` , `pref_desc` )
                          VALUES (
                            '', 'default_project', '1', 'Default project id'
                        )");

$upgrade = $fs->dbQuery("ALTER TABLE `flyspray_list_category` ADD `project_id` MEDIUMINT( 3 ) NOT NULL AFTER `category_id`");
$upgrade = $fs->dbQuery("ALTER TABLE `flyspray_list_os` ADD `project_id` MEDIUMINT( 3 ) NOT NULL AFTER `os_id`");
$upgrade = $fs->dbQuery("ALTER TABLE `flyspray_list_version` ADD `project_id` MEDIUMINT( 3 ) NOT NULL AFTER `version_id`");

$convert = $fs->dbQuery("UPDATE flyspray_list_category SET project_id = '1'");
$convert = $fs->dbQuery("UPDATE flyspray_list_os SET project_id = '1'");
$convert = $fs->dbQuery("UPDATE flyspray_list_version SET project_id = '1'");

$upgrade = $fs->dbQuery("ALTER TABLE `flyspray_list_version` CHANGE `version_name` `version_name` VARCHAR( 20 ) NOT NULL");
$upgrade = $fs->dbQuery("ALTER TABLE `flyspray_list_resolution` CHANGE `resolution_name` `resolution_name` VARCHAR( 30 ) NOT NULL ");

  echo "<table class=\"admin\"><tr><td class=\"text\">Your Flyspray database is now upgraded for use with version 0.9.5.  You can delete this script.<br><br>";
  echo "<a href=\"../\">Take me to Flyspray 0.9.5 now!</a></td></tr><table>";
};

};
?>

</body>
</html>
