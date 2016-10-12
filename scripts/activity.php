<?php
/*****************************\
| Activity Graph Maker        |
| Renders a graph for topview |
\*****************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

$data='';

# Project Graph
if ((Get::has('project_id') && Get::val('graph', 'project') == 'project')) {
	if ($user->can_view_project(Get::num('project_id'))) {
		$today          = date('Y-m-d');
		$thirtyone_days = date('U' , strtotime("-31 day", strtotime($today)));
		$sixtyone_days  = date('U' , strtotime("-61 day", strtotime($today)));

		//look 30 + days and if found scale
		$projectCheck = Project::getActivityProjectCount($sixtyone_days, $thirtyone_days, Get::num('project_id'));

		if($projectCheck > 0) {
			$data = Project::getDayActivityByProject($sixtyone_days, date('U', strtotime(date('Y-m-d'))), Get::num('project_id'));
		} else {
			$data = Project::getDayActivityByProject($thirtyone_days, date('U', strtotime(date('Y-m-d'))), Get::num('project_id'));
		}

		$data = implode(',', $data);
	} else {
		# and make the zero-line 'invisible'
		$_GET['line']='fff';
	}   
# User Graph
} else if(Get::has('user_id') && Get::has('project_id') && Get::val('graph') == 'user') {
	if ($user->can_view_project(Get::num('project_id'))) {
		$today          = date('Y-m-d');
		$thirtyone_days = date('U' , strtotime("-31 day", strtotime($today)));
		$sixtyone_days  = date('U' , strtotime("-61 day", strtotime($today)));

		//look 30 + days and if found scale
		$projectCheck = Project::getActivityProjectCount($sixtyone_days, $thirtyone_days, Get::num('project_id'));

		if($projectCheck > 0) {
			$data = User::getDayActivityByUser($sixtyone_days, date('U', strtotime(date('Y-m-d'))), Get::num('project_id'), Get::num('user_id'));
		} else {
			$data = User::getDayActivityByUser($thirtyone_days, date('U', strtotime(date('Y-m-d'))), Get::num('project_id'), Get::num('user_id'));
		}

		$data = implode(',', $data);
	} else {
		# and make the zero-line 'invisible'
		$_GET['line']='fff';
	} 
} else {
	# make the zero-line 'invisible'
	$_GET['line']='fff';
}

// Not pretty but gets the job done.
$_SERVER['QUERY_STRING'] = 'size=160x25&data='. $data;
$_GET['size']            = '160x25';
$_GET['data']            = $data;
require dirname(__DIR__) . '/vendor/jamiebicknell/Sparkline/sparkline.php';
