<?php
define('IN_FS', true);

require_once dirname(__FILE__).'/header.php';

/* permission stuff */
if (Cookie::has('flyspray_userid') && Cookie::has('flyspray_passhash')) {
		$user = new User(Cookie::val('flyspray_userid'), $proj);
		$user->check_account_ok();
} else {
		$user = new User(0, $proj);
		echo 'logged out ..';
		die();
}

$result = $db->query('SELECT e.*, t.item_summary, p.project_title FROM {effort} e
	LEFT JOIN {tasks} t ON t.task_id=e.task_id
	LEFT JOIN {projects} p ON p.project_id=t.project_id
	WHERE user_id = ?
	AND end_timestamp IS NULL', array($user->id));
$result=$db->fetchAllArray($result);
?>
<html>
<head>
<title>Zeiterfassung</title>
<meta http-equiv="refresh" content="60"/>
<link media="screen" href="themes/CleanFS/theme.css" rel="stylesheet" type="text/css">
</head>
<body>
<h3>Meine laufenden Zeiterfassungen</h3>
<form action="index.php" method="post" target="_blank">
<input type="hidden" name="csrftoken" value="<?= $_SESSION['csrftoken'] ?>"/>
<input type="hidden" name="action" value="details.efforttracking"/>
<input type="hidden" name="stop_tracking" value="true"/>
<table border="1">
<thead>
<tr>
<!--<th>id</th>-->
<th>project</th>
<th>task</th>
<th>summary</th>
<th>effort<br/>tracking<br/> started</th>
<th></th>
</tr>
</thead>
<tbody>
<?php
$out='';
foreach($result as $r){
	$out.='<tr>
	<!--<td>'.$r['effort_id'].'</td>-->
	<td>'.$r['project_title'].'</td>
	<td>FS#'.$r['task_id'].'</td>
	<td><a href="index.php?do=details&task_id='.$r['task_id'].'" target="_blank">'.$r['item_summary'].'</a></td>
	<td>'.formatDate($r['start_timestamp'],true).'</td>
	<!--
	<td>'.$r['end_timestamp'].'</td>
	<td>'.$r['effort'].'</td>
	-->
	<td><button type="submit" name="task_id" value="'.$r['task_id'].'"/>Zeiterfassung beenden</button></td>
	</tr>';
}
echo $out;
echo 'webservertime: '.formatDate(time(),true);
?>
</tbody>
</table>
</form>
</html>
