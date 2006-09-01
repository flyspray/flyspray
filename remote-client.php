<?php
/*
 ---------------------------------------------------------------
 | This is the test script for remote.php, the xml-rpc server. |
 | Most functions require authentication with a  Flyspray      |
 | username and password.                                      |
 ---------------------------------------------------------------

 Changes:
 4th August 2005: Angus Hardie Angus@malcolmhardie.com for xmlrpc library instead of ixr

 requires the xmlrpc library
 http://phpxmlrpc.sourceforge.net
 should be located in a directory called xmlrpc in the root of the flyspray directory

 */

define('IN_FS', true);

require_once('header.php');

// default server (for easier testing)

//$server = "http://flyspray.rocks.cc/bts/remote.php";

$server = Flyspray::absoluteURI('remote.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>

<link href="themes/Bluey/theme.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="javascript/functions.js"></script>
<title>Test page for the Flyspray XML-RPC interface</title>
</head>

<body>
<div style="padding:16px">
<h1>Flyspray XML-RPC interface</h1>

<?php
// If nothing was posted, use this first section
if (!isset($_REQUEST['username']))
//if (true)
{
   ?>
   <div id="intro" style="position:absolute;left:10px;top:80px;width:200px;">
   This is a test page for the Flyspray bug-tracking system XML-RPC interface.  XML-RPC allows
      you to request information from a remote Flyspray installation so that you can include it in your
      local application or website.  You are welcome to request information from the official Flyspray BTS,
      but you are free to point it to another Flyspray installation with the latest revision of remote.php
      </div>

      <div id="taskdetails" style="position:absolute;top:80px;left:250px;width:600px;border:1px solid black;">
         <form action="remote-client.php" method="get">
         <table style="width:100%">
         <caption>Request task details</caption>
         <tr>
         <td>Base URL to Flyspray</td>
         <td><input name="url" type="text" class="text" size="30" value="<?php echo $server;?>" /></td>
         </tr>
         <tr>
         <td>Your User Name:</td>
         <td><input name="username" type="text" class="text" size="30" /></td>
         </tr>
         <tr>
         <td>Your Password:</td>
         <td><input class="password" name="password" type="password" size="30" /></td>
         </tr>
         <tr>
         <td colspan="2"><hr /></td>
         </tr>
         <tr>
         <td>Action:</td>
         <td>
         <select id="task" name="rpcAction">
         <option value="getVersion">Get Flyspray version information</option>
         <option value="getTask">Get Task Information</option>
         <option value="getUser">Get User Information</option>
         <option value="getNewTaskData">getNewTaskData</option>
         <option value="getArrayListForName">getArrayListForName</option>
         <option value="createNewTask">createNewTask</option>
         <option value="openTask">openTask (with sample data)</option>
         <option value="filterTasks">Filter tasks</option>
          <option value="getProjects">getProjects</option>
         <option value="addComment">addComment</option>
         </select>
         </td>
         </tr>
         <tr id="getTaskForm">
         <td>Task ID:</td>
         <td><input name="taskid" type="text" class="text" size="4" value="1"/></td>
         </tr>
         <tr id="getUserForm">
         <td>User ID:</td>
         <td><input name="userid" type="text" class="text" size="4" value="1"/></td>
         </tr>
         <tr id="getArrayListForNameForm">
         <td>Array name:</td>
         <td><input name="arrayname" type="text" class="text" size="16" value="status"/></td>
         </tr>
         <tr id="getProjectIdForm">
         <td>Project id:</td>
         <td><input name="projectid" type="text" class="text" size="16" value="2"/></td>
         </tr>
         <tr id="getSearchTermForm">
         <td>Seach String:</td>
         <td><input name="filterSearch" type="text" class="text" size="20" value=""/></td>
         </tr>
         <tr id="activeProjectsOnlyForm">
         <td>Active Projects Only:</td>
         <td><input name="activeOnly" type="checkbox" checked/></td>
         </tr>
         
         <tr id="">
         <td>New comment text:</td>
         <td><input name="commenttext" type="text" class="text" size="40" value=""/></td>
         </tr>
         <tr>
         <td colspan="2"><hr /></td>
         </tr>
         <tr>
         <td>Debug?</td>
         <td><input name="debug" type="checkbox" value="1" /></td>
         </tr>
         <tr>
         <td colspan="2" align="center"><input type="submit" value="Send Request" /></td>
         </tr>
         </table>
         </form>
         </div>






<?php
// If something was posted, use this second section
}
else
{
   //echo "<div style=\"border:1px solid gray;padding:8px;overflow:scroll;height:400px;left:20px;width:350px;position:relative;float:left\">";
   echo "<div>";
   // Include a copy of the xml-rpc library. This can reside anywhere.
   // We're just calling the same copy as the server for convenience.
   // (switched to xmlrpc)
   require_once dirname(__FILE__) .'/includes/external/xmlrpc.inc';

   //extract parts of the submitted url
   $urlParts = parse_url($_REQUEST['url']);

   // Define the server. Enter the URL of your flyspray installation, with 'remote.php' at the end.
   $client = new xmlrpc_client($urlParts['path'],$urlParts['host']);

   // Enable debug for testing
   if (isset($_REQUEST['debug']) && $_REQUEST['debug'] == '1')
      $client->debug = true;
   
   $rpcAction = $_REQUEST['rpcAction'];
   $msg = "";

   if (!isset($rpcAction)) {
      die ("no action specified");
   }

   if ("getTask" == $rpcAction) {
      $response = remoteCall("fs.getTask",$_REQUEST['taskid']);
      showResponse($response);
   }

   if ("getUser" == $rpcAction) {
      $response = remoteCall("fs.getUser",$_REQUEST['userid']);
      showResponse($response);
   }

   /*if ("getTaskTypeList" == $rpcAction) {
      $response = remoteCall("fs.getTaskTypeList",array("projectid"=>$_REQUEST['projectid']));
      showResponse($response);
   }
*/
   if ("getProjects" == $rpcAction) {
      $response = remoteCall("fs.getProjects",array("activeonly"=>$_REQUEST['activeOnly']));
      showResponse($response);
   }
   

   if ("getNewTaskData" == $rpcAction) {

      $response = remoteCall("fs.getNewTaskData",array("projectid"=>$_REQUEST['projectid']));
      showResponse($response);
   }

   if ("getArrayListForName" == $rpcAction) {

      $response = remoteCall("fs.getArrayListForName",array("arrayname"=>$_REQUEST['arrayname'],"projectid"=>$_REQUEST['projectid']));
      showResponse($response);
   }

   if ("getVersion" == $rpcAction) {

      $response = remoteCall("fs.getVersion");
      showResponse($response);
   }
   
   if ("filterTasks" == $rpcAction) {

       $filterTaskData['search'] = $_REQUEST['filterSearch'];
       
       $response = remoteCall("fs.filterTasks",$filterTaskData);
       showResponse($response);
   }
   
   if ("addComment" == $rpcAction) {
      
      $commentData['taskid'] = $_REQUEST['taskid'];
      $commentData['commenttext'] = $_REQUEST['commenttext'];
      
      $response = remoteCall("fs.addComment",$commentData);
      showResponse($response);
   }
   

   if ("createNewTask" == $rpcAction) {
      $response = remoteCall("fs.getNewTaskData");


      if ($response->success) {
      ?>

         <div id="taskdetails">
         <form enctype="multipart/form-data" action="remote-client.php" method="post">
         <div>
         <table>
         <tr>
         <td>
         <input type="hidden" name="debug" value="<?php echo $_REQUEST['debug']?>" />
         <input type="hidden" name="do" value="modify" />
         <input type="hidden" name="rpcAction" value="newTask" />
         <input type="hidden" name="project_id" value="<?php echo $_REQUEST['projectid']?>" />
         <input type="hidden" name="username" value="<?php echo $_REQUEST['username']?>">
         <input type="hidden" name="password" value="<?php echo $_REQUEST['password']?>">
         <input type="hidden" name="url" value="<?php echo $_REQUEST['url']?>">
         <label for="itemsummary">Summary</label>
            </td>
            <td><input id="itemsummary" type="text" class="text" name="item_summary" size="50" maxlength="100" />
Project: <?php echo $response->value['projectList'][$_REQUEST['projectid']];?></td></tr>

            </table>
            <div id="taskfields1">
            <table>
            <tr>
               <td>
                  <label for="tasktype">Task Type</label>
               </td>
               <td>
                  <?php echo menuForArray($response->value['taskType'],"task_type");?>
               </td>
            </tr>
            <tr>
               <td>
                  <label for="productcategory">Category</label>
               </td>
               <td>
                  <?php echo menuForArray($response->value['category'],"product_category");?>
               </td>
            </tr>
            <tr>
               <td>
                  <label for="itemstatus">Status</label>
               </td>
               <td>
                  <?php echo menuForArray($response->value['status'],"item_status"); ?>
               </td>
            </tr>
            <tr>
               <td>
                  <label for="assigned">Assigned To</label>
               </td>
               <td>
                  <?php echo menuForArray($response->value['assignedUserList'],"assigned"); ?>
               </td>
            </tr>
            <tr>
               <td>
                  <label for="operatingsystem">Operating System</label>
               </td>
               <td>
                  <?php echo menuForArray($response->value['operatingSystem'],"operating_system"); ?>
               </td>
            </tr>
         </table>
      </div>
      <div id="taskfields2">
         <table>
            <tr>
               <td>
                     <label for="taskseverity">Severity</label>
               </td>
               <td>
                    <?php echo menuForArray($response->value['severity'],'task_severity'); ?>
               </td>
           </tr>
           <tr>
               <td>
                       <label for="task_priority">Priority</label>
               </td>
               <td>
                       <?php echo menuForArray($response->value['priority'],"task_priority"); ?>
               </td>
          </tr>
          <tr>

            <td>
               <label for="productversion">Reported Version</label>
            </td>
            <td>
               <?php echo menuForArray($response->value['reportedVersion'],"product_version"); ?>
            </td>
         </tr>
         <tr>
            <td>
                  <label for="closedbyversion">Due in Version</label>
            </td>
            <td>
                  <?php echo menuForArray($response->value['dueInVersion'],"closedby_version"); ?>
            </td>
         </tr>
         <tr>
            <td>
                  <label for="duedate">Due Date</label>
            </td>
            <td id="duedate">
                  <select id="due_date" name="due_date" >
                  <option value="0">Due anytime</option>
                  <option id="date_d">Select Due Date</option>
                  </select>
                  <script type="text/javascript">
                  Calendar.setup(
                                 {
            inputField  : "date_d",         // ID of the input field
            ifFormat    : "%d-%b-%Y",    // the date format
            displayArea : "date_d",       // The display field
            daFormat    : "%d-%b-%Y",
            button      : "date_d"       // ID of the button
                                 }
                                 );
            </script>

            </td>
         </tr>
      </table>
   </div>

   <div id="taskdetailsfull">
      <label for="details">Details</label>
      <textarea id="details" name="detailed_desc" cols="70" rows="10"></textarea>
   </div>
   <div id="uploadfilebox">
      Attach a file         <input type="file" class="file" size="55" name="userfile[]" /><br />
   </div>

   <button type="button" onclick="addUploadFields()">Select more files</button>

   <button type="submit" accesskey="s" name="buSubmit">Add this task</button>
            &nbsp;&nbsp;<input class="text" type="checkbox" name="notifyme" value="1" checked="checked" />Notify me whenever this task changes


   </form>
</div>

<?php
      } else {
            showResponse($response);
      }
   }

   if ("newTask" == $rpcAction) {

      $taskArray = $_REQUEST;

      //$taskArray['project_id'] = 1;



     // print "<pre>".print_r($taskArray,true)."</pre>";

      $response = remoteCall("fs.openTask",$taskArray);

      showResponse($response);

      if ($response->success) {
         $task_id = $response->value['task_id'];
         echo "<a href=\"index.php?do=details&id=$task_id\">View New Task</a>";
      }

   }

   if ("openTask" == $rpcAction) {

      $taskArray['item_summary'] = "test task summary";
      $taskArray['detailed_desc'] = "test task description";
      $taskArray['project_id'] = 1;
      $taskArray['task_type'] = 2;
      $taskArray['item_status'] = 2;
      $taskArray['assigned'] = 2;
      $taskArray['product_category'] = 2;
      $taskArray['product_version'] = 2;
      $taskArray['closedby_version'] = 2;
      $taskArray['operating_system'] = 2;
      $taskArray['task_severity'] = 1;
      $taskArray['task_priority'] = 2;
      $taskArray['due_date'] = 0;



      $response = remoteCall("fs.openTask",$taskArray);

      showResponse($response);
   }








   // End of script
}

function showResponse($response)
{

   // Display the results
   echo '<pre>';

   if ($response->success) {
      print_r($response->value);
   } else {
      print($response->message);
   }
   echo '</pre>';

   echo '<div><br /><br /><a href="remote-client.php">Try again</a></div>';
}

function menuForArray($array,$name)
{


   $result = "<select name=\"$name\" id=\"$name\">\n";

   //for($i=1;$i<=count($array);$i++) {
   foreach($array as $key => $value)
      $result .= "<option value=\"$key\">".$value."</option>\n";
   //}
   $result .= "</select>\n";

   return $result;
}

function remoteCall($name,$args=array())
{
   global $client;

   $params = array(new xmlrpcval($_REQUEST['username']),new xmlrpcval($_REQUEST['password']),php_xmlrpc_encode($args));
   $msg = new xmlrpcmsg($name, $params);

   $response = $client->send($msg);

   if ($response->faultCode() != 0) {

      $result->message = 'XML_RPC Error ('.$response->faultCode().') <br /> '.$response->faultString();
      if (isset($_REQUEST['debug']))  {
         $result->message .= "\n<br />".print_r($response,true);
      } else {
            $result->message .= "\n<br />Enable debug mode to see more details";
      }
     //$result->message .= '<br /><br /><a href="remote-client.php">Try again</a>';
      $result->response = $response;
      $result->success = false;
      return $result;
   } else {

      $result->message = "ok";
      $result->success = true;
      $result->response = $response;
      $result->value = php_xmlrpc_decode($response->value());
      return $result;

   }
}

echo "</div>";

?>

</div>
</body>
</html>
