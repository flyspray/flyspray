<?php
/*
    This script is the AJAX callback that performs a search
    for users, and returns them in an ordered list.
*/

define('IN_FS', true);

header('Content-type: text/html; charset=utf-8');

require_once('../../header.php');

$searchterm = '%' . reset($_POST) . '%';

// Get the list of users from the global groups above
$get_users = $db->Query('SELECT u.real_name, u.user_name
                           FROM {users} u
                          WHERE u.user_name LIKE ? OR u.real_name LIKE ?',
                         array($searchterm, $searchterm), 20);

$html = '<ul class="autocomplete">';

while ($row = $db->FetchRow($get_users))
{
   $html .= '<li title="' . $row['real_name'] . '">' . $row['user_name'] . '<span class="informal"> (' . $row['real_name'] . ')</span></li>';
}

$html .= '</ul>';

echo $html;

?>
