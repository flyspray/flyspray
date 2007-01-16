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
    Flyspray::Redirect($baseurl);
}

if (Req::val('user_name') != '' && Req::val('password') != '') {
    // Otherwise, they requested login.  See if they provided the correct credentials...
    $username = Req::val('user_name');
    $password = Req::val('password');

    // Run the username and password through the login checker
    if (($user_id = Flyspray::checkLogin($username, $password)) < 1) {
        $_SESSION['failed_login'] = Req::val('user_name');
        if ($user_id == -1) {
            Flyspray::show_error(23);
        } else  /* $user_id == 0 */ {
            Flyspray::show_error(7);
        }
    } else {
        // Determine if the user should be remembered on this machine
        if (Req::has('remember_login')) {
            $cookie_time = time() + (60 * 60 * 24 * 30); // Set cookies for 30 days
        } else {
            $cookie_time = 0; // Set cookies to expire when session ends (browser closes)
        }
        
        $user = new User($user_id);

        // Set a couple of cookies
        Flyspray::setcookie('flyspray_userid', $user->id, $cookie_time);
        Flyspray::setcookie('flyspray_passhash', crypt($user->infos['user_pass'], $conf['general']['cookiesalt']), $cookie_time);

        // If the user had previously requested a password change, remove the magic url
        $remove_magic = $db->Query("UPDATE {users} SET magic_url = '' WHERE user_id = ?",
                                    array($user->id));

        $_SESSION['SUCCESS'] = L('loginsuccessful');
    }
}
else {
    // If the user didn't provide both a username and a password, show this error:
    Flyspray::show_error(8);
}

Flyspray::Redirect(Req::val('return_to'));
?>
