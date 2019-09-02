<?php

  /********************************************************\
  | User authentication (no output)                        |
  | ~~~~~~~~~~~~~~~~~~~                                    |
  \********************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

if (Req::val('logout')) {
    $user->logout();
    Flyspray::redirect($baseurl);
}

if (Req::val('user_name') != '' && Req::val('password') != '') {
    // Otherwise, they requested login.  See if they provided the correct credentials...
    // FIXME: Do not do clean_username. Should not autostrip stuff
    // $username = Backend::clean_username(Req::val('user_name'));
    $username = Req::val('user_name');
    $password = Req::val('password');
    $method = isset($conf['general']['auth_method']) ? $conf['general']['auth_method'] : 'native';

    // Run the username and password through the login checker
    $user_id = Flyspray::checkLogin($username, $password, $method);
    if($method === 'ldap' && $user_id < 1) {
        // Recheck with native method if LDAP
        $user_id = Flyspray::checkLogin($username, $password, 'native');
    }
    if ($user_id < 1) {
        $_SESSION['failed_login'] = Req::val('user_name');
        if($user_id === -2) {
            Flyspray::show_error(L('usernotexist'));
        }elseif ($user_id === -1) {
            Flyspray::show_error(23);
        } else  /* $user_id == 0 */ {
            // just some extra check here so that never ever an account can get locked when it's already disabled
            // ... that would make it easy to get enabled
            $db->query('UPDATE {users} SET login_attempts = login_attempts+1 WHERE account_enabled = 1 AND user_name = ?',
                        array($username));
            // Lock account if failed too often for a limited amount of time
            $db->query('UPDATE {users} SET lock_until = ?, account_enabled = 0 WHERE login_attempts > ? AND user_name = ?',
                         array(time() + 60 * $fs->prefs['lock_for'], LOGIN_ATTEMPTS, $username));

            if ($db->affectedRows()) {
                Flyspray::show_error(sprintf(L('error71'), $fs->prefs['lock_for']));
                Flyspray::redirect($baseurl);
            } else {
                Flyspray::show_error(7);
            }
        }
    } else {
        // Determine if the user should be remembered on this machine
        if (Req::has('remember_login')) {
            $cookie_time = time() + (60 * 60 * 24 * 30); // Set cookies for 30 days
        } else {
            $cookie_time = 0; // Set cookies to expire when session ends (browser closes)
        }

        $user = new User($user_id);

			# check if user still has an outdated password hash and upgrade it
			if(    $conf['general']['passwdcrypt']!='md5'
			    && $conf['general']['passwdcrypt']!='sha1'
			    && $conf['general']['passwdcrypt']!='sha512'
			){
				if( substr($user->infos['user_pass'],0,1)!='$'
				    && ( strlen($user->infos['user_pass'])==32
				      || strlen($user->infos['user_pass'])==40
				      || strlen($user->infos['user_pass'])==128
				       )
				){
 						# upgrade from unsalted md5 or unsalted sha1 or unsalted sha512 to better
						if($conf['general']['passwdcrypt']=='argon2i'){
							$newhash=password_hash($password, PASSWORD_ARGON2I);
						}else{
							$cryptoptions=array('cost'=>12);
							$newhash=password_hash($password, PASSWORD_BCRYPT, $cryptoptions);
						}
						# save the new hash
						$db->query("UPDATE {users} SET user_pass=? WHERE user_id=?", array($newhash, $user_id));
						# reload the user with updated data
						$user= new User($user_id);
				}
		}
		
        // Set a couple of cookies
        $passweirded = crypt($user->infos['user_pass'], $conf['general']['cookiesalt']);
        Flyspray::setCookie('flyspray_userid', $user->id, $cookie_time,null,null,null,true);
        Flyspray::setCookie('flyspray_passhash', $passweirded, $cookie_time,null,null,null,true);
        // If the user had previously requested a password change, remove the magic url
        $remove_magic = $db->query("UPDATE {users} SET magic_url = '' WHERE user_id = ?",
                                    array($user->id));
        // Save for displaying
        if ($user->infos['login_attempts'] > 0) {
            $_SESSION['login_attempts'] = $user->infos['login_attempts'];
        }
        $db->query('UPDATE {users} SET login_attempts = 0, last_login = ? WHERE user_id = ?', array(time(), $user->id));

        $_SESSION['SUCCESS'] = L('loginsuccessful');
    }
}
else {
    // If the user didn't provide both a username and a password, show this error:
    Flyspray::show_error(8);
}

Flyspray::redirect(Req::val('return_to'));
?>
