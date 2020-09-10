<?php

  /********************************************************\
  | Show various reports on tasks                          |
  | ~~~~~~~~~~~~~~~~~~~~~~~~                               |
  \********************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

if (!$user->perms('view_reports')) {
    Flyspray::redirect($baseurl);
}

require_once BASEDIR . '/includes/events.inc.php';
$page->setTitle($fs->prefs['page_title'] . L('reports'));

/**********************\
*  Event reports       *
\**********************/

$events = array(1 => L('opened'),
                13 => L('reopened'),
                2 => L('closed'),
                3 => L('edited'),
                14 => L('assignmentchanged'),
                29 => L('events.useraddedtoassignees'),
                4 => L('commentadded'),
                5 => L('commentedited'),
                6 => L('commentdeleted'),
                7 => L('attachmentadded'),
                8 => L('attachmentdeleted'),
                11 => L('relatedadded'),
                12 => L('relateddeleted'),
                9 => L('notificationadded'),
                10 => L('notificationdeleted'),
                17 => L('reminderadded'),
                18 => L('reminderdeleted'),
                15 => L('addedasrelated'),
                16 => L('deletedasrelated'),
                19 => L('ownershiptaken'),
                20 => L('closerequestmade'),
                21 => L('reopenrequestmade'),
                22 => L('depadded'),
                23 => L('depaddedother'),
                24 => L('depremoved'),
                25 => L('depremovedother'),
                28 => L('pmreqdenied'),
                32 => L('subtaskadded'),
                33 => L('subtaskremoved'),
                34 => L('supertaskadded'),
                35 => L('supertaskremoved'),
    );

// Should events 19, 20, 21, 29 be here instead?
$user_events = array(30 => L('created'),
                     31 => L('deleted'));

$page->assign('events', $events);
$page->assign('user_events', $user_events);
$page->assign('theuser', $user);

$sort = strtoupper(Req::enum('sort', array('desc', 'asc')));

$where = array();
$params = array();
$orderby = '';

switch (Req::val('order')) {
    case 'type':
        $orderby = "h.event_type {$sort}, h.event_date {$sort}";
        break;
    case 'user':
        $orderby = "user_id {$sort}, h.event_date {$sort}";
        break;
    case 'date': default:
        $orderby = "h.event_date {$sort}, h.event_type {$sort}";
}

if( is_array(Req::val('events')) ){
	foreach (Req::val('events') as $eventtype) {
		$where[] = 'h.event_type = ?';
		$params[] = $eventtype;
	}
	$where = '(' . implode(' OR ', $where) . ')';

	if ($proj->id) {
		$where = $where . 'AND (t.project_id = ?  OR h.event_type IN(30, 31)) ';
		$params[] = $proj->id;
	}

	if ( Req::val('fromdate') || Req::val('todate')) {
		$where .= ' AND ';
		$fromdate = Req::val('fromdate');
		$todate = Req::val('todate');

		if ($fromdate) {
			$where .= ' h.event_date > ?';
			$params[] = Flyspray::strtotime($fromdate) + 0;
		}
		if ($todate && $fromdate) {
			$where .= ' AND h.event_date < ?';
			$params[] = Flyspray::strtotime($todate) + 86400;
		} else if ($todate) {
			$where .= ' h.event_date < ?';
			$params[] = Flyspray::strtotime($todate) + 86400;
		}
	}

	$histories = $db->query("SELECT h.*
                        FROM  {history} h
                   LEFT JOIN {tasks} t ON h.task_id = t.task_id
                        WHERE $where
                     ORDER BY $orderby", $params, Req::num('event_number', -1));
	$histories = $db->fetchAllArray($histories);
}

$page->uses('histories', 'sort');

$page->pushTpl('reports.tpl');
?>
