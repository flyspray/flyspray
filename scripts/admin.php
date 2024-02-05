<?php

  /***********************************************\
  | Administrator's Toolbox                       |
  | ~~~~~~~~~~~~~~~~~~~~~~~~                      |
  | This script allows members of a global Admin  |
  | group to modify the global preferences, user  |
  | profiles, global lists, global groups, pretty |
  | much everything global.                       |
  \***********************************************/

if (!defined('IN_FS')) {
	die('Do not access this file directly.');
}

if (!$user->perms('is_admin')) {
	Flyspray::show_error(4);
}

$proj = new Project(0);

$page->pushTpl('admin.menu.tpl');

/**
 * @param string $colname user column, use the string used also for translations, so use 'emailaddress' instead of user db field 'email_address'.
 */
function tpl_userlistheading($colname)
{
	$coltitle = eL($colname);
	if (isset($_GET['order']) && $_GET['order'] === $colname) {
		if (isset($_GET['sort']) && $_GET['sort'] == 'desc') {
			$sort1 = 'asc';
			$coltitle .=' <i class="fa fa-sort-desc"></i>';
		} else {
			$sort1 = 'desc';
			$coltitle .=' <i class="fa fa-sort-asc"></i>';
		}
	} else {
		if (isset($_GET['sort']) && $_GET['sort'] == 'desc') {
			$sort1 = 'desc';
		} else {
			$sort1 = 'asc';
		}
	}

	$new_order = array('order' => $colname, 'sort' => $sort1);
	# unneeded or duplicate params from $_GET for the sort links
	$params=array_merge($_GET, $new_order);
	unset($params['do']);
	unset($params['area']);
	# resorting a search result should show always the first results
	unset($params['pagenum']);

	$html = sprintf('<a title="%s" href="%s">%s</a>',
		eL('sortthiscolumn'), Filters::noXSS(createURL('admin', 'editallusers', null, $params)), $coltitle);

	return $html;
}

switch ($area = Req::val('area', 'prefs')) {
	case 'users':
		$id = Flyspray::usernameToId(Req::val('user_name'));
		if (!$id) {
			$id = is_numeric(Req::val('user_id')) ? Req::val('user_id') : 0;
		}
		$theuser = new User($id, $proj);
		if ($theuser->isAnon()) {
			Flyspray::show_error(5, true, null, $_SESSION['prev_page']);
		}
		$page->assign('theuser', $theuser);

	case 'editgroup':
		// looks a bit dumb, maybe replace that big switch-case fallthrough construct
		if (Req::val('area') == 'editgroup') {
			$group_details = Flyspray::getGroupDetails(Req::num('id'));
			if (!$group_details || $group_details['project_id'] != $proj->id) {
				Flyspray::show_error(L('groupnotexist'));
				Flyspray::redirect(createURL('pm', 'groups', $proj->id));
			}
			$page->uses('group_details');
		}

	case 'editallusers':
		// looks a bit dumb, maybe replace that big switch-case fallthrough construct
		if ($area == 'editallusers') {
			$perpage = 250; # take care of the PHP max_input_vars / (your form vars per user row in html output)
			$pagenum = Get::num('pagenum', 1);
			if ($pagenum < 1) {
				$pagenum = 1;
			}
			$offset = $perpage * ($pagenum - 1);

			$showstats = (isset($_GET['showfields']) && in_array('stats', $_GET['showfields'])) ? 1 : 0;
			$showltf = (isset($_GET['showfields']) && in_array('ltf', $_GET['showfields'])) ? 1 : 0;

			$listopts['perpage'] = $perpage;
			$listopts['pagenum'] = $pagenum;
			$listopts['offset'] = $offset;
			if ($showstats) {
				$listopts['stats']=1;
			}

			if (isset($_GET['status']) && $_GET['status']==='1') {
				$listopts['status']=1;
			} elseif (isset($_GET['status']) && $_GET['status']==='0') {
				$listopts['status']=0;
			}
			
			if (isset($_GET['namesearch']) && is_string($_GET['namesearch']) && $_GET['namesearch'] != '') {
				$listopts['namesearch'] = '%'.$_GET['namesearch'].'%';
				$namesearch=$_GET['namesearch'];
			} else { 
				$namesearch=false;
			}
			
			if (isset($_GET['mailsearch']) && is_string($_GET['mailsearch']) && $_GET['mailsearch'] != '') {
				$listopts['mailsearch'] = '%'.$_GET['mailsearch'].'%';
				$mailsearch=$_GET['mailsearch'];
			} else {
				$mailsearch=false;
			}

			if (isset($_GET['order']) && is_string($_GET['order']) && $_GET['order'] != '') {
				$sortings = array('realname', 'username', 'emailaddress', 'jabberid', 'regdate', 'lastlogin');
				if (in_array($_GET['order'], $sortings)) {
					$listopts['order'] = $_GET['order'];
				}
			}

			if (isset($_GET['sort']) && is_string($_GET['sort']) && $_GET['sort'] == 'desc') {
				$listopts['sort'] = 'desc';
			}

			$users = Flyspray::listUsers($listopts);
			$page->assign('users', $users['users']);
			$page->assign('usercount', $users['count']);
			$page->uses('showstats', 'showltf', 'perpage', 'pagenum', 'offset', 'namesearch', 'mailsearch');
		}
		
	case 'cat':
	case 'groups':
	case 'newuser':
	case 'newuserbulk':
		$page->assign('groups', Flyspray::listGroups());
	case 'userrequest':
		$sql = $db->query("SELECT  *
                             FROM  {admin_requests}
                            WHERE  request_type = 3 AND project_id = 0 AND resolved_by = 0
                         ORDER BY  time_submitted ASC");
		$page->assign('pendings', $db->fetchAllArray($sql));
	case 'newproject':
	case 'os':
	case 'prefs':
	case 'resolution':
	case 'tasktype':
	case 'tag':
	case 'status':
	case 'version':
	case 'newgroup':
		$page->setTitle($fs->prefs['page_title'] . L('admintoolboxlong'));
		$page->pushTpl('admin.'.$area.'.tpl');
		break;

	case 'translations':
		require_once BASEDIR.'/scripts/langdiff.php';
		break;

	case 'checks':
		$hashtypes=$db->query('
			SELECT COUNT(*) c, LENGTH(user_pass) l,
			CASE WHEN SUBSTRING(user_pass FROM 1 FOR 1)=\'$\' THEN 1 ELSE 0 END AS s,
			SUM(CASE WHEN (SUBSTRING(user_pass FROM 1 FOR 2)=\'$2\' AND SUBSTRING(user_pass FROM 3 FOR 1)=\'$\' ) THEN 1 ELSE 0 END) cr,
			SUM(CASE WHEN (SUBSTRING(user_pass FROM 1 FOR 2)=\'$2\' AND SUBSTRING(user_pass FROM 3 FOR 1) IN( \'a\', \'x\', \'y\' ) ) THEN 1 ELSE 0 END) bcr,
			SUM(CASE WHEN SUBSTRING(user_pass FROM 1 FOR 3)=\'$1$\' THEN 1 ELSE 0 END) md5crypt,
			SUM(CASE WHEN SUBSTRING(user_pass FROM 1 FOR 8)=\'$argon2i\' THEN 1 ELSE 0 END) argon2i
			FROM {users}
			GROUP BY LENGTH(user_pass), CASE WHEN SUBSTRING(user_pass FROM 1 FOR 1)=\'$\' THEN 1 ELSE 0 END
			ORDER BY l ASC, s ASC');
		$hashlengths='<table><thead><tr><th>strlen</th><th>count</th><th>salted?</th><th>options</th><th>hash algo</th></tr></thead><tbody>';
		$warnhash=0;
		$warnhash2=0;
		while ($r = $db->fetchRow($hashtypes)){
			$alert='';
			if(    $r['l']==32 && $r['s']==0){  $maybe='md5';     $warnhash+=$r['c']; $alert=' style="background-color:#f99"';}
			elseif($r['l']==13 && $r['s']==0){  $maybe='CRYPT_STD_DES';  $r['s']=2; $warnhash2+=$r['c']; $alert=' style="background-color:#fc9"';}
			elseif($r['l']==40 && $r['s']==0){  $maybe='sha1';    $warnhash+=$r['c']; $alert=' style="background-color:#f99"';}
			elseif($r['l']==128 && $r['s']==0){ $maybe='sha512';  $warnhash+=$r['c']; $alert=' style="background-color:#f99"';}
			elseif($r['l']==34 && $r['s']==1){  $maybe='CRYPT_MD5';$warnhash2+=$r['c'];$alert=' style="background-color:#fc9"';}
			elseif($r['l']==60){$maybe='CRYPT_BLOWFISH';}
			elseif($r['s']==1){
				$maybe='other pw hashes';
				if($r['argon2i']>0){$maybe.=': '.$r['argon2i'].' argon2i'; }
			}else{$maybe='not detected';}
			$hashlengths.='<tr'.$alert.'><td>'.$r['l'].'</td><td> '.$r['c'].'</td><td>'.$r['s'].'</td><td>'.$r['bcr'].' '.$r['cr'].' '.$r['md5crypt'].' '.$r['argon2i'].'</td><td>'.$maybe.'</td></tr>';
		}
		$hashlengths.='</tbody></table>';
		if($warnhash>0){
			$hashlengths.='<div class="error">'.$warnhash." users with unsalted password hashes.</div>";
		}
		if($warnhash2>0){
			$hashlengths.='<div class="error">'.$warnhash2." users with salted password hashes, but considered bad algorithms for password hashing.</div>";
		}
		$page->assign('passwdcrypt', $conf['general']['passwdcrypt']);
		$page->assign('hashlengths', $hashlengths);

		# info of old temporary unfinished user registration entries, for insights into register bot pattern or for cleanup old entries to free unused usernames as available again.
		$statregistrations=$db->query('SELECT COUNT(*) FROM {registrations}');
		$regcount=$db->fetchOne($statregistrations);
		$page->assign('regcount', $regcount);

		# stats of unsent xmpp notification_messages
		# counts also possible orphaned entries (deleted entries in {notification_messages}) because adodb xmlschema does not have foreign key constraints feature.
		$xmppmessagecount=$db->query("SELECT COUNT(*) AS count FROM {notification_recipients}
			WHERE notify_method='j'");

		$page->assign('xmppmessagecount', $db->fetchRow($xmppmessagecount)['count']);

		# 10 of the unsent xmpp messages with count of recipients
		$xmppmessages=$db->query("
			SELECT m.message_id, COUNT(r.recipient_id) AS rcount, time_created, message_subject
			FROM {notification_messages} m
			LEFT JOIN {notification_recipients} r ON m.message_id=r.message_id
			WHERE notify_method='j'
			GROUP BY m.message_id
			LIMIT 10"
		);
		$page->assign('xmppmessages', $db->fetchAllArray($xmppmessages));

		# use join instead of left join here
		if ($db->dbtype=='pgsql') {
			$oldyear=$db->query("SELECT count(*)
				FROM {notification_messages} m
				JOIN {notification_recipients} r ON r.message_id=m.message_id
				WHERE r.notify_method='j'
				AND to_timestamp(time_created) < (CURRENT_TIMESTAMP - INTERVAL '1 year')");
		} else {
			# mysql/mariadb
			$oldyear=$db->query("SELECT count(*)
				FROM {notification_messages} m
				JOIN {notification_recipients} r ON r.message_id=m.message_id
				WHERE r.notify_method='j'
				AND from_unixtime(time_created) < (CURRENT_TIMESTAMP - INTERVAL 1 year)");
		}
		$page->assign('olderyear', $db->fetchRow($oldyear)[0]);

		if ($db->dbtype=='pgsql') {
			$oldmonth=$db->query("SELECT count(*)
				FROM {notification_messages} m
				JOIN {notification_recipients} r ON r.message_id=m.message_id
				WHERE r.notify_method='j'
				AND to_timestamp(time_created) < (CURRENT_TIMESTAMP - INTERVAL '1 month')");
		} else {
			# mysql/mariadb
			$oldmonth=$db->query("SELECT count(*)
				FROM {notification_messages} m
				JOIN {notification_recipients} r ON r.message_id=m.message_id
				WHERE r.notify_method='j'
				AND from_unixtime(time_created) < (CURRENT_TIMESTAMP - INTERVAL 1 month)");
		}
		$page->assign('oldermonth', $db->fetchRow($oldmonth)[0]);


		# show oldest unfinished user registrations
		$registrations=$db->query('SELECT reg_time, user_name, email_address FROM {registrations}
			ORDER BY reg_time ASC
			LIMIT 50');
		$page->assign('registrations', $db->fetchAllArray($registrations));

		/** check if all category tree sets (1 tree set per project + 1 global) are in an ok state (no crossing node lft-rgt)
		* example:
		*  1------------------------------------18
		*    2-3  4-----9 10-11 12------------17
		*           5---8          13-14 15-16
		*            6-7
		*
		* example of a bad state:
		* node1 4------9
		* node2  5------10
		*/
		$treeerrors = $db->query("SELECT c1.project_id, COUNT(*) AS count
			FROM {list_category} c1
			JOIN {list_category} c2 ON c1.project_id=c2.project_id
			WHERE c1.lft<c2.lft
			AND c1.rgt>c2.lft
			AND c1.rgt<c2.rgt
			GROUP BY c1.project_id");
		if ($db->countRows($treeerrors)) {
			$treeerrors=$db->fetchAllArray($treeerrors);
			$page->assign('cattreeerrors', $treeerrors);
		}

		// another state that should never happen in a nested set model.
		$rgtbelowequallft = $db->query("SELECT COUNT(*) FROM {list_category} WHERE rgt <= lft");
		$rgtbelowequallft = $db->fetchOne($rgtbelowequallft);
		if ($rgtbelowequallft > 0) {
			$page->assign('cattreelftrgt', $rgtbelowequallft);
		}

		// another check: in a nested set model there must lft and rgt number together be unique
		$cattreenonunique = $db->query("SELECT project_id, lft, COUNT(*) c
			FROM (
				SELECT project_id, category_id, lft FROM {list_category}
				UNION
				SELECT project_id, category_id, rgt AS lft FROM {list_category}
			) AS t
			GROUP BY project_id, lft
			HAVING COUNT(*)>1
			ORDER BY project_id, lft");
		if ($db->countRows($cattreenonunique)) {
			$cattreenonunique = $db->fetchAllArray($cattreenonunique);
			$page->assign('cattreenonunique', $cattreenonunique);
		}

		/** check if tasks have wrong category id, eg. after moving task to other project without changing to a global category or target project category.
		 * Or if a category was deleted while having tasks related to it.
		 * This may happen because older Flyspray version didn't warn while moving or user just overruled it, forcing the move to other project
		 * or just deleting a category. May be tolerable for old closed task for example, depends if you care about that.
		 * At least there is now a query that tells you about that.
		 */
		$wrongtaskcatscount = $db->query("
			SELECT COUNT(*)
			FROM {tasks} t
			LEFT JOIN {list_category} c ON t.product_category=c.category_id
			WHERE (t.project_id <> c.project_id AND c.project_id >0)
			OR c.project_id IS NULL");
		$wrongtaskcatscount = $db->fetchOne($wrongtaskcatscount);
		$page->assign('wrongtaskcategoriescount', $wrongtaskcatscount);

		$wrongtaskcats = $db->query("
			SELECT t.task_id, t.product_category, t.project_id AS tpid, c.project_id AS cpid, t.is_closed
			FROM {tasks} t
			LEFT JOIN {list_category} c ON t.product_category=c.category_id
			WHERE (t.project_id <> c.project_id AND c.project_id >0)
			OR c.project_id IS NULL
			ORDER BY t.project_id, t.is_closed, t.task_id desc
			LIMIT 20");
		$page->assign('wrongtaskcategories', $db->fetchAllArray($wrongtaskcats));

		$sinfo=$db->dblink->serverInfo();
		if( ($db->dbtype=='mysqli' || $db->dbtype=='mysql') && isset($sinfo['version'])) {
			# contrary to MariaDB 10.4.17, MYSQL 8.0.22 returns fields from information_schema always in UPPERCASE, so explicit use AS as workaround.
			$fsdb=$db->query("
				SELECT
				default_character_set_name AS default_character_set_name,
				default_collation_name AS default_collation_name
				FROM INFORMATION_SCHEMA.SCHEMATA
				WHERE SCHEMA_NAME=?", array($db->dblink->database)
			);
			$page->assign('fsdb', $db->fetchRow($fsdb));

			/**
			 * @todo Test if Flyspray tables really have default charset utf8mb4 and default collation utf8mb4_unicode_ci.
			 * @todo Test if the TEXT/CHAR/VARCHAR fields that should have utf8mb4_general_ci or utf8mb_unicode_ci really have it.
			 * (*general_ci assumed faster, *unicode_ci sorting more accurate)
			 * @todo Test if the TEXT/CHAR/VARCHAR fields that should have other collations really have that other collation.
			 * utf8mb4_unicode_ci may be not optimal for every TEXT/CHAR/VARCHAR field of Flyspray.
			 * Must be defined explicit for fields that differs from the default in the xmlschemas in the setup/upgrade/* files.
			 * At the moment (in 2021) the current ADODB 5.21.0 release does not handle that stuff yet.
			 */
			if(version_compare($sinfo['version'], '5.5.3')>=0 ){
				$page->assign('utf8mb4upgradable', "Your MySQL supports full utf-8 since 5.5.3. You are using ".$sinfo['version']." and Flyspray tables could be upgraded.");
			} else{
				$page->assign('oldmysqlversion', "Your MySQL version ".$sinfo['version']." does not support full utf-8, only up to 3 Byte chars. No emojis for instance. Consider upgrading your MySQL server version.");
			}

			# contrary to MariaDB 10.4.17, MYSQL 8.0.22 returns fields from information_schema always in UPPERCASE, so explicit use AS as workaround.
			$fstables=$db->query("SELECT
				table_name AS table_name,
				table_collation AS table_collation,
				engine AS table_type,
				create_options AS create_options,
				table_comment AS table_comment
				FROM INFORMATION_SCHEMA.tables
				WHERE table_schema=? AND table_name LIKE '".$db->dbprefix."%'
				ORDER BY table_name ASC", array($db->dblink->database)
			);
			$page->assign('fstables', $db->fetchAllArray($fstables));

			# contrary to MariaDB 10.4.17, MYSQL 8.0.22 returns fields from information_schema always in UPPERCASE, so explicit use AS as workaround.
			$fsfields=$db->query("
				SELECT
				table_name AS table_name,
				column_name AS column_name,
				column_default AS column_default,
				data_type AS data_type,
				is_nullable AS is_nullable,
				character_set_name AS character_set_name,
				collation_name AS collation_name,
				column_type AS column_type,
				column_comment AS column_comment
				FROM INFORMATION_SCHEMA.columns
				WHERE table_schema=?
				AND table_name LIKE '".$db->dbprefix."%'
				ORDER BY table_name ASC, ordinal_position ASC", array($db->dblink->database)
			);
			$page->assign('fsfields', $db->fetchAllArray($fsfields));

		} elseif ($db->dbtype=='pgsql') {
			$fsdb=$db->query("SELECT datcollate AS default_collation_name, datctype AS default_character_set_name FROM pg_database WHERE datname=?", array($db->dblink->database));
                        $page->assign('fsdb', $db->fetchRow($fsdb));

			$fstables=$db->query("SELECT table_name, '' AS table_collation, table_type, '' AS create_options, '-' AS table_comment
				FROM INFORMATION_SCHEMA.tables
				WHERE table_catalog=? AND table_name LIKE '".$db->dbprefix."%'
				ORDER BY table_name ASC", array($db->dblink->database)
			);
			$page->assign('fstables', $db->fetchAllArray($fstables));

			$fsfields=$db->query("
				SELECT table_name, column_name, column_default, data_type as column_type, is_nullable, character_set_name, collation_name, '-' AS column_comment
				FROM INFORMATION_SCHEMA.columns
				WHERE table_catalog=? AND table_name LIKE '".$db->dbprefix."%'
				ORDER BY table_name ASC, ordinal_position ASC", array($db->dblink->database)
			);
			$page->assign('fsfields', $db->fetchAllArray($fsfields));
		}
		$page->assign('adodbversion', $db->dblink->version());
		$page->assign('htmlpurifierversion', HTMLPurifier::VERSION);

		# swiftmailer 5.4.* version not set for class when installed with composer, so test for a VERSION file in swiftmailer directory first:
		if (file_exists('./vendor/swiftmailer/swiftmailer/VERSION')) {
			$page->assign('swiftmailerversion', file_get_contents('./vendor/swiftmailer/swiftmailer/VERSION'));
		} else {
			# maybe	later versions get it right
			$page->assign('swiftmailerversion', Swift::VERSION);
		}

		$page->pushTpl('admin.'.$area.'.tpl');
		break;
	default:
		Flyspray::show_error(6);
}

?>
