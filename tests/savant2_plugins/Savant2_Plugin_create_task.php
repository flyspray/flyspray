<?php
require_once 'FlyspraySavant2Plugin.php';

/**
* 
* Creates a new task
* 
* 
* @author Anders Betnér 
* @package Savant2
* 
* @license LGPL http://www.gnu.org/copyleft/lesser.html
*
**/

class Savant2_Plugin_create_task extends FlyspraySavant2Plugin {

   /**
   * 
   * Creates a new flyspray task.
   * 
   * The function takes an associative array with task data. You can provide
   * as many or few elements as you whish. The elements left out
   * will use their flyspray default values. (Just as if you didn't touch
   * them when entering the task by hand in flyspray)
   * To select a select box field use the english label for the option to select,
   * possible values are defined by flyspray.
   * 
   * @access public
   * @param array $opt associative array
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
   *
   * @return string html code
   * 
   */
   function plugin($opt) {

      $html = $this->_createTR('clickAndWait', 'newtasklink');
      if (!empty ($opt['summary'])) {
         $html .= $this->_createTR('type', 'itemsummary', $opt['summary']);
      }
      if (!empty ($opt['tasktype'])) {
         $html .= $this->_createTR('select', 'tasktype', $opt['tasktype']);
      }
      if (!empty ($opt['category'])) {
         $html .= $this->_createTR('select', 'productcategory', $opt['category']);
      }
      if (!empty ($opt['status'])) {
         $html .= $this->_createTR('select', 'itemstatus', $opt['status']);
      }
      if (!empty ($opt['assignedto'])) {
         $html .= $this->_createTR('select', 'assignedto', $opt['assignedto']);
      }
      if (!empty ($opt['os'])) {
         $html .= $this->_createTR('select', 'operatingsystem', $opt['os']);
      }
      if (!empty ($opt['severity'])) {
         $html .= $this->_createTR('select', 'taskseverity', $opt['severity']);
      }
      if (!empty ($opt['priority'])) {
         $html .= $this->_createTR('select', 'task_priority', $opt['priority']);
      }
      if (!empty ($opt['version'])) {
         $html .= $this->_createTR('select', 'productversion', $opt['reportedversion']);
      }
      if (!empty ($opt['dueversion'])) {
         $html .= $this->_createTR('select', 'closedbyversion', $opt['dueversion']);
      }
      if (!empty ($opt['due'])) {
         $html .= $this->_createTR('type', 'duedatehidden', $opt['due']);
      }
      if (!empty ($opt['details'])) {
         $html .= $this->_createTR('type', 'details', $opt['details']);
      }
      $html .= $this->_createTR('clickAndWait', 'buSubmit');
      return $html;
   }
}
?>