<?php
require_once 'FlyspraySavant2Plugin.php';

/**
* 
* Verify user
* 
* 
* @author Anders Betnér 
* 
* @package Savant2
* 
* @license LGPL http://www.gnu.org/copyleft/lesser.html
* 
*/

class Savant2_Plugin_verify_user extends FlyspraySavant2Plugin {

   /**
   * 
   * Verifies that a user exists with supplied data.
   * 
   * Only super users can use this method. The password can not be verified.
   * 
   * @access public
   * 
   * @param array $user associative array of user data
   * <pre>
   * $user['username'] = '';
   * $user['realname'] = '';
   * $user['email']    = '';
   * $user['jabberid'] = '';
   * $user['notification'] = ''; // one of none,email,jabber
   * $user['group']    = ''; // one of Admin,Developers,Reporters,Basic,
   *                         // Disabled + groups you have defined 
   * </pre> 
   * @return string html code
   * 
   */
   function plugin($user) {
      $html .= $this->_createTR('clickAndWait', 'link=Admin Toolbox');
      $html .= $this->_createTR('clickAndWait', 'link=Users and Groups');
      $html .= $this->_createTR('clickAndWait', 'link='.$user['username']);
      if (!empty ($user['realname'])) {
         $html .= $this->_createTR('verifyValue', 'realname', $user['realname']);
      }
      if (!empty ($user['email'])) {
         $html .= $this->_createTR('verifyValue', 'emailaddress', $user['email']);
      }
      if (!empty ($user['notification'])) {
         $html .= $this->_createTR('verifySelected', 'notifytype',$user['notification']);
      }
      if (!empty ($user['jabberid'])) {
         $html .= $this->_createTR('verifyValue', 'jabberid', $user['jabberid']);
      }
      if (!empty ($user['group'])) {
         $html .= $this->_createTR('verifySelected', 'groupin', $user['group']);

      }
      return $html;
   }
}
?>