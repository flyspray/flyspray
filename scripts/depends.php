<?php

  /********************************************************\
  | Task Dependancy Graph                                  |
  | ~~~~~~~~~~~~~~~~~~~~~                                  |
  \********************************************************/

/**
 * XXX: This stuff looks incredible ugly, rewrite me for 1.0
 */

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

if ( !($task_details = Flyspray::GetTaskDetails(Req::num('task_id')))
        || !$user->can_view_task($task_details))
{
    Flyspray::show_error(9);
}

$id = Req::num('task_id');
$page->assign('task_id', $id);

$prunemode = Req::num('prune', 0);
$selfurl   = CreateURL('depends', $id);
$pmodes    = array(L('none'), L('pruneclosedlinks'), L('pruneclosedtasks'));

foreach ($pmodes as $mode => $desc) {
    if ($mode == $prunemode) {
        $strlist[] = $desc;
    } else {
        $strlist[] = "<a href='". htmlspecialchars($selfurl, ENT_QUOTES, 'utf-8') .
                      ($mode !=0 ? "&amp;prune=$mode" : "") . "'>$desc</a>\n";
    }
}

$page->uses('strlist');

$starttime = microtime();

$sql= 'SELECT t1.task_id AS id1, t1.item_summary AS sum1,
             t1.percent_complete AS pct1, t1.is_closed AS clsd1,
             lst1.status_name AS stat1, t1.task_severity AS sev1,
             t1.task_priority AS pri1,
             t1.closure_comment AS com1, u1c.real_name AS clsdby1,
             r1.resolution_name as res1,
             t2.task_id AS id2, t2.item_summary AS sum2,
             t2.percent_complete AS pct2, t2.is_closed AS clsd2,
             lst2.status_name AS stat2, t2.task_severity AS sev2,
             t2.task_priority AS pri2,
             t2.closure_comment AS com2, u2c.real_name AS clsdby2,
             r2.resolution_name as res2
       FROM  {dependencies} AS d
       JOIN  {tasks} AS t1 ON d.task_id=t1.task_id
  LEFT JOIN  {users} AS u1c ON t1.closed_by=u1c.user_id
  LEFT JOIN  {list_status} AS lst1 ON t1.item_status = lst1.status_id
  LEFT JOIN  {list_resolution} AS r1 ON t1.resolution_reason=r1.resolution_id
       JOIN  {tasks} AS t2 ON d.dep_task_id=t2.task_id
  LEFT JOIN  {list_status} AS lst2 ON t2.item_status = lst2.status_id
  LEFT JOIN  {users} AS u2c ON t2.closed_by=u2c.user_id
  LEFT JOIN  {list_resolution} AS r2 ON t2.resolution_reason=r2.resolution_id
      WHERE  t1.project_id= ?
   ORDER BY  d.task_id, d.dep_task_id';

$get_edges = $db->Query($sql, array($proj->id));

$edge_list = array();
$rvrs_list = array();
$node_list = array();
while ($row = $db->FetchRow($get_edges)) {
    extract($row, EXTR_REFS);
    $edge_list[$id1][] = $id2;
    $rvrs_list[$id2][] = $id1;
    if (!isset($node_list[$id1])) {
        $node_list[$id1] =
	  array('id'=>$id1, 'sum'=>$sum1, 'pct'=>$pct1, 'clsd'=>$clsd1,
		'status_name'=>$stat1, 'sev'=>$sev1, 'pri'=>$pri1,
		'com'=>$com1, 'clsdby'=>$clsdby1, 'res'=>$res1);
    }
    if (!isset($node_list[$id2])) {
        $node_list[$id2] =
	  array('id'=>$id2, 'sum'=>$sum2, 'pct'=>$pct2, 'clsd'=>$clsd2,
		'status_name'=>$stat2, 'sev'=>$sev2, 'pri'=>$pri2,
		'com'=>$com2, 'clsdby'=>$clsdby2, 'res'=>$res2);
    }
}

// Now we have our lists of nodes and edges, along with a helper
// list of reverse edges. Time to do the graph coloring, so we know
// which ones are in our particular connected graph. We'll set up a
// list and fill it up as we visit nodes that are connected to our
// main task.

$connected  = array();
$levelsdown = 0;
$levelsup   = 0;
function ConnectsTo($id, $down, $up) {
    global $connected, $edge_list, $rvrs_list, $levelsdown, $levelsup;
    global $prunemode, $node_list;
    if (!isset($connected[$id])) { $connected[$id]=1; }
    if ($down > $levelsdown) { $levelsdown = $down; }
    if ($up   > $levelsup  ) { $levelsup   = $up  ; }
#echo "$id ($down d, $up u) => $levelsdown d $levelsup u<br>\n";
    if (empty($node_list)) return;
    $selfclosed = $node_list[$id]['clsd'];
    if (isset($edge_list[$id])) {
        foreach ($edge_list[$id] as $neighbor) {
            $neighborclosed = $node_list[$neighbor]['clsd'];
            if (!isset($connected[$neighbor]) &&
                    !($prunemode==1 && $selfclosed && $neighborclosed) &&
                    !($prunemode==2 && $neighborclosed)) {
                ConnectsTo($neighbor, $down, $up+1);
            }
        }
    }
    if (isset($rvrs_list[$id])) {
        foreach ($rvrs_list[$id] as $neighbor) {
            $neighborclosed = $node_list[$neighbor]['clsd'];
            if (!isset($connected[$neighbor]) &&
                    !($prunemode==1 && $selfclosed && $neighborclosed) &&
                    !($prunemode==2 && $neighborclosed)) {
                ConnectsTo($neighbor, $down+1, $up);
            }
        }
    }
}

ConnectsTo($id, 0, 0);
$connected_nodes = array_keys($connected);
sort($connected_nodes);

// Now lets get rid of the extra junk in our arrays.
// In prunemode 0, we know we're only going to have to get rid of
// whole lists, and not elements in the lists, because if they were
// in the list, they'd be connected, so we wouldn't be removing them.
// In prunemode 1 or 2, we may have to remove stuff from the list, because
// you can have an edge to a node that didn't end up connected.
foreach (array("edge_list", "rvrs_list", "node_list") as $l) {
    foreach (${$l} as $n => $list) {
        if (!isset($connected[$n])) {
            unset(${$l}[$n]);
        }
        if ($prunemode!=0 && $l!="node_list" && isset(${$l}[$n])) {
            // Only keep entries that appear in the $connected_nodes list
            ${$l}[$n] = array_intersect(${$l}[$n], $connected_nodes);
        }
    }
}

// Now we've got everything we need... prepare JSON data
$resultData = array();
foreach ($node_list as $task_id => $taskInfo) {
	$adjacencies = array();
	if (isset($edge_list[$task_id])) {
		foreach ($edge_list[$task_id] as $dst) {
			array_push($adjacencies, array('nodeTo' => $dst, 'nodeFrom' => $task_id));
		}
	}

    if ($task_id == $id) {
        $color = '#5F9729';
    } else if ($taskInfo['clsd']) {
        $color = '#808080';
    } else {
        $color = '#83548B';
    }
    
	$newTask = array('id' => $task_id,
					 'name' => tpl_tasklink($task_id),
					 'data' => array('$color' => $color,
                                     '$type' => 'circle',
                                     '$dim' => 15),
                     'adjacencies' => $adjacencies);

	array_push($resultData, $newTask);
}

$jasonData = json_encode($resultData);
$page->assign('jasonData', $jasonData);
$page->assign('task_id', $id);

$page->setTitle(sprintf('FS#%d : %s', $id, L('dependencygraph')));
$page->pushTpl('depends.tpl');
