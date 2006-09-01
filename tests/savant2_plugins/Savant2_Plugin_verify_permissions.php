<?php
require_once 'FlyspraySavant2Plugin.php';

/**
* 
* Verify user permissions
* 
* 
* @author Anders Betnér 
* 
* @package Savant2
* 
* @license LGPL http://www.gnu.org/copyleft/lesser.html
*
**/

class Savant2_Plugin_verify_permissions extends FlyspraySavant2Plugin {

   /**
   * 
   * Verifys the user permissions by looking at the "View Permissions" div
   * 
   * No actual test of the permissions will be performed, just a check that
   * flyspray reports these permissions in the "view permissions" div
   * @access public
   * 
   * @param $array array with none or more of permissions the user should have.
   * the following permissions are defined:
   * <pre>
   * array ('is admin', 'manage project', 'view tasks', 'open new tasks',
   *        'modify own tasks', 'modify all tasks', 'view comments',
   *        'add comments', ' edit comments', ' delete comments',
   *        'view attachments', 'create attachments', 'delete attachments',
   *        'view history', 'close own tasks', 'close other tasks',
   *        'assign to self', 'assign others to self', 'view reports', 'global view')
   * </pre>
   * @return string
   * 
   */
   function plugin($a_permissions) {
      $permissionList = array ('is admin', 'manage project', 'view tasks', 'open new tasks', 'modify own tasks', 'modify all tasks', 'view comments', 'add comments', ' edit comments', ' delete comments', 'view attachments', 'create attachments', 'delete attachments', 'view history', 'close own tasks', 'close other tasks', 'assign to self', 'assign others to self', 'view reports', 'global view');
      $html = $this->_createTR('click', 'link=View Permissions');
      $i = 1;
      foreach ($permissionList as $perm) {
         if (false === array_search($perm, $a_permissions)) {
            $state = 'No';
         } else {
            $state = 'Yes';
         }
         $html .= $this->_createTR('verifyText', "//div[@id='permissions']//tr[$i]", $perm.$state);
         $i ++;
      }
      $html .= $this->_createTR('click', 'link=View Permissions');
      return $html;
   }
}
?>