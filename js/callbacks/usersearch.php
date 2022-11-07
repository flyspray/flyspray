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

// task search contains several username fields (opened, dev, closed)
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

$html = '<ul class="autocomplete">';
$json=[];

while ($row = $db->fetchRow($get_users)) {
   $data = array_map(array('Filters','noXSS'), $row);
   $html .= '<li title="' . $data['real_name'] . '">'.($data['profile_image']!='' ? '<img src="avatars/'.$data['profile_image'].'" />' : '<span class="noavatar"></span>' ). $data['user_name'] . '<span class="informal"> ' . $data['real_name'] . '</span></li>';
   $json[] = ['id' => $data['user_name'], 'name' => $data['user_name']];
}

$html .= '</ul>';

// ckeditor mention plugin wants JSON
if (isset($_POST['username'])) {
	echo json_encode($json);
} else {
	echo $html;
}

?>
