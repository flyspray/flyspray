<?php

/**
 * This script is the AJAX callback that performs a search
 *  for users, and returns true if the user_name is not given.
 */

define('IN_FS', true);

header('Content-type: text/html; charset=utf-8');

require_once '../../header.php';


if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')) {
    $user = new User(Cookie::val('flyspray_userid'));
    $user->check_account_ok();
} else {
    $user = new User(0, $proj);
}

if ($user->isAnon()) {
    # at least allow for guests when user registration is enabled, fix FS#2528
    if( !($user->can_register() or $user->can_self_register()) ){
        die();
    }
}

if (Req::has('name')) {
    $searchterm = strtolower(Req::val('name'));
} else {
    die();
}

// Get the list of users from the global groups above
$get_users = $db->query('
    SELECT count(u.user_name) AS anz_u_user, count(r.user_name) AS anz_r_user
    FROM {users} u
    LEFT JOIN {registrations} r ON u.user_name = r.user_name
    WHERE LOWER(u.user_name) = ? OR LOWER(r.user_name) = ?',
    array($searchterm, $searchterm)
);

load_translations();

while ($row = $db->fetchRow($get_users)){
    if ($row['anz_u_user'] > '0' || $row['anz_r_user'] > '0') {
        $html = 'false|' . eL('usernametaken');
    } else {
        $html = 'true';
    }
}

echo $html;
?>
