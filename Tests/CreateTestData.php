<?php

# temp: just wrapped that code in a function so it is not run by phpunit
function createTestData(){
    # quick safety
    exit;

    if (PHP_SAPI !== 'cli') {
        die('');
    }

// Use this only on a new test installation, code does not work on
// an existing one, and never will.

// Borg Inc. is a big multinational company delivering
$maxproducts = 50;
// both to it's
$maxcorporateusers = 450;
// working in
$maxcorporates = 50;
// and
$maxindividualusers = 10;
// who are all happy to report to us about
$maxtasks = 150;
// the many problems in our products. And then there are also
$maxviewers = 50;
// who just like to watch what's going on here at Borg Inc.
// Our users are also keen to add attachments to their reports and comments, so there are
$maxattachments = 500;
// in our database;
// Our users are also very active with commenting.
$maxcomments = 750;
// To handle all the resulting work, we need
$maxadmins = 3;
$maxmanagers = 5;
$maxdevelopers = 50;
// people working together all over the globe to care for their needs.

// We also have both a very innovative and standardized naming scheme for our products.
// And we have also made one big invention offered only to our customers in the world:
// Time travel! You can comment on tasks and other comments in them even before the task
// is even opened or the original comment made.

// Add more according to your taste...
$subjects[] = "%s sucks!";
$subjects[] = "%s is utterly crap!";
$subjects[] = "Developers of %s should be hanged!";
$subjects[] = "Developers of %s should be strangled!";
$subjects[] = "Developers of %s should be eaten alive by zombies!";
$subjects[] = "Developers of %s should be thrown in a pit of snakes!";
$subjects[] = "Who is the idiot responsible for %s?";

error_reporting(E_ALL);

// die('Enable me by commenting this out by editing and read the contents first!'.basename(__FILE__).' at line '.__LINE__);
define('IN_FS', 1);

require_once dirname(__FILE__) . '/../includes/fix.inc.php';
require_once dirname(__FILE__) . '/../includes/class.flyspray.php';
require_once dirname(__FILE__) . '/../includes/constants.inc.php';
require_once dirname(__FILE__) . '/../includes/i18n.inc.php';
require_once dirname(__FILE__) . '/../includes/class.tpl.php';
require_once dirname(__FILE__) . '/../vendor/autoload.php';

$conf = @parse_ini_file('../flyspray.conf.php', true) or die('Cannot open config file.');

$db = new Database;
$db->dbOpenFast($conf['database']);
$RANDOP = 'RAND()';
if ($db->dblink->dataProvider == 'postgres') {
    $RANDOP = 'RANDOM()';
}

$fs = new Flyspray();
$user = new User(1);
$proj = new Project(1);
$notify = new Notifications;
load_translations();

for ($i = 1; $i <= $maxadmins; $i++) {
    $user_name = "admin$i";
    $real_name = "Administrator $i";
    $password = $user_name;
    $time_zone = 0; // Assign different one!
    $email = null; // $user_name . '@foo.bar.baz.org';

    Backend::create_user($user_name, $password, $real_name, '', $email, 0, $time_zone, 1, 1);
}

for ($i = 1; $i <= $maxmanagers; $i++) {
    $user_name = "pm$i";
    $real_name = "Project Manager $i";
    $password = $user_name;
    $time_zone = 0; // Assign different one!
    $email = null; // $user_name . '@foo.bar.baz.org';

    Backend::create_user($user_name, $password, $real_name, '', $email, 0, $time_zone, 2, 1);
}
$db->Query('UPDATE {projects} SET project_is_active = 0 WHERE project_id = 1');
// Show more columns by default, trying to make database or flyspray crash under stress.
$db->Query("UPDATE {prefs} SET pref_value = 'id project category tasktype severity summary status openedby dateopened progress comments attachments votes' WHERE pref_name = 'visible_columns'");

// Add 3 different Global developer groups with different
// view rights first, then assign developers to them at random.
// Borg Inc. has a strict hierarchy on who can see and do what.

// Somewhat more relaxed with our own developers.

$db->Query("INSERT INTO {groups} "
        . "(group_name,group_desc,project_id,group_open,view_comments,manage_project,view_tasks, view_groups_tasks, view_own_tasks,open_new_tasks,modify_own_tasks) "
        . "VALUES('Developer Group 1', 'Developer Group 1', 0, 1, 1, 0, 1, 1, 1, 1, 1)");
$db->Query("INSERT INTO {groups} "
        . "(group_name,group_desc,project_id,group_open,view_comments,manage_project,view_tasks, view_groups_tasks, view_own_tasks,open_new_tasks,modify_own_tasks) "
        . "VALUES('Developer Group 2', 'Developer Group 2', 0, 1, 1, 0, 0, 1, 1, 1, 1)");
$db->Query("INSERT INTO {groups} "
        . "(group_name,group_desc,project_id,group_open,view_comments,manage_project,view_tasks, view_groups_tasks, view_own_tasks,open_new_tasks,modify_own_tasks) "
        . "VALUES('Developer Group 3', 'Developer Group 3', 0, 1, 1, 0, 0, 0, 1, 1, 1)");

// Add also general groups for corporate users, individual users and viewers.
// Allow only login. Not so relaxed with them bastards.
$db->Query("INSERT INTO {groups} "
        . "(group_name,group_desc,project_id,group_open) "
        . "VALUES('Corporate Users', 'Corporate Users', 0, 1)");
$db->Query("INSERT INTO {groups} "
        . "(group_name,group_desc,project_id,group_open) "
        . "VALUES('Trusted Users', 'Trusted Users', 0, 1)");
$db->Query("INSERT INTO {groups} "
        . "(group_name,group_desc,project_id,group_open) "
        . "VALUES('Non-trusted Users', 'Non-trusted Users', 0, 1)");


for ($i = 1; $i <= $maxdevelopers; $i++) {
    $user_name = "dev$i";
    $real_name = "Developer $i";
    $password = $user_name;
    $time_zone = 0; // Assign different one!
    $email = $email = null; $user_name . '@foo.bar.baz.org';
    $group = rand(7, 9);

    Backend::create_user($user_name, $password, $real_name, '', $email, 0, $time_zone, $group, 1);
}

// We have been really active in the past years, AND have a lot of projects.
for ($i = 1; $i <= $maxproducts; $i++) {
    $projname = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, mt_rand(8, 12)));
    $projname = 'Borg Inc. Product ' . preg_replace('/^(.{3})(.+)$/', '$1-$2', $projname);
    
      $db->Query('INSERT INTO  {projects}
      ( project_title, theme_style, intro_message,
      others_view, anon_open, project_is_active,
      visible_columns, visible_fields, lang_code,
      notify_email, notify_jabber, disp_intro)
      VALUES  (?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?)',
      array($projname, 'CleanFS', "Welcome to $projname", 0, 0,
      'id category tasktype severity summary status openedby dateopened progress comments attachments votes',
      'supertask tasktype category severity priority status private assignedto reportedin dueversion duedate progress os votes',
      'en', '', '', 1));
     
    add_project_data();
    
}

// Assign some of the poor developers project manager or project developer
// rights to some of the projects they must work on.
for ($i = 1; $i <= $maxproducts; $i++) {
    $projid = $i + 1;
    $sql = $db->Query('SELECT group_id FROM {groups} WHERE project_id = ? AND manage_project = 1', array($projid));
    $pmgroup = $db->FetchOne($sql);
    $sql = $db->Query('SELECT group_id FROM {groups} WHERE project_id = ? AND manage_project = 0', array($projid));
    $pdgroup = $db->FetchOne($sql);
    
    $pmlimit = intval($maxdevelopers / 100) + rand(-2, 2);
    $pdlimit = intval($maxdevelopers / 20) + rand(-10, 10);
    $pmlimit = $pmlimit < 1 ? 1 : $pmlimit;
    $pdlimit = $pdlimit < 1 ? 1 : $pdlimit;
    
    $sql = $db->Query("SELECT user_id FROM {users_in_groups} WHERE group_id in (7, 8, 9) ORDER BY $RANDOP limit $pmlimit");
    $pms = $db->fetchCol($sql);
    $sql = $db->Query("SELECT user_id FROM {users_in_groups} WHERE group_id in (8, 9) ORDER BY $RANDOP limit $pdlimit");
    $pds = $db->fetchCol($sql);
    
    foreach ($pms as $pm) {
        $db->Query('INSERT INTO {users_in_groups} (user_id, group_id) values (?, ?)', array($pm, $pmgroup));
    }
    
    foreach ($pds as $pd) {
        $check = $db->Query('SELECT * FROM {users_in_groups} WHERE user_id = ? AND group_id = ?', array($pd, $pmgroup));
        if (!$db->CountRows($check)) {
            $db->Query('INSERT INTO {users_in_groups} (user_id, group_id) values (?, ?)', array($pd, $pdgroup));
        }
    }
    
}

// Create corporate users.
for ($i = 1; $i <= $maxcorporateusers; $i++) {
    $user_name = "cu$i";
    $real_name = "Corporate user $i";
    $password = $user_name;
    $time_zone = 0; // Assign different ones!
    $email = $email = null; $user_name . '@foo.bar.baz.org';
    $group = 10;

    Backend::create_user($user_name, $password, $real_name, '', $email, 0, $time_zone, $group, 1);
}

// Now, create corporate user groups for some of our projects.
// Just %5 change of getting added.
for ($i = 1; $i <= $maxcorporates; $i++) {
    for ($j = 1; $j <= $maxproducts; $j++) {
        if (rand(1, 20) == 1) {
            $projid = $j + 1;
            $db->Query("INSERT INTO {groups} "
                    . "(group_name,group_desc,project_id,manage_project,view_tasks, view_groups_tasks, view_own_tasks,open_new_tasks,add_comments,create_attachments,group_open,view_comments) "
                    . "VALUES('Corporate $i', 'Corporate $i Users', $projid, 0, 0, 1, 1, 1, 1, 1,1,1)");
            $sql = $db->Query('SELECT MAX(group_id) FROM {groups}');
            $group_id = $db->FetchOne($sql);
            // Then, add users
            for ($k = $i; $k <= $maxcorporateusers; $k += $maxcorporates) {
                $username = "cu$k";
                $sql = $db->Query('SELECT user_id FROM {users} WHERE user_name = ?', array($username));
                $user_id = $db->FetchOne($sql);
                $db->Query('INSERT INTO {users_in_groups} (user_id, group_id) VALUES (?, ?)', array($user_id, $group_id));
            }
        }
    }
}
// And also those individual users...
for ($i = 1; $i <= $maxindividualusers; $i++) {
    $user_name = "iu$i";
    $real_name = "Individual user $i";
    $password = $user_name;
    $time_zone = 0; // Assign different ones!
    $email = $email = null; $user_name . '@foo.bar.baz.org';
    $group = rand(11, 12);

    Backend::create_user($user_name, $password, $real_name, '', $email, 0, $time_zone, $group, 1);
}
// That's why we need some more global groups with different viewing rights
// And 100000 users just viewing our progress
for ($i = 1; $i <= $maxindividualusers; $i++) {
    $user_name = "basic$i";
    $real_name = "Basic $i";
    $password = $user_name;
    $time_zone = 0; // Assign different ones!
    $email = $email = null; $user_name . '@foo.bar.baz.org';
    $group = 4;

    Backend::create_user($user_name, $password, $real_name, '', $email, 0, $time_zone, $group, 1);
}
// But that was not enough for all needed permission, so in practice, every
// project also has between 1 AND 20 project groups.
// 
// Must recreate, so rights for new projects get loaded. Otherwise,
// even first user in database can't create tasks.
$user = new User(1);

// And that's why we've got 1000000 tasks opened within the last 10 years
for ($i = 1; $i <= $maxtasks; $i++) {
    $project = rand(2, $maxproducts);
    // Find someone who is allowed to open a task, do not use global groups
    $sql = $db->Query("SELECT uig.user_id
                         FROM {users_in_groups} uig
                         JOIN {groups} g ON g.group_id = uig.group_id AND g.open_new_tasks = 1
                          AND (g.project_id = 0 OR g.project_id = ?)
                        WHERE g.group_id NOT IN (1, 2, 7, 8, 9)
                     ORDER BY $RANDOP LIMIT 1", array($project));
    $reporter = $db->FetchOne($sql);
    $sql = $db->Query("SELECT category_id FROM {list_category} WHERE project_id = ? AND category_name <> 'root' ORDER BY $RANDOP LIMIT 1",
        array($project));
    $category = $db->FetchOne($sql);
    $opened = time() -  rand(1, 315360000);
    $args = array();
    
    $args['project_id'] = $project;
    $args['date_opened'] = time() -  rand(1, 315360000);
    // 'last_edited_time' => time(),
    $args['opened_by'] = $reporter;
    $args['product_category'] = $category;
    $args['task_severity'] = 1;
    $args['task_priority'] = 1;
    // 'task_type', , 'product_version',
    // 'operating_system', , 'estimated_effort',
    // 'supertask_id',
    $sql = $db->Query("SELECT project_title FROM {projects} WHERE project_id = ?",
        array($project));
    $projectname = $db->FetchOne($sql);
    $subject = $subjects[rand(0, count($subjects) - 1)];
    $subject = sprintf($subject, $projectname);
    
    $args['item_summary'] = "Task $i ($subject)";
    // 'detailed_desc'
    // echo $args['item_summary'] . "\r\n";
    $ok = Backend::create_task($args);
    if ($ok === 0) {
        echo "Failed to create task.\r\n";
    }
    else {
        list($id, $token) = $ok;
        $db->Query('UPDATE {tasks} SET opened_by = ?, date_opened = ? WHERE task_id = ?',
                array($reporter, $opened, $id));
    }
    
}

// One task in ten of is unconfirmed, probably just bullshit, not assigned to anyone,
// and we add just a comment "Cannot reproduce".
 
for ($i = 1; $i <= $maxcomments; $i++) {
    $taskid = rand(2, $maxtasks + 1);
    $task = Flyspray::GetTaskDetails($taskid, true);
    $project = $task['project_id'];
    $added = time() -  rand(1, 315360000);
    // Find someone who is allowed to add comment, do not use global groups
    $sqltext = "SELECT uig.user_id
                         FROM {users_in_groups} uig
                         JOIN {groups} g ON g.group_id = uig.group_id AND g.add_comments = 1
                          AND (g.project_id = 0 OR g.project_id = ?)
                        WHERE g.group_id NOT IN (1, 2, 7, 8, 9)
                     ORDER BY $RANDOP ";
    $sql = $db->Query($sqltext, array($project));
    $row = $db->FetchRow($sql);
    $reporter = new User($row['user_id']);
   
    // User might still not be able to add a comment, if he can not see the task...
    // Just try again until a suitable one comes out from the query. It will finally.
    // Try to enhance the query also to return fewer unsuitable ones.
    while (!$reporter->can_view_task($task)) {
        $row = $db->FetchRow($sql);
        $reporter = new User($row['user_id']);
    }
   
    $comment = 'Comment.';
    Backend::add_comment($task, $comment);
    $sql = $db->Query('SELECT MAX(comment_id) FROM {comments}');
    $comment_id = $db->FetchOne($sql);
    $db->Query('UPDATE {comments} SET user_id = ?, date_added = ? WHERE comment_id = ?',
                array($reporter->id, $added, $comment_id));
}

// And 5000000 attachments total, either to task or comment

for ($i = 1; $i <= $maxattachments; $i++) {
    $sql = $db->Query("SELECT comment_id, task_id, user_id, date_added FROM {comments} ORDER BY $RANDOP LIMIT 1");
    list($comment_id, $task_id, $user_id, $date_added) = $db->FetchRow($sql);
    $fname = "Attachment $i";
    if (rand(1, 100) == 1) {
        $comment_id = 0;
    }
    $origname = GetAttachmentDescription() . " $i";
    $db->Query("INSERT INTO  {attachments}
                                     ( task_id, comment_id, file_name,
                                       file_type, file_size, orig_name,
                                       added_by, date_added)
                             VALUES  (?, ?, ?, ?, ?, ?, ?, ?)",
            array($task_id, $comment_id, $fname,
        'application/octet-stream', 1024,
        $origname,
        $user_id, $date_added));
}
function GetAttachmentDescription() {
    $type = rand(1, 100);
    if ($type > 80 && $type <= 100) {
        return 'Information that might help solve the problem';
    }
    elseif ($type == 79) {
        return 'Pic of my pet alligator';
    }
    elseif ($type == 78) {
        return 'Pic of my pet rhinoceros';
    }
    elseif ($type == 77) {
        return 'Pic of my pet elephant';
    }
    elseif ($type == 76 || $type == 75) {
        return 'Pic of my pet monkey';
    }
    elseif ($type == 74 || $type == 73) {
        return 'Pic of my undulate';
    }
    elseif ($type == 72 || $type == 71) {
        return 'Pic of my goldfish';
    }
    elseif ($type == 70 || $type == 69) {
        return 'Pic of my pet pig';
    }
    elseif ($type == 68 || $type == 67) {
        return 'Pic of my pet snake';
    }
    elseif ($type == 66 || $type == 65) {
        return 'Pic of my pet rat';
    }
    elseif ($type == 64 || $type == 63) {
        return 'Pic of my pet goat';
    }
    elseif ($type == 62 || $type == 61) {
        return 'Pic of my pet rabbit';
    }
    elseif ($type == 60 || $type == 59) {
        return 'Pic of my pet gerbil';
    }
    elseif ($type == 58 || $type == 57) {
        return 'Pic of my pet hamster';
    }
    elseif ($type == 56 || $type == 55) {
        return 'Pic of my pet chinchilla';
    }
    elseif ($type == 54 || $type == 53) {
        return 'Pic of my pet guinea pig';
    }
    elseif ($type == 52 || $type == 51) {
        return 'Pic of my pet turtle';
    }
    elseif ($type == 50 || $type == 49) {
        return 'Pic of my pet lizard';
    }
    elseif ($type == 48 || $type == 47) {
        return 'Pic of my pet frog';
    }
    elseif ($type == 46 || $type == 45) {
        return 'Pic of my pet tarantula';
    }
    elseif ($type == 44 || $type == 43) {
        return 'Pic of my pet hermit crab';
    }
    elseif ($type == 42 || $type == 41) {
        return 'Pic of my pet parrot';
    }
    elseif ($type >= 40 && $type < 25) {
        return 'Pic of my dog';
    }
    else {
        return 'Pic of my cat';
    }
}
// // But at least we have been able to solve approximately half of the tasks
// Of course, many of the tasks are somehow related to each other, so add
// parents, relationships, dependencies, duplicates etc. last.

// Do this ones last, after creating all the other data.
// Approximately 200 hundred of our projects are already closed or deleted.
// Cannot be sure when using random...

for ($i = 1; $i < 200; $i++) {
    
}

// Some of our developers AND project managers couldn't take all that AND have already left the premises
// No wonder, because we've got those corporate AND individual users always complaining
// AND whining, not to speak about our management.

$db->dbClose();

function add_project_data() {
    global $db;

    $sql = $db->Query('SELECT project_id FROM {projects} ORDER BY project_id DESC', false, 1);
    $pid = $db->fetchOne($sql);

    $cols = array('manage_project', 'view_tasks', 'open_new_tasks',
        'modify_own_tasks', 'modify_all_tasks', 'view_comments',
        'add_comments', 'edit_comments', 'delete_comments', 'show_as_assignees',
        'create_attachments', 'delete_attachments', 'view_history', 'add_votes',
        'close_own_tasks', 'close_other_tasks', 'assign_to_self', 'edit_own_comments',
        'assign_others_to_self', 'add_to_assignees', 'view_reports', 'group_open',
        'view_estimated_effort', 'view_current_effort_done', 'track_effort',
        'add_multiple_tasks', 'view_roadmap', 'view_own_tasks', 'view_groups_tasks',
        'edit_assignments');

    $args = array_fill(0, count($cols), '1');
    array_unshift($args, 'Project Managers', 'Permission to do anything related to this project.', intval($pid));
    $db->Query("INSERT INTO  {groups}
                                 ( group_name, group_desc, project_id,
                                   " . join(',', $cols) . ")
                         VALUES  ( " . $db->fill_placeholders($cols, 3) . ")", $args);

    // Add 1 project specific developer group too.
    $args = array_fill(1, count($cols) - 1, '1');
    array_unshift($args, 'Project Developers', 'Permission to do almost anything but not manage project.', intval($pid), 0);
    $db->Query("INSERT INTO  {groups}
                                 ( group_name, group_desc, project_id,
                                   " . join(',', $cols) . ")
                         VALUES  ( " . $db->fill_placeholders($cols, 3) . ")", $args);

    $db->Query("INSERT INTO  {list_category}
                                 ( project_id, category_name,
                                   show_in_list, category_owner, lft, rgt)
                         VALUES  ( ?, ?, 1, 0, 1, 4)", array($pid, 'root'));

    $db->Query("INSERT INTO  {list_category}
                                 ( project_id, category_name,
                                   show_in_list, category_owner, lft, rgt )
                         VALUES  ( ?, ?, 1, 0, 2, 3)", array($pid, 'Backend / Core'));

    // We develop software for a lot of operating systems.
    // Add your favorite ones to the end so we get more data.
    $os = 1;
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'All', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows 3.0', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows 3.1', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows 3.11', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows 95', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows ME', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows 2000', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows XP', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows Vista', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows 7', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows 8', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows 8.1', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows 10', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'OS X 10.0', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'OS X 10.1', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'OS X 10.2', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'OS X 10.3', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'OS X 10.4', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'OS X 10.5', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'OS X 10.6', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'OS X 10.7', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows Server 2003', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows Server 2003 R2', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows Server 2008', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows Server 2008 R2', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows Server 2012', $os++));
    $db->Query("INSERT INTO  {list_os}
                                 ( project_id, os_name, list_position, show_in_list )
                         VALUES  (?, ?, ?, 1)", array($pid, 'Windows Server 2012 R2', $os++));

    // We've got also a lot of versions.
    // Never go over 42! It's the universal answer to everything. So in that version,
    // every possible bug is finally solved AND every feature request implemented.
    $totalversions = rand(3, 42);
    $present = rand(1, $totalversions);
    for ($i = 1; $i <= $totalversions; $i++) {
        $tense = ($i == $present ? 2 : ($i < $present ? 1 : 3));
        $db->Query("INSERT INTO  {list_version}
                                 ( project_id, version_name, list_position,
                                   show_in_list, version_tense )
                         VALUES  (?, ?, ?, 1, ?)", array($pid, sprintf('%d.0', $i), $i, $tense));
    }
}

} // end function creatTestData
?>
