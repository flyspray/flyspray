<?php

  /*********************************************************\
  | Register a new user (when confirmation codes is used)   |
  | ~~~~~~~~~~~~~~~~~~~                                     |
  \*********************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

$page->setTitle($fs->prefs['page_title'] . L('registernewuser'));

if (!$user->isAnon()) {
    Flyspray::Redirect($baseurl);
}

if ($user->can_register()) {
    if (Req::has('magic_url')) {
        // If the user came here from their notification link
        $sql = $db->Query('SELECT * FROM {registrations} WHERE magic_url = ?',
                          array(Get::val('magic_url')));

        if (!$db->CountRows($sql)) {
            Flyspray::show_error(18);
        }

        $page->pushTpl('register.magic.tpl');
    } else {
        $page->pushTpl('register.no-magic.tpl');
    }
} elseif ($user->can_self_register()) {
    $page->pushTpl('common.newuser.tpl');
} else {
    Flyspray::show_error(22);
}
?>
