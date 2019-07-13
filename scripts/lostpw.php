<?php

  /*********************************************************\
  | Deal with lost passwords                                |
  | ~~~~~~~~~~~~~~~~~~~~~~~~                                |
  \*********************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

$page->setTitle($fs->prefs['page_title'] . L('lostpw'));

if (!Req::has('magic_url') && $user->isAnon()) {
    // Step One: user requests magic url
    $page->pushTpl('lostpw.step1.tpl');
}
elseif (Req::has('magic_url') && $user->isAnon()) {
    # Step Two: user enters new password
    # First as link from email (GET), form could be repeated as POST
    # when user misrepeats the new password. so GET and POST possible here!
    $check_magic = $db->query('SELECT * FROM {users} WHERE magic_url = ?',
            array(Req::val('magic_url')));

    if (!$db->countRows($check_magic)) {
        Flyspray::show_error(12);
    }
    $page->pushTpl('lostpw.step2.tpl');
} else {
    Flyspray::redirect($baseurl);
}
?>
