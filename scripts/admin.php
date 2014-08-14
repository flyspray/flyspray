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
$proj->setCookie();

$page->pushTpl('admin.menu.tpl');

switch ($area = Req::val('area', 'prefs')) {
    case 'users':
        $id = Flyspray::UserNameToId(Req::val('user_name'));
        if (!$id) {
            $id = Req::val('user_id');
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
                Flyspray::Redirect(CreateURL('pm', 'groups', $proj->id));
            }
            $page->uses('group_details');
        }
    case 'groups':
    case 'newuser':
    case 'newuserbulk':
    case 'editallusers':
        $page->assign('groups', Flyspray::ListGroups());
    case 'userrequest':
	$sql = $db->Query("SELECT  *
                             FROM  {admin_requests}
                            WHERE  request_type = 3 AND project_id = 0 AND resolved_by = 0
                         ORDER BY  time_submitted ASC");

        $page->assign('pendings', $db->fetchAllArray($sql));
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
