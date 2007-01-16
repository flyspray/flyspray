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

switch ($area = Req::val('area', 'prefs')) {
    case 'users':
        $id = Flyspray::username_to_id(Req::val('user_id'));
        
        $theuser = new User($id, $proj);
        if ($theuser->isAnon()) {
            Flyspray::show_error(5, true, null, $_SESSION['prev_page']);
        }
        $page->assign('theuser', $theuser);
    case 'cat':
    case 'editgroup':
    case 'groups':
    case 'newuser':
        $page->assign('groups', Flyspray::ListGroups());
    case 'newproject':
    case 'os':
    case 'prefs':
    case 'resolution':
    case 'tasktype':
    case 'status':
    case 'version':
    case 'newgroup':

        $page->setTitle($fs->prefs['page_title'] . L('admintoolboxlong'));
        $page->pushTpl('admin.'.$area.'.tpl');
        break;

    default:
        Flyspray::show_error(6);
}

?>
