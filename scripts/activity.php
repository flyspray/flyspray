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
//If project_id or user_id != integer kill
if(isset($_GET['project_id']))
{
	if(!ctype_digit($_GET['project_id']))
	{
		die; //Not a digit
	}
}
elseif(isset($_GET['user_id']))
{
	if(!ctype_digit($_GET['user_id']))
	{
		die;//Not a digit
	}
}//PROJECT GRAPH
if(isset($_GET['project_id']) && !isset($_GET['user_id']))
{
	$thirtyDays = array();
	$today = date('m/j/Y');
	$daythirtyone = '';
	for($i = 1; $i < 31; $i++)
	{
		$newday = date( 'm/j/Y' , strtotime("-$i day", strtotime($today)));	
		$val = Project::getDayActivityByProject($newday, $_GET['project_id']);
		$sparkline->SetData($i, $val[0]);
		$daythirtyone = $newday;
	}
	$daythirtyone = date( 'm/j/Y' , strtotime("-2 day", strtotime($daythirtyone)));	
	$daysixtyone = date( 'm/j/Y' , strtotime("-32 day", strtotime($daythirtyone)));	
	//look 30 days more and if found scale
	$check = Project::getActivityProjectCount($daysixtyone, $daythirtyone, $_GET['project_id']);
	if($check[0] > 0)
	{
		for($i = 30; $i < 61; $i++)
		{
		$newday = date( 'm/j/Y' , strtotime("-$i day", strtotime($daythirtyone)));	
		$val = Project::getDayActivityByProject($newday, $_GET['project_id']);
		$sparkline->SetBarWidth(2);
		$sparkline->SetBarSpacing(0.5);
		$sparkline->SetData($i, $val[0]);
		}
	}
}//User Graph
elseif(isset($_GET['user_id']) && isset($_GET['project_id']))
{
	$thirtyDays = array();
	$today = date('m/j/Y');
	$daythirtyone = '';
	for($i = 1; $i < 31; $i++)
	{
		$newday = date( 'm/j/Y' , strtotime("-$i day", strtotime($today)));	
		$val = User::getDayActivityByUser($newday, $_GET['project_id'], $_GET['user_id']);
		$sparkline->SetData($i, $val[0]);
		$daythirtyone = $newday;
	}
	$daythirtyone = date( 'm/j/Y' , strtotime("-2 day", strtotime($daythirtyone)));	
	$daysixtyone = date( 'm/j/Y' , strtotime("-32 day", strtotime($daythirtyone)));	
	//look 30 days more and if found scale
	$check = User::getActivityUserCount($daysixtyone, $daythirtyone, $_GET['project_id'], $_GET['user_id']);
	if($check[0] > 0)
	{
		for($i = 30; $i < 61; $i++)
		{
		$newday = date( 'm/j/Y' , strtotime("-$i day", strtotime($daythirtyone)));	
		$val = User::getDayActivityByUser($newday, $_GET['project_id'], $_GET['user_id']);
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
?>
