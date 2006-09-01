<?php
require_once 'FlyspraySavant2Plugin.php';

/**
* 
* Verifys that a task contains specified values
* 
* 
* @author Anders Betnér 
* 
* @package Savant2
* 
* @license LGPL http://www.gnu.org/copyleft/lesser.html
*
**/

class Savant2_Plugin_verify_task extends FlyspraySavant2Plugin {

   /**
   * 
   * Verifys that a task has the fields specified
   * 
   * You can provide as many or few of the fields as you like. Selenium finds
   * the task by typing the task id in the "Show task #" box
   * 
   * @access public
   * 
   * @param int $taskId The id of the task to verify
   * @param array $opt associative array of task fields to verify
   * <pre>
   * $task['summary']    = '';
   * $task['tasktype']   = '';
   * $task['category']   = '';
   * $task['status']     = '';
   * $task['assignedto'] = '';
   * $task['os']         = '';
   * $task['severity']   = '';
   * $task['priority']   = '';
   * $task['reportedversion'] = '';
   * $task['dueversion'] = '';
   * $task['due']        = ''; // use date format 19-oct-2005 
   * $task['details']    = '';
   * </pre>
   * @return string
   * 
   */

   function plugin($taskId,$opt) {
      $html  = $this->_createTR('type', 'show_task',$taskId);
      $html .= $this->_createTR('clickAndWait','//input[@value="Go!"]');
      if (!empty ($opt['summary'])) {
         $html .= $this->_createTR('verifyText', '//h2', "FS#$taskId &mdash; ".$opt['summary']);
      }
      if (!empty ($opt['tasktype'])) {
         $html .= $this->_createTR('verifyText', 'tasktype', $opt['tasktype']);
      }
      if (!empty ($opt['category'])) {
         $html .= $this->_createTR('verifyText', 'category', $opt['category']);
      }
      if (!empty ($opt['status'])) {
         $html .= $this->_createTR('verifyText', 'status', $opt['status']);
      }
      if (!empty ($opt['assignedto'])) {
         $html .= $this->_createTR('verifyText', 'assignedto', $opt['assignedto']);
      }
      if (!empty ($opt['os'])) {
         $html .= $this->_createTR('verifyText', 'os', $opt['os']);
      }
      if (!empty ($opt['severity'])) {
         $html .= $this->_createTR('verifyText', 'severity', $opt['severity']);
      }
      if (!empty ($opt['priority'])) {
         $html .= $this->_createTR('verifyText', 'priority', $opt['priority']);
      }
      if (!empty ($opt['version'])) {
         $html .= $this->_createTR('verifyText', 'reportedver', $opt['reportedversion']);
      }
      if (!empty ($opt['dueversion'])) {
         $html .= $this->_createTR('verifyText', 'dueversion', $opt['dueversion']);
      }
      if (!empty ($opt['due'])) {
         $html .= $this->_createTR('verifyText', 'duedate', $opt['due']);
      }
      if (!empty ($opt['details'])) {
         $html .= $this->_createTR('verifyText', 'taskdetailsfull', '*'.$opt['details']);
      }
      return $html;
   }
}
?>