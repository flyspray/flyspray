<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-AU" xml:lang="en-AU">
<head>
  <title>Tests</title>
  <link href="style.css" rel="stylesheet" type="text/css" />
  <meta content="text/html; charset=UTF-8" http-equiv="content-type" />
</head>
<body>
  <table class="mytable" border="0" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <td colspan="3">Create tasks</td>
      </tr>
    </thead>
    <tbody>
    <?php
    $taskNumber = 2; // The first task created will have number 2 since
                     // flyspray initially has a dummy task
$this->plugin('login', 'developer', 'developer');
$task = array();
$task['summary'] = 'A task with default values';
$task['details'] = 'This task is created with default values';
$this->plugin('create_task', $task);
$this->plugin('verify_task', $taskNumber, $task);

$taskNumber++;
$task = array();
$task['summary'] = 'Unconfirmed task';
$task['status'] = 'Unconfirmed';
$task['details'] = 'This task is unconfirmed';
$this->plugin('create_task', $task);
$this->plugin('verify_task', $taskNumber, $task);

$taskNumber++;
$task = array();
$task['summary'] = 'Assigned to developer';
$task['assignedto'] = 'Joe Developer';
$task['details'] = 'This task is assigned to Joe Developer';
$this->plugin('create_task', $task);
$this->plugin('verify_task', $taskNumber, $task);

$taskNumber++;
$task = array();
$task['summary'] = 'Operating system windows';
$task['os'] = 'Windows';
$task['details'] = 'This task only affects windows';
$this->plugin('create_task', $task);
$this->plugin('verify_task', $taskNumber, $task);

$taskNumber++;
$task = array();
$task['summary'] = 'Critical task';
$task['severity'] = 'Critical';
$task['details'] = 'This is the most critical task ever';
$this->plugin('create_task', $task);
$this->plugin('verify_task', $taskNumber, $task);

$taskNumber++;
$task = array();
$task['summary'] = 'Priority flash';
$task['priority'] = 'Flash';
$task['details'] = 'This must fixed now since it is a flash task';
$this->plugin('create_task', $task);
$this->plugin('verify_task', $taskNumber, $task);

$taskNumber++;
$task = array();
$task['summary'] = 'Due on 19 October 2005';
$task['due'] = '19-oct-2005';
$task['details'] = 'This task is due 2005-10-19';
$this->plugin('create_task', $task);
$this->plugin('verify_task', $taskNumber, $task);

?>
    </tbody>
  </table>
</body>