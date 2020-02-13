<?php
/*****************************\
| Activity Graph Maker        |
| Renders a graph for topview |
\*****************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

$data=array();

# Project Graph
if ((Get::has('project_id') && Get::val('graph', 'project') == 'project')) {
	if ($user->can_view_project(Get::num('project_id'))) {
		$today          = date('Y-m-d');
		$thirtyone_days = date('U' , strtotime("-31 day", strtotime($today)));
		$sixtyone_days  = date('U' , strtotime("-61 day", strtotime($today)));
		$end            = date('U' , strtotime("+1 day", strtotime($today)));

		//look 30 + days and if found scale
		$projectCheck = Project::getActivityProjectCount($sixtyone_days, $thirtyone_days, Get::num('project_id'));

		if ($projectCheck > 0) {
			$data = Project::getDayActivityByProject($sixtyone_days, $end, Get::num('project_id'));
		} else {
			$data = Project::getDayActivityByProject($thirtyone_days, $end, Get::num('project_id'));
		}

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
		$end            = date('U' , strtotime("+1 day", strtotime($today)));

		//look 30 + days and if found scale
		$projectCheck = Project::getActivityProjectCount($sixtyone_days, $thirtyone_days, Get::num('project_id'));

		if($projectCheck > 0) {
			$data = User::getDayActivityByUser($sixtyone_days, $end, Get::num('project_id'), Get::num('user_id'));
		} else {
			$data = User::getDayActivityByUser($thirtyone_days, $end, Get::num('project_id'), Get::num('user_id'));
		}

	} else {
		# and make the zero-line 'invisible'
		$_GET['line']='fff';
	} 
} else {
	# make the zero-line 'invisible'
	$_GET['line']='fff';
}

$height=25;
$width=160;
if (extension_loaded('gd')) {
	// Not pretty but gets the job done.
	$data=implode(',', $data);
	$_SERVER['QUERY_STRING'] = 'size='.$width.'x'.$height.'&data='.$data;
	$_GET['size'] = $width.'x'.$height;
	$_GET['data'] = $data;
	require dirname(__DIR__) . '/vendor/jamiebicknell/Sparkline/sparkline.php';
} else {
	# maybe svg gets the default as it is more versatile than just sparklines with gd

	header('Content-Type: image/svg+xml');
	# do we need that really?
	/*echo '<?xml version="1.0" standalone="no"?>'; */
	#echo '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 20010904//EN" "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">';
	echo '<svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="'.$height.'" viewbox="0 0 '.$width.' '.$height.'">';
	if (count($data)<1) {
		echo '<!-- no data --></svg>';
		exit;
	}
	$max=max($data); # TODO: limit influence of unusual high activity spikes, or logarithm scales
	if ($max<1) {
		$max=1;
	}
	$days=count($data);
	# just to be sure                                     
	if ($days<1) {
		$days=1;
	}
	$path='';
	$tick=0;
	foreach ($data as $d){
		$tick++;
		$path.=' L'.$tick.','.($height*$d/$max);
	}
	$path.=' L'.$tick.',0';
	echo "\n".'<path d="M0,0 '.$path.'" stroke="#369" stroke-width="'.(2*$days/$width).'" fill="none" transform="translate(0,'.$height.') scale('.($width/$days).',-1)"/>';
	echo '</svg>';
	exit;
}
