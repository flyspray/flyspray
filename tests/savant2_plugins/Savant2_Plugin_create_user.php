<?php
require_once 'FlyspraySavant2Plugin.php';

/**
* 
* Creates a user
* 
* 
* @author Anders Betnér 
* 
* @package Savant2
* 
* @license LGPL http://www.gnu.org/copyleft/lesser.html
* 
*/

class Savant2_Plugin_create_user extends FlyspraySavant2Plugin {

   /**
   * 
   * Creates a user
   *
   * The function takes an associative array of user data. You can provide
   * as many or few elements as you whish.
   *    
   * @access public
   * 
   * @param array $user associative array of user data
   * <pre>
   * $user['username'] = '';
   * $user['password'] = '';
   * $user['realname'] = '';
   * $user['email']    = '';
   * $user['jabberid'] = '';
   * $user['notification'] = ''; // one of none,email,jabber
   * $user['group']    = ''; // one of Admin,Developers,Reporters,Basic,
   *                         // Disabled or groups defined in flyspray 
   * </pre> 
   * @return string html code
   * 
   */
   function plugin($user) {
      $html .= $this->_createTR('clickAndWait', 'link=Admin Toolbox');
      $html .= $this->_createTR('clickAndWait', 'link=Users and Groups');
      $html .= $this->_createTR('clickAndWait', 'link=Register New User');
      if (!empty ($user['username'])) {
         $html .= $this->_createTR('type', 'username', $user['username']);
      }
      if (!empty ($user['password'])) {
         $html .= $this->_createTR('type', 'userpass', $user['password']);
         $html .= $this->_createTR('type', 'userpass2', $user['password']);
      }
      if (!empty ($user['realname'])) {
         $html .= $this->_createTR('type', 'realname', $user['realname']);
      }
      if (!empty ($user['email'])) {
         $html .= $this->_createTR('type', 'emailaddress', $user['email']);
      }
      if (!empty ($user['notification'])) {
         switch ($user['notification']) {
            case 'email' :
               $radioValue = '1';
               break;
            case 'jabber' :
               $radioValue = '2';
               break;
            default :
               $radioValue = '0';
         }
         $html .= $this->_createTR('click', '//input[@type="radio" and @name="notify_type" and @value="'.$radioValue.'"]');
      }
      if (!empty ($user['jabberid'])) {
         $html .= $this->_createTR('type', 'jabberid', $user['jabberid']);
      }
      if (!empty ($user['group'])) {
         $html .= $this->_createTR('select', 'groupin', $user['group']);

      }
      $html .= $this->_createTR('clickAndWait', 'buSubmit');
      return $html;
   }
}
?>