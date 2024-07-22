<?php
/*
    This script is the AJAX callback that performs a search
    for users, and returns them in an ordered list.
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

// don't allow anonymous users to access this page at all
if ($user->isAnon()) {
    die();
}
$first = reset($_POST);
if (is_array($first)) {
	$first = reset($first);
}
$searchterm = '%' . $first . '%';

// Get the list of users from the global groups above
$get_users = $db->query('SELECT real_name, user_name, profile_image
	FROM {users} u
	WHERE u.user_name LIKE ? OR u.real_name LIKE ?',
	array($searchterm, $searchterm), 20);

$html = '<ul>' . "\n";

while ($row = $db->fetchRow($get_users)) {
   $data = array_map(array('Filters','noXSS'), $row);
   $html .= "\t" . '<li title="' . $data['real_name'] . '"><span>' .
      (
         $data['profile_image'] != ''
         ?
         '<img src="avatars/'.$data['profile_image'].'" />'
         :
         '<span class="noavatar"><span class="fas fa-user fa-lg"></span></span>'
      ) .
      $data['user_name'] . '<span class="informal"> ' . $data['real_name'] . '</span></span></li>' . "\n";
}

$html .= '</ul>' . "\n";

echo $html;

?>
