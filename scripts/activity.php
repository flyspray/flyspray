<?php

  /********************************************************\
  | Activity Graph Maker 								   |
  | Renders a graph for topview                            |
  | ~~~~~~~~~~~~~                                          |
  \********************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

//PROJECT GRAPH
if ((Get::has('project_id') && Get::val('graph', 'project') == 'project')) {
    $today          = date('Y-m-d');
    $thirtyone_days = date('Y-m-d' , strtotime("-31 day", strtotime($today)));
    $sixtyone_days  = date('Y-m-d' , strtotime("-61 day", strtotime($today)));
    
    //look 30 + days and if found scale
    $projectCheck = Project::getActivityProjectCount($sixtyone_days, $thirtyone_days, Get::num('project_id'));
    
    if($projectCheck > 0) {
        $data = Project::getDayActivityByProject($thirtyone_days, date('Y-m-d'), Get::num('project_id'));
    } else {
        $data = Project::getDayActivityByProject($thirtyone_days, date('Y-m-d'), Get::num('project_id'));
    }
    
    $data = implode(',', array_reverse($data));
    
//User Graph
} else if(Get::has('user_id') && Get::has('project_id') && Get::val('graph') == 'user') {
    $today          = date('Y-m-d');
    $thirtyone_days = date('Y-m-d' , strtotime("-31 day", strtotime($today)));
    $sixtyone_days  = date('Y-m-d' , strtotime("-61 day", strtotime($today)));
    
    //look 30 + days and if found scale
    $projectCheck = Project::getActivityProjectCount($sixtyone_days, $thirtyone_days, Get::num('project_id'));
    
    if($projectCheck > 0) {
        $data = User::getDayActivityByUser($thirtyone_days, date('Y-m-d'), Get::num('project_id'), Get::num('user_id'));
    } else {
        $data = User::getDayActivityByUser($thirtyone_days, date('Y-m-d'), Get::num('project_id'), Get::num('user_id'));
    }
    
    $data = implode(',', array_reverse($data));
} else {
    $data = '';
}

// Not pretty but gets the job done.
$_SERVER['QUERY_STRING'] = 'size=160x25&data='. $data;
$_GET['size']            = '160x25';
$_GET['data']            = $data;
require dirname(__DIR__) . '/vendor/jamiebicknell/Sparkline/sparkline.php';