<?php

  /********************************************************\
  | Project Managers Toolbox                               |
  | ~~~~~~~~~~~~~~~~~~~~~~~~                               |
  | This script is for Project Managers to modify settings |
  | for their project, including general permissions,      |
  | members, group permissions, and dropdown list items.   |
  \********************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

if (!$user->perms('manage_project') || !$proj->id) {
    Flyspray::show_error(16);
}

switch ($area = Req::val('area', 'prefs')) {
    case 'pendingreq':
        $sql = $db->Query("SELECT  *
                             FROM  {admin_requests} ar
                        LEFT JOIN  {tasks} t ON ar.task_id = t.task_id
                        LEFT JOIN  {users} u ON ar.submitted_by = u.user_id
                            WHERE  ar.project_id = ? AND resolved_by = 0
                         ORDER BY  ar.time_submitted ASC", array($proj->id));

        $page->assign('pendings', $db->fetchAllArray($sql));

    case 'prefs':
    case 'groups':
        $page->assign('groups', Flyspray::ListGroups($proj->id));
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
    case 'tasktype':
    case 'resolution':
    case 'os':
    case 'version':
    case 'cat':
    case 'status':
    case 'newgroup':

        $page->setTitle($fs->prefs['page_title'] . L('pmtoolbox'));
        $page->pushTpl('pm.menu.tpl');
        $page->pushTpl('pm.'.$area.'.tpl');
        break;

    default:
        Flyspray::show_error(17);
}
?>
