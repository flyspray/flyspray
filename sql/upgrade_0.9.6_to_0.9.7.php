<?php
// Include the header file that includes the database information
include('../header.php');
?>

<html>
<head>
<title>Flyspray upgrade script</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<link href="../themes/Bluey/theme.css" rel="stylesheet" type="text/css">
</head>

<body>
<h1 id="title"></h1>
<h2 class="subheading">Upgrade Flyspray</h2>
 Version 0.9.6 to version 0.9.7
<?php

// First check that the current install is at least version 0.9.6
$check_ver = $fs->dbQuery("SELECT pref_value FROM flyspray_prefs WHERE pref_name = 'dateformat'");
if ($fs->dbCountRows($check_ver) > 0)
{
   $prev_ver = 'yes';
}

// Check to see if this database has been already upgraded to 0.9.7
$checkdb = $fs->dbQuery("SELECT pref_value FROM flyspray_prefs WHERE pref_name = 'fs_ver'");
if ($fs->dbCountRows($checkdb) > 0)
{
   $upgraded = 'yes';
}

$page = $_GET['page'];
if (!$page) {

  if ($upgraded != 'yes' && $prev_ver == 'yes') {
    
    echo '<table class="box"><tr><td class="text">This script will upgrade your Flyspray database structure for use with Flyspray 0.9.7.';
    echo '<br><br><a href="' . $_SERVER['PHP_SELF'] . '?page=2">Perform upgrade now!</a></td></tr></table>';
    
  } else {
  
    echo '<table class="box"><tr><td class="text">Your Flyspray database has already been upgraded for use with version 0.9.7.  You can delete this script.<br><br>';
    echo '<a href="../">Take me to Flyspray 0.9.7 now!</a></td></tr><table>';

  };

} elseif ($page == '2') {

  if ($upgraded != 'yes' && $prev_ver == 'yes') {

  
   // Perform the upgrade now!
   
   // Retrieve the database schema into a string
   $sql_file = file_get_contents('upgrade_0.9.6_to_0.9.7.' . $dbtype);

   // Separate each query
   $sql = explode(';', $sql_file);

   // Cycle through the queries and insert them into the database
   while (list($key, $val) = each($sql)) {
      //echo '<br />' . $val;
      $insert = $fs->dbQuery($val);
   };
   
   
   // Now create entries in the flyspray_users_in_groups table, otherwise current users will not work!
   $user_query = $fs->dbQuery("SELECT * FROM flyspray_users ORDER BY user_id ASC");
   while ($row = $fs->dbFetchArray($user_query)) {
     $insert = $fs->dbQuery("INSERT INTO flyspray_users_in_groups
                            (user_id, group_id)
                            VALUES(?, ?)",
                            array($row['user_id'], $row['group_in']));
  };


   // Cycle though existing projects to create PM groups for them
   $get_projects = $fs->dbQuery("SELECT project_id FROM flyspray_projects");
   while ($row = $fs->dbFetchArray($get_projects))
   {
      // Create the groups now!
      $add_group = $fs->dbQuery("INSERT INTO flyspray_groups
                                (group_name,
                                 group_desc,
                                 belongs_to_project,
                                 manage_project,
                                 view_tasks,
                                 open_new_tasks,
                                 modify_own_tasks,
                                 modify_all_tasks,
                                 view_comments,
                                 add_comments,
                                 edit_comments,
                                 delete_comments,
                                 create_attachments,
                                 delete_attachments,
                                 view_history,
                                 close_own_tasks,
                                 close_other_tasks,
                                 assign_to_self,
                                 assign_others_to_self,
                                 view_reports,
                                 group_open)
                                 VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                                 array('Project Managers', 'Permission to do anything related to this project.' ,
                                       intval($row['project_id']),
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1',
                                       '1')
                                );
   
      // End of cycling through projects to create PM groups
      };
   
   echo '<table class="box"><tr><td class="text">Your Flyspray database is now upgraded for use with version 0.9.7.  You should delete the entire <i>sql/</i> directory.<br><br>';
   echo '<br /><br />';
   echo 'Flyspray 0.9.7 has support for seperate user groups per project.  Consider <a href="http://flyspray.rocks.cc/?p=Documentation">';
   echo 'reading the documentation</a> to fully understand how global and project-level groups work.';
   echo '<br /><br />';
   echo '<a href="../">Take me to Flyspray 0.9.7 now!</a></td></tr><table>';


  } else {

    echo '<table class="box"><tr><td class="text">Your MYSQL Flyspray database has already been upgraded for use with version 0.9.7.  You should delete the entire <i>sql/</i> directory.<br><br>';
    echo '<a href="../">Take me to Flyspray 0.9.7 now!</a></td></tr><table>';


  };

// End of pages
};
?>

</body>
</html>
