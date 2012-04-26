<?php

  /********************************************************\
  | Activity Graph Maker 								   |
  | Renders a graph for topview                            |
  | ~~~~~~~~~~~~~                                          |
  \********************************************************/

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

require_once(BASEDIR . '/includes/external/sparkline/Sparkline_Bar.php');
$sparkline = new Sparkline_Bar();
$sparkline->SetBarColorDefault("CleanFS");
$sparkline->SetBarWidth(4);
$sparkline->SetBarSpacing(1);

//PROJECT GRAPH
//Anonymous
if(Get::has('project_id') && !Get::has('graph'))
{
    $thirtyDays = array();
    $today = date('m/j/Y');
    $daythirtyone = '';
    for($i = 1; $i < 31; $i++)
    {
        $newday = date( 'm/j/Y' , strtotime("-$i day", strtotime($today)));
        $val = Project::getDayActivityByProject($newday, Get::num('project_id'));
        $sparkline->SetData($i, $val[0]);
        $daythirtyone = $newday;
    }
    $daythirtyone = date( 'm/j/Y' , strtotime("-2 day", strtotime($daythirtyone)));
    $daysixtyone = date( 'm/j/Y' , strtotime("-32 day", strtotime($daythirtyone)));
    //look 30 days more and if found scale
    $projectCheck = Project::getActivityProjectCount($daysixtyone, $daythirtyone, Get::num('project_id'));
    if($projectCheck[0] > 0)
    {
        for($i = 30; $i < 61; $i++)
        {
            $newday = date( 'm/j/Y' , strtotime("-$i day", strtotime($daythirtyone)));
            $val = Project::getDayActivityByProject($newday, Get::num('project_id'));
            $sparkline->SetBarWidth(2);
            $sparkline->SetBarSpacing(0.5);
            $sparkline->SetData($i, $val[0]);
        }
    }
}//User Logged in
elseif(Get::has('project_id') && Get::has('graph') && Get::val('graph') == 'project')
{
    $thirtyDays = array();
    $today = date('m/j/Y');
    $daythirtyone = '';
    for($i = 1; $i < 31; $i++)
    {
        $newday = date( 'm/j/Y' , strtotime("-$i day", strtotime($today)));
        $val = Project::getDayActivityByProject($newday, Get::num('project_id'));
        $sparkline->SetData($i, $val[0]);
        $daythirtyone = $newday;
    }
    $daythirtyone = date( 'm/j/Y' , strtotime("-2 day", strtotime($daythirtyone)));
    $daysixtyone = date( 'm/j/Y' , strtotime("-32 day", strtotime($daythirtyone)));
    //look 30 days more and if found scale
    $projectCheck = Project::getActivityProjectCount($daysixtyone, $daythirtyone, Get::num('project_id'));
    $userCheck = User::getActivityUserCount($daysixtyone, $daythirtyone, Get::num('project_id'), Get::num('user_id'));
    if($projectCheck[0] > 0 || $userCheck[0] > 0)
    {
        for($i = 30; $i < 61; $i++)
        {
            $newday = date( 'm/j/Y' , strtotime("-$i day", strtotime($daythirtyone)));
            $val = Project::getDayActivityByProject($newday, Get::num('project_id'));
            $sparkline->SetBarWidth(2);
            $sparkline->SetBarSpacing(0.5);
            $sparkline->SetData($i, $val[0]);
        }
    }
}//User Graph
elseif(Get::has('user_id') && Get::has('project_id') && Get::val('graph') == 'user')
{
    $thirtyDays = array();
    $today = date('m/j/Y');
    $daythirtyone = '';
    for($i = 1; $i < 31; $i++)
    {
        $newday = date( 'm/j/Y' , strtotime("-$i day", strtotime($today)));
        $val = User::getDayActivityByUser($newday, Get::num('project_id'), Get::num('user_id'));
        $sparkline->SetData($i, $val[0]);
        $daythirtyone = $newday;
    }
    $daythirtyone = date( 'm/j/Y' , strtotime("-2 day", strtotime($daythirtyone)));
    $daysixtyone = date( 'm/j/Y' , strtotime("-32 day", strtotime($daythirtyone)));
    //look 30 days more and if found scale
    $check = Project::getActivityProjectCount($daysixtyone, $daythirtyone, Get::num('project_id'));
    if($check[0] > 0)
    {
        for($i = 30; $i < 61; $i++)
        {
            $newday = date( 'm/j/Y' , strtotime("-$i day", strtotime($daythirtyone)));
            $val = User::getDayActivityByUser($newday, Get::num('project_id'), Get::num('user_id'));
            $sparkline->SetBarWidth(2);
            $sparkline->SetBarSpacing(0.5);
            $sparkline->SetData($i, $val[0]);
        }
    }
}
else
{
    $sparkline->SetData(0, 0);
}

$sparkline->Render(20);
$activity = $sparkline->Output();