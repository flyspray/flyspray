<?php

/*
* For Flyspray development tests only!
*
* Script for simulating Flyspray with many testdata like many projets, hundreds of users, thousands of tasks and thousands comments ..
* 
* TODO:
# replace some hardcoded IDs by getting the appropriate ids from db instead (autoincrement values of user groups for example)
# add some supertask_id to some tasks (use flyspray functions/api that should check if legal - no loops of any hop depth, permissions, cross project, existing task)
# add some related task relations
# add some dependencies to some tasks (flyspray should check legal - watch for logic deadlocks)
# add some 'is duplicate of' to some tasks (only when closing)
# Change: (simulate more daily managing work with tasks)
# * status of some tasks
# * progress of some tasks
# * category of some tasks
# * reported version of some tasks
# * milestone version of some tasks
# close some tasks with resolution status and text
# assign dev users to tasks
# add 'notify me' to some tasks
# add reminders to some tasks
# add user votes to some tasks
# add some close requests by some users
# deactivate and delete some users
# add some users 'lost password' requests
# add some failed user login attempts
# add/change some user avatar gif/png/jpg uploads
# add some real attachments with uploaded files of different type (generated .docx,.odt,.tex,.ps,.pdf,.txt,.xml,.sql)
# change some task descriptions:
# * add some kyrilic, greek, arab, chinese, emoji task description and task summaries
# * add some code segments for GeShi - some languages are harder to parse than others!
# tags:
# * remove some tags from tasks

* Maybe someday parts of it are moved to phpunit testing in future, so can automatic tested by travis-ci.
*
*/

# Only use this script after a fresh Flyspray install (1 project id=1 , 1 user with id=1, 1 task with id=1 existing)
# choose dokuwiki during setup
#createTestData();

# temp: just wrapped that code in a function so it is not run by phpunit
function createTestData()
{
	# quick safety
	exit;

	if (PHP_SAPI !== 'cli') {
		die('Please call it only from commandline');
	}

	global $db, $fs, $conf, $proj, $user, $notify, $language;

	# Use this only on a new test installation, code does not work on
	# an existing one, and never will.


 ### Simulation Settings ###
	# Set conservative data as default, setup bigger values for further performance tests.
	# maybe setting moved out of this function to make performance graphs with multiple runs..

	$years=10;
	$timerange=3600*24*365*$years;
	
	$maxprojects = 3;
	# ca 100 tasks/sec, 100 comments/sec on an old laptop with Flyspray 1.0-rc7 with mysqli setup in a virtual machine as thumb rule
	$maxtasks = 1000; # absolute number, e.g. 1000
	$maxcomments = 1000; # absolute number, e.g. 10000
	$maxattachments = 500; # only emulated yet, e.g. 500
	$maxversions = 3; # per project, e.g. 5
	$maxcorporates = 3; # mmhh
	
	# spread some user with different permissions
	$maxcorporateusers = 20; # absolute number, e.g. 20
	$maxadmins = 2;
	$maxmanagers = 2;
	$maxdevelopers = 500; # a bit higher for due they have more rights, can have more relations with tasks
	$maxindividualusers = 50;
	$maxviewers = 50;

  ### End of Simulation Settings ###

	$subjects = array(
		'get some fresh air dude',
		'Cool idea I have!',
		'bad weather :-/ change it',
		'buy present for ...',
		'write release notes',
		'weird funny bug in the matrix',
		'organize conference',
		'send invitations'
        );
	
	error_reporting(E_ALL);

	define('IN_FS', 1);

	$now=microtime(true); # simple performance times
	$start=$now;
	require_once dirname(__FILE__) . '/../includes/fix.inc.php';
	require_once dirname(__FILE__) . '/../includes/class.flyspray.php';
	require_once dirname(__FILE__) . '/../includes/constants.inc.php';
	require_once dirname(__FILE__) . '/../includes/i18n.inc.php';
	require_once dirname(__FILE__) . '/../includes/class.tpl.php';
	require_once dirname(__FILE__) . '/../vendor/autoload.php';

	$conf = parse_ini_file('../flyspray.conf.php', true) or die('Cannot open config file.');

	# faster generate new users with very weak, but fast password hash generation, only for this test.
	$conf['general']['passwdcrypt']='md5';

	error_reporting(E_ALL); # overwrite settings in fix.inc.php

	$db = new Database;
	$db->dbOpenFast($conf['database']);
	$RANDOP = 'RAND()';
	if ($db->dblink->dataProvider == 'postgres') {
		$RANDOP = 'RANDOM()';
	}
	$last=$now;$now=microtime(true);echo round($now-$last,6)." s database connection\n";


	$fs = new Flyspray();
	$user = new User(1);
	$proj = new Project(1);
	$notify = new Notifications;
	load_translations();

	$last=$now; $now=microtime(true); echo round($now-$last,6)." s Flyspray objects\n";

	for ($i = 1; $i <= $maxadmins; $i++) {
		$user_name = "admin$i";
		$real_name = "Admin $i";
		$password = $user_name;
		$time_zone = 0; // Assign different one!
		$email = null; // $user_name . '@example.com';

		Backend::create_user($user_name, $password, $real_name, '', $email, 0, $time_zone, 1, 1);
	}
	$last=$now; $now=microtime(true); echo round($now-$last,6).': '.$maxadmins." admins created\n";

	for ($i = 1; $i <= $maxmanagers; $i++) {
		$user_name = "pm$i";
		$real_name = "Project Manager $i";
		$password = $user_name;
		$time_zone = 0; // Assign different one!
		$email = null; // $user_name . '@example.com';

		Backend::create_user($user_name, $password, $real_name, '', $email, 0, $time_zone, 2, 1);
	}
	$last=$now; $now=microtime(true); echo round($now-$last,6).': '.$maxmanagers." managers created\n";

	$db->query('UPDATE {projects} SET project_is_active = 0 WHERE project_id = 1');
	// Show more columns by default, trying to make database or flyspray crash under stress.
	$db->query("UPDATE {prefs} SET pref_value = 'id project category tasktype severity summary status openedby dateopened progress comments attachments votes' WHERE pref_name = 'visible_columns'");

	// Add 3 different global developer groups with different
	// view rights first, then assign developers to them at random.

	$db->query("INSERT INTO {groups} "
        . "(group_name,group_desc,project_id,group_open,view_comments,manage_project,view_tasks, view_groups_tasks, view_own_tasks,open_new_tasks,modify_own_tasks) "
        . "VALUES('Developer Group 1', 'Developer Group 1', 0, 1, 1, 0, 1, 1, 1, 1, 1)");

	$db->query("INSERT INTO {groups} "
        . "(group_name,group_desc,project_id,group_open,view_comments,manage_project,view_tasks, view_groups_tasks, view_own_tasks,open_new_tasks,modify_own_tasks) "
        . "VALUES('Developer Group 2', 'Developer Group 2', 0, 1, 1, 0, 0, 1, 1, 1, 1)");

	$db->query("INSERT INTO {groups} "
        . "(group_name,group_desc,project_id,group_open,view_comments,manage_project,view_tasks, view_groups_tasks, view_own_tasks,open_new_tasks,modify_own_tasks) "
        . "VALUES('Developer Group 3', 'Developer Group 3', 0, 1, 1, 0, 0, 0, 1, 1, 1)");

	$last=$now;$now=microtime(true);echo round($now-$last,6).': '."3 global dev groups created\n";


	// Add also general groups for corporate users, individual users and viewers.
	// Allow only login. Not so relaxed with them bastards.
	$db->query("INSERT INTO {groups} "
	        . "(group_name,group_desc,project_id,group_open) "
	        . "VALUES('Corporate Users', 'Corporate Users', 0, 1)");

	$db->query("INSERT INTO {groups} "
        . "(group_name,group_desc,project_id,group_open) "
        . "VALUES('Trusted Users', 'Trusted Users', 0, 1)");

	$db->query("INSERT INTO {groups} "
        . "(group_name,group_desc,project_id,group_open) "
        . "VALUES('Non-trusted Users', 'Non-trusted Users', 0, 1)");
	$last=$now; $now=microtime(true); echo round($now-$last,6).': '."3 global user groups created\n";


	for ($i = 1; $i <= $maxdevelopers; $i++) {
		$user_name = "dev$i";
		$real_name = "Developer $i";
		$password = $user_name;
		$time_zone = 0;
		$email = null; // $user_name . '@example.com';
		$group = rand(7, 9);

		if($i==1){
			$registered = time() - rand($timerange-3600, $timerange);
		}else{
			$registered = $prevuserreg + rand(0, 0.9*2*$timerange/$maxdevelopers); # 0.9 to be sure not in future
                }
		Backend::create_user($user_name, $password, $real_name, '', $email, 0, $time_zone, $group, 1);
		# a bit	weired,	but simple UPDATE {users} SET register_date = ? WHERE user_id = (SELECT MAX(user_id) FROM {users}) doesn't work	for mysql
		$db->query('UPDATE {users} SET register_date = ? WHERE user_id = (SELECT user_id FROM (SELECT * FROM {users}) AS tempusers ORDER BY user_id DESC LIMIT 1)',
                        array($registered) );
		$prevuserreg=$registered;
	}
	$last=$now;$now=microtime(true);echo round($now-$last,6).': '.$maxdevelopers." dev users created\n";

	$tags=array(
                array('name'=>'blue', 'color'=>'#00c'),
                array('name'=>'red', 'color'=>'#c00'),
                array('name'=>'green', 'color'=>'#090'),
                array('name'=>'rosa', 'color'=>'#f9f'),
                array('name'=>'lila', 'color'=>'#c0c'),
                array('name'=>'black', 'color'=>'#000'),
                array('name'=>'brown', 'color'=>'#c90'),
                array('name'=>'darkred', 'color'=>'#600'),
                array('name'=>'darkblue', 'color'=>'#006')
        );
	
	// add some projects and some tag definitions
	$tgcounter=0;
	$project_id=0;
	for ($i = 1; $i <= $maxprojects; $i++) {
		$projname = 'Product '.($i+1);

		$db->query('
			INSERT INTO {projects} (
				project_title, theme_style, intro_message,
				others_view, anon_open, project_is_active,
				visible_columns,
				visible_fields,
				lang_code, notify_email, notify_jabber,
				disp_intro, default_task, notify_reply,
				feed_description
			)
			VALUES (
				?, ?, ?,
				?, ?, 1,
				?,
				?,
				?,
				?, ?, ?,
				?, ?, ?
			)',
			array(
				$projname, 'CleanFS', "Welcome to $projname",
				0, 0,
				'id category tasktype severity summary status openedby dateopened progress comments attachments votes',
				'supertask tasktype category severity priority status private assignedto reportedin dueversion duedate progress os votes',
				'en', '', '',
				1, '', '',
				'')
		);
		$project_id=$db->insert_Id();
		add_project_data($project_id);

		for ($t=0; $t<count($tags); $t++){
			$db->query("INSERT INTO {list_tag} (project_id, tag_name, show_in_list, class) VALUES (?, ?, ?, ?)",
				array($project_id, $tags[$t]['name'].($i+1), rand(0,1), $tags[$t]['color'])
			);
			$tgcounter++;
		}
	}

	$last=$now;$now=microtime(true);echo round($now-$last,6).': '.$maxprojects.' projects created, '.$tgcounter." tags created\n";

	// Assign some developers to project manager or project developer groups
	for ($i = 1; $i <= $maxprojects; $i++) {
		$projid = $i + 1;
		$sql = $db->query('SELECT group_id FROM {groups} WHERE project_id = ? AND manage_project = 1', array($projid));
		$pmgroup = $db->fetchOne($sql);
		$sql = $db->query('SELECT group_id FROM {groups} WHERE project_id = ? AND manage_project = 0', array($projid));
		$pdgroup = $db->fetchOne($sql);

		$pmlimit = intval($maxdevelopers / 100) + rand(-2, 2);
		$pdlimit = intval($maxdevelopers / 20) + rand(-10, 10);
		$pmlimit = $pmlimit < 1 ? 1 : $pmlimit;
		$pdlimit = $pdlimit < 1 ? 1 : $pdlimit;

		$sql = $db->query("SELECT user_id FROM {users_in_groups} WHERE group_id in (7, 8, 9) ORDER BY $RANDOP limit $pmlimit");
		$pms = $db->fetchCol($sql);
		$sql = $db->query("SELECT user_id FROM {users_in_groups} WHERE group_id in (8, 9) ORDER BY $RANDOP limit $pdlimit");
		$pds = $db->fetchCol($sql);

		foreach ($pms as $pm) {
			$db->query('INSERT INTO {users_in_groups} (user_id, group_id) values (?, ?)', array($pm, $pmgroup));
		}

		foreach ($pds as $pd) {
			$check = $db->query('SELECT * FROM {users_in_groups} WHERE user_id = ? AND group_id = ?', array($pd, $pmgroup));
			if (!$db->countRows($check)) {
				$db->query('INSERT INTO {users_in_groups} (user_id, group_id) values (?, ?)', array($pd, $pdgroup));
			}
		}
	}
	$last=$now;$now=microtime(true);echo round($now-$last,6).': '.$maxprojects." projects assigned some developers to project groups\n";

	for ($i = 1; $i <= $maxcorporateusers; $i++) {
	    $user_name = "cu$i";
	    $real_name = "Corporate user $i";
	    $password = $user_name;
	    $time_zone = 0; // Assign different ones!
	    $email = null; // $user_name . '@example.com';
	    $group = 10;

	    Backend::create_user($user_name, $password, $real_name, '', $email, 0, $time_zone, $group, 1);
	}
	$last=$now;$now=microtime(true);echo round($now-$last,6).': '.$maxcorporateusers." corp users created\n";

	// Now, create corporate user groups for some of our projects.
	// Just %5 change of getting added.
	for ($i = 1; $i <= $maxcorporates; $i++) {
		for ($j = 1; $j <= $maxprojects; $j++) {
			if (rand(1, 20) == 1) {
				$projid = $j + 1;
				$db->query("INSERT INTO {groups} "
	                    . "(group_name,group_desc,project_id,manage_project,view_tasks, view_groups_tasks, view_own_tasks,open_new_tasks,add_comments,create_attachments,group_open,view_comments) "
	                    . "VALUES('Corporate $i', 'Corporate $i Users', $projid, 0, 0, 1, 1, 1, 1, 1,1,1)");
				$group_id = $db->insert_ID();
				for ($k = $i; $k <= $maxcorporateusers; $k += $maxcorporates) {
					$username = "cu$k";
					$sql = $db->query('SELECT user_id FROM {users} WHERE user_name = ?', array($username));
					$user_id = $db->fetchOne($sql);
					$db->query('INSERT INTO {users_in_groups} (user_id, group_id) VALUES (?, ?)', array($user_id, $group_id));
				}
			}
		}
	}
	$last=$now;$now=microtime(true);echo round($now-$last,6).': '.$maxcorporates." corporate usergroups created\n";

	// And also those individual users...
	for ($i = 1; $i <= $maxindividualusers; $i++) {
		$user_name = "iu$i";
		$real_name = "Individual user $i";
		$password = $user_name;
		$time_zone = 0; // Assign different ones!
		$email = null; // $user_name . '@example.com';
		$group = rand(11, 12);

		Backend::create_user($user_name, $password, $real_name, '', $email, 0, $time_zone, $group, 1);
	}
	$last=$now;$now=microtime(true);echo round($now-$last,6).': '.$maxindividualusers." indi users created\n";


	// That's why we need some more global groups with different viewing rights
	for ($i = 1; $i <= $maxindividualusers; $i++) {
		$user_name = "basic$i";
		$real_name = "Basic $i";
		$password = $user_name;
		$time_zone = 0; // Assign different ones!
		$email = null; // $user_name . '@example.com';
		$group = 4;

		Backend::create_user($user_name, $password, $real_name, '', $email, 0, $time_zone, $group, 1);
	}
	$last=$now;$now=microtime(true);echo round($now-$last,6).': '.$maxindividualusers." basic users created\n";


	// Must recreate, so rights for new projects get loaded. Otherwise,
	// even first user in database can't create tasks.
	$user = new User(1);

	# for generating pseudo task description
	$vocals=array('e','e','a','i','o','u'); # e most freq vocal in western languages
	$conso=array('s','sch','st','z','r','l','b','p','g','k','m','n','v','w','d','t','qu');
	$wordlen=array(3,12);
	$clauselen=array(3,8); # 3: words per sub clause
	$commas=array(0,2); # 1: commas per sentence
	$paralen=array(1,5); # sentences per paragraph
	$parts=array(1,10); # parts per task description (paragraphs or lists or code..)
	$codes=array('','php','xml','sql','html');
	
	echo "Creating $maxtasks tasks: ";
	if ($conf['database']['dbtype'] == 'mysql' or $conf['database']['dbtype'] == 'mysqli') {
		$sqlid=$db->query("SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?",
			array($conf['database']['dbname'], $conf['database']['dbprefix'].'tasks')
		);
		$firsttaskid = $db->fetchOne($sqlid);
		$prevtaskopened = $firsttaskid - 1;
	} else {
		# TODO similiar for Postgresql and other (PDO?)
		$prevtaskopened = 1; 
		$firsttaskid = $prevtaskopened + 1;
	}
	$finaltaskid=$maxtasks + $prevtaskopened;

	for ($i = $firsttaskid; $i <= $finaltaskid; $i++) {
		$project_id = rand(2, $maxprojects + 1); # project id 1 is default project which we exclude.
		if ($i === $firsttaskid) {
			$opened = time() - rand($timerange-(30*24*3600), $timerange);
		} else {
			$opened = $prevtaskopened + rand(0, 0.9*2*$timerange/$maxtasks); # 0.9 to be sure not in future
		}

		// Find someone who is allowed to open a task, do not use global groups
		$sql = $db->query("SELECT u.user_id
			FROM {users} u
			JOIN {users_in_groups} uig ON u.user_id=uig.user_id
			JOIN {groups} g ON g.group_id = uig.group_id AND g.open_new_tasks = 1 AND (g.project_id = 0 OR g.project_id = ?)
			WHERE g.group_id NOT IN (1)
			AND u.register_date < ? 
			ORDER BY $RANDOP LIMIT 1", array($project_id, $opened)
		);
		$reporter = $db->fetchOne($sql);
		$sql = $db->query("SELECT category_id FROM {list_category}
			WHERE project_id = ?
			AND category_name <> 'root'
			ORDER BY $RANDOP LIMIT 1",
		array($project_id));
		$category = $db->fetchOne($sql);
		$args = array();

		$args['project_id'] = $project_id;
		$args['date_opened'] = $opened;
		// 'last_edited_time' => time(),
		$args['opened_by'] = $reporter;
		$args['product_category'] = $category;
		$args['task_severity'] = rand(1,5); # 5 fixed severities
		$args['task_priority'] = rand(1,6); # 6 fixed priorities
		$args['task_type'] = rand(1,2); # 2 global task types after install
		$args['item_status'] = rand(1,6); # 6 global task status after install
		$args['item_status'] = rand(1,6); # 6 global task status after install
		# assigned or researching status
		if ($args['item_status']==3 or $args['item_status']==4) {
			$args['percent_complete'] = rand(0,8)*10;
		}
		# waiting or testing status
		if ($args['item_status']==5 or $args['item_status']==6) {
			$args['percent_complete'] = rand(4,9)*10;
		}
		
		/**
		 * @todo 'product_version'
		 * @todo 'operating_system'
		 * @todo 'estimated_effort'
		 * @todo 'supertask_id' - find existing task of project
		 */

		$sql = $db->query("SELECT project_title FROM {projects} WHERE project_id = ?",
		array($project_id));
		$projectname = $db->fetchOne($sql);
		$subject = $subjects[rand(0, count($subjects) - 1)];
		$subject = sprintf($subject, $projectname);

		$args['item_summary'] = "$subject (task $i)";

		$dparts=rand($parts[0], $parts[1]);
		$descr='';
		for ($p=0; $p<$dparts; $p++) {
			$para='';
			$type=rand(0,2); # text, list, code
			if ($type==0) {
				$dsent=rand($paralen[0], $paralen[1]);
				for ($s=0; $s<$dsent; $s++) {
					$sent='';
					$dcommas=rand($commas[0], $commas[1]);
					for ($c=0; $c<$dcommas; $c++) {
						$clausepart='';
						$dwords=rand($clauselen[0], $clauselen[1]);
						for ($w=0; $w<$dwords; $w++) {
							$v=rand(0,1);
							$wd='';
							$dletters=rand($wordlen[0], $wordlen[1]);
							for ($l=0; $l<$dletters; $l++) {
								$wd.= ($v % 2) ? $vocals[rand(0, count($vocals)-1)] : $conso[rand(0, count($conso)-1)];
								$v++;
							}
							if (rand(0,100) < 1) {
								$wd='FS#'.rand(2,$i);
							}
							if (rand(0,100) < 2) {
								$wd = ($conf['general']['syntax_plugin'] === 'html') ? '<em>'.$wd.'</em>' : '//'.$wd.'//';
							}
							if (rand(0,100) < 2) {
								$wd = ($conf['general']['syntax_plugin'] === 'html') ? '<strong>'.$wd.'</strong>' : '**'.$wd.'**';
							}
							# underline without meaning/semantic IMHO I consider unwanted, but our dokuwiki plugin has it enabled.. 
							if ($conf['general']['syntax_plugin'] === 'dokuwiki') {
								if (rand(0,100) < 2) {
									$wd='__'.$wd.'__';
								}
							}
							$clausepart.=$wd.' ';	
						}
						$sent.=$clausepart;
						$sent.=($c+1 < $dcommas) ? ', ': '.';
					}
					if (rand(0, 5) < 1) {
						$para.=' random mention of @dev'.rand(1, $maxdevelopers).' ';
					}
				}
				if ($conf['general']['syntax_plugin'] === 'html') {
					$para='<p>'.$para.'</p>';
				}
			} elseif ($type==1) {
				if ($conf['general']['syntax_plugin'] === 'html') {
					$para.='<ul><li>listitem</li><li>listitem2</li><li>listitem3';
					if (rand(0, 5) < 1) {
						$para.=' random mention of @dev'.rand(1, $maxdevelopers).' ';
					}
					$para.='</li></ul>';
				} else {
					# dokuwiki list
					$para.="  * listitem\n  * listitem\n  * listitem3";
					if (rand(0, 5) < 1) {
						$para.=' random mention of @dev'.rand(1, $maxdevelopers).' ';
					}
				}
			} elseif ($type==2) {
				if ($conf['general']['syntax_plugin'] === 'html') {
					$para.='<pre><code class="language-'.$codes[rand(0, count($codes)-1)].'"> some signs&lt;&lt;y&lt;&gt;&gt;&gt;&gt; """</code></pre>';
				} else {
					# dokuwiki code
					$para.='<code '.$codes[rand(0, count($codes)-1)].'> some signs<<y<>>> """</code>';
				}
			}
			$descr.=$para."\n\n";
		}
		$args['detailed_desc'] = $descr;

		$ok = Backend::create_task($args);
		if ($ok === 0) {
			echo "Failed to create task.\n";
		} else {
			list($task_id, $token) = $ok;
			$db->query('UPDATE {tasks} SET opened_by = ?, date_opened = ? WHERE task_id = ?',
			array($reporter, $opened, $task_id));

			$limit=rand(0,3);
			$db->query("INSERT INTO {task_tag} (task_id, tag_id) 
				SELECT $task_id, tag_id FROM {list_tag}
				WHERE project_id=?
				ORDER BY $RANDOP
				LIMIT $limit",
				array($project_id)
			);
		}
		$prevtaskopened=$opened;
		if ($i%500 == 0) {
                        echo $i.' mem:'.memory_get_usage()."\n";
                }
	} # end for maxtasks

	$last=$now; $now=microtime(true); echo round($now-$last, 6).': '.$maxtasks." tasks created\n";

	echo "Creating $maxcomments comments: \n";
        $maxtask=$task_id;
	for ($i = 1; $i <= $maxcomments; $i++) {
		$taskid = rand(2, $maxtask);
		$task = Flyspray::getTaskDetails($taskid);
		$project_id = $task['project_id'];
		# XXX only allow comments after task created date and also later as existing comments in that task.
		$added = time() - rand(1, $timerange);

		// Find someone who is allowed to add comment, do not use global groups
		$sqltext = "SELECT uig.user_id
			FROM {users_in_groups} uig
                         JOIN {groups} g ON g.group_id = uig.group_id AND g.add_comments = 1
                          AND (g.project_id = 0 OR g.project_id = ?)
                        WHERE g.group_id NOT IN (1, 2, 7, 8, 9)
                     ORDER BY $RANDOP ";
		$sql = $db->query($sqltext, array($project_id));
		$row = $db->fetchRow($sql);
		$reporter = new User($row['user_id']);

		// User might still not be able to add a comment, if he can not see the task...
		// Just try again until a suitable one comes out from the query. It will finally.
		// Try to enhance the query also to return fewer unsuitable ones.
		while (!$reporter->can_view_task($task)) {
			$row = $db->fetchRow($sql);
			$reporter = new User($row['user_id']);
		}

		$comment = 'Comment.';
		$comment_id=Backend::add_comment($task, $comment);
		$db->query('UPDATE {comments} SET user_id = ?, date_added = ? WHERE comment_id = ?',
			array($reporter->id, $added, $comment_id));
		
		if ($i%500 == 0){
                        echo $i.' mem:'.memory_get_usage()."\n";
                }
	} # end for maxcomments
	$last=$now; $now=microtime(true); echo round($now-$last,6).': '.$maxcomments." comments created\n";


	// And $maxattachments total, either to task or comment
	for ($i = 1; $i <= $maxattachments; $i++) {
		$sql = $db->query("SELECT comment_id, task_id, user_id, date_added FROM {comments} ORDER BY $RANDOP LIMIT 1");
		list($comment_id, $task_id, $user_id, $date_added) = $db->fetchRow($sql);
		$fname = "Attachment $i";
		if (rand(1, 100) == 1) {
			$comment_id = 0;
		}

		$origname = getAttachmentDescription() . " $i";
		$db->query("INSERT INTO {attachments}
			( task_id, comment_id, file_name,
				file_type, file_size, orig_name,
				added_by, date_added)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
			array($task_id, $comment_id, $fname,
			'application/octet-stream', 1024,
			$origname,
			$user_id, $date_added));
	}
	$last=$now; $now=microtime(true); echo round($now-$last,6).': '.$maxattachments." pseudo attachments created\n";
	echo "\nTestdata filled in ".round($now-$start,1)." s.\n\n";
	$db->dbClose();
} // end function createTestData


function getAttachmentDescription()
{
    $type = rand(1, 100);

    if ($type > 80 && $type <= 100) {
        return 'Information that might help solve the problem';
    } elseif ($type == 79) {
        return 'Pic of my pet alligator';
    } elseif ($type == 78) {
        return 'Pic of my pet rhinoceros';
    } elseif ($type == 77) {
        return 'Pic of my pet elephant';
    } elseif ($type == 76 || $type == 75) {
        return 'Pic of my pet monkey';
    } elseif ($type == 74 || $type == 73) {
        return 'Pic of my undulate';
    } elseif ($type == 72 || $type == 71) {
        return 'Pic of my goldfish';
    } elseif ($type == 70 || $type == 69) {
        return 'Pic of my pet pig';
    } elseif ($type == 68 || $type == 67) {
        return 'Pic of my pet snake';
    } elseif ($type == 66 || $type == 65) {
        return 'Pic of my pet rat';
    } elseif ($type == 64 || $type == 63) {
        return 'Pic of my pet goat';
    } elseif ($type == 62 || $type == 61) {
        return 'Pic of my pet rabbit';
    } elseif ($type == 60 || $type == 59) {
        return 'Pic of my pet gerbil';
    } elseif ($type == 58 || $type == 57) {
        return 'Pic of my pet hamster';
    } elseif ($type == 56 || $type == 55) {
        return 'Pic of my pet chinchilla';
    } elseif ($type == 54 || $type == 53) {
        return 'Pic of my pet guinea pig';
    } elseif ($type == 52 || $type == 51) {
        return 'Pic of my pet turtle';
    } elseif ($type == 50 || $type == 49) {
        return 'Pic of my pet lizard';
    } elseif ($type == 48 || $type == 47) {
        return 'Pic of my pet frog';
    } elseif ($type == 46 || $type == 45) {
        return 'Pic of my pet tarantula';
    } elseif ($type == 44 || $type == 43) {
        return 'Pic of my pet hermit crab';
    } elseif ($type == 42 || $type == 41) {
        return 'Pic of my pet parrot';
    } elseif ($type >= 40 && $type < 25) {
        return 'Pic of my dog';
    } else {
        return 'Pic of my cat';
    }
}

function add_project_data($pid = 0)
{
	global $db;

	if (!$pid>0) {
		$sql = $db->query('SELECT project_id FROM {projects} ORDER BY project_id DESC', false, 1);
		$pid = $db->fetchOne($sql);
	}

	$cols = array(
		'manage_project',
		'view_tasks',
		'open_new_tasks',
		'modify_own_tasks',
		'modify_all_tasks',
		'view_comments',
		'add_comments',
		'edit_comments',
		'delete_comments',
		'show_as_assignees',
		'create_attachments',
		'delete_attachments',
		'view_history',
		'add_votes',
		'close_own_tasks',
		'close_other_tasks',
		'assign_to_self',
		'edit_own_comments',
		'assign_others_to_self',
		'add_to_assignees',
		'view_reports',
		'group_open',
		'view_estimated_effort',
		'view_current_effort_done',
		'track_effort',
		'add_multiple_tasks',
		'view_roadmap',
		'view_own_tasks',
		'view_groups_tasks',
		'edit_assignments'
	);

	$args = array_fill(0, count($cols), '1');
	array_unshift($args, 'Project Managers', 'Permission to do anything related to this project.', intval($pid));
	$db->query("INSERT INTO {groups}
		( group_name, group_desc, project_id,
		" . join(',', $cols) . ")
		VALUES ( " . $db->fill_placeholders($cols, 3) . ")", $args);

	// Add 1 project specific developer group too.
	$args = array_fill(1, count($cols) - 1, '1');
	array_unshift($args, 'Project Developers', 'Permission to do almost anything but not manage project.', intval($pid), 0);
	$db->query("INSERT INTO {groups}
		( group_name, group_desc, project_id,
		" . join(',', $cols) . ")
		VALUES ( " . $db->fill_placeholders($cols, 3) . ")", $args);

	$db->query("INSERT INTO {list_category}
		( project_id, category_name,
		show_in_list, category_owner, lft, rgt)
		VALUES ( ?, ?, 1, 0, 1, 4)", array($pid, 'root'));

	$db->query("INSERT INTO {list_category}
		( project_id, category_name,
		show_in_list, category_owner, lft, rgt )
		VALUES ( ?, ?, 1, 0, 2, 3)", array($pid, 'Backend / Core'));

	$os = 1;
	$db->query("INSERT INTO {list_os}
		( project_id, os_name, list_position, show_in_list )
		VALUES  (?, ?, ?, 1)", array($pid, 'All', $os++));

	$totalversions = rand(3, 10);
	$present = rand(1, $totalversions);
	for ($i = 1; $i <= $totalversions; $i++) {
		$tense = ($i == $present ? 2 : ($i < $present ? 1 : 3));
		$db->query("INSERT INTO {list_version}
			( project_id, version_name, list_position, show_in_list, version_tense )
			VALUES (?, ?, ?, 1, ?)",
			array($pid, sprintf('%d.0', $i), $i, $tense)
		);
	}
} # end function add_project_data
