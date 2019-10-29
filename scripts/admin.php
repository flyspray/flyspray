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
#I $proj->setCookie();

$page->pushTpl('admin.menu.tpl');

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
    case 'cat':
    case 'editgroup':
        // yeah, utterly stupid, is changed in 1.0 already
        if (Req::val('area') == 'editgroup') {
            $group_details = Flyspray::getGroupDetails(Req::num('id'));
            if (!$group_details || $group_details['project_id'] != $proj->id) {
                Flyspray::show_error(L('groupnotexist'));
                Flyspray::redirect(createURL('pm', 'groups', $proj->id));
            }
            $page->uses('group_details');
        }
    case 'groups':
    case 'newuser':
    case 'newuserbulk':
    case 'editallusers':
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

		# show oldest unfinished user registrations
		$registrations=$db->query('SELECT reg_time, user_name, email_address FROM {registrations}
			ORDER BY reg_time ASC
			LIMIT 50');
		$page->assign('registrations', $db->fetchAllArray($registrations));

		$sinfo=$db->dblink->serverInfo();
		if( ($db->dbtype=='mysqli' || $db->dbtype=='mysql') && isset($sinfo['version'])){
			$fsdb=$db->query("SELECT default_character_set_name, default_collation_name
				FROM INFORMATION_SCHEMA.SCHEMATA
				WHERE SCHEMA_NAME=?", array($db->dblink->database)
			);
			$page->assign('fsdb', $db->fetchRow($fsdb));

			# TODO Test if Flyspray tables really have default charset utf8mb4 and default collation utf8mb4_unicode_ci.
			# TODO Test if the TEXT/CHAR/VARCHAR fields that should have utf8mb_unicode_ci really have it.
			# TODO Test if the TEXT/CHAR/VARCHAR fields that should have other collations really have that other collation.
			# utf8mb4_unicode_ci may be not optimal for every TEXT/CHAR/VARCHAR field of Flyspray.
			# Must be defined explicit for fields that differs from the default in the xmlschemas in the setup/upgrade/* files.
			# At the moment (in 2019) the current ADODB 5.20.14 release does not handle that stuff yet.

			if(version_compare($sinfo['version'], '5.5.3')>=0 ){
				$page->assign('utf8mb4upgradable', "Your MySQL supports full utf-8 since 5.5.3. You are using ".$sinfo['version']." and Flyspray tables could be upgraded.");
			} else{
				$page->assign('oldmysqlversion', "Your MySQL version ".$sinfo['version']." does not support full utf-8, only up to 3 Byte chars. No emojis for instance. Consider upgrading your MySQL server version.");
			}

			$fstables=$db->query("SELECT table_name, table_collation, engine as table_type, create_options, table_comment
				FROM INFORMATION_SCHEMA.tables
				WHERE table_schema=? AND table_name LIKE '".$db->dbprefix."%'
				ORDER BY table_name ASC", array($db->dblink->database)
			);
			$page->assign('fstables', $db->fetchAllArray($fstables));

			$fsfields=$db->query("
				SELECT table_name, column_name, column_default, data_type, character_set_name, collation_name, column_type, column_comment
				FROM INFORMATION_SCHEMA.columns
				WHERE table_schema=? AND table_name LIKE '".$db->dbprefix."%'
				ORDER BY table_name ASC, ordinal_position ASC", array($db->dblink->database)
			);
			$page->assign('fsfields', $db->fetchAllArray($fsfields));

		} elseif($db->dbtype=='pgsql'){
			$fsdb=$db->query("SELECT datcollate AS default_collation_name, datctype AS default_character_set_name FROM pg_database WHERE datname=?", array($db->dblink->database));
                        $page->assign('fsdb', $db->fetchRow($fsdb));
			
			$fstables=$db->query("SELECT table_name, '' AS table_collation, table_type, '' AS create_options, '-' AS table_comment
				FROM INFORMATION_SCHEMA.tables
				WHERE table_catalog=? AND table_name LIKE '".$db->dbprefix."%'
				ORDER BY table_name ASC", array($db->dblink->database)
			);
			$page->assign('fstables', $db->fetchAllArray($fstables));

			$fsfields=$db->query("
				SELECT table_name, column_name, column_default, data_type as column_type, character_set_name, collation_name, '-' AS column_comment
				FROM INFORMATION_SCHEMA.columns
				WHERE table_catalog=? AND table_name LIKE '".$db->dbprefix."%'
				ORDER BY table_name ASC, ordinal_position ASC", array($db->dblink->database)
			);
			$page->assign('fsfields', $db->fetchAllArray($fsfields));
		}
		$page->assign('adodbversion', $db->dblink->version());
		$page->assign('htmlpurifierversion', HTMLPurifier::VERSION);
		$page->pushTpl('admin.'.$area.'.tpl');
		break;
    default:
        Flyspray::show_error(6);
}

?>
