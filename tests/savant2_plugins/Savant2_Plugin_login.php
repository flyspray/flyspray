<?php
require_once 'FlyspraySavant2Plugin.php';

/**
* 
* Login a user
* 
* 
* @author Anders Betnér 
* 
* @package Savant2
* 
* @license LGPL http://www.gnu.org/copyleft/lesser.html
* 
*/

class Savant2_Plugin_login extends FlyspraySavant2Plugin {

   /**
   * 
   * Log in the user
   * 
   * If there is a user already logged in this selenium test will fail.
   * 
   * @access public
   * 
   * @param string $user username
   * @param string $password password
   * 
   * @return string
   * 
   */

   function plugin($user, $password) {
      $html  = $this->_createTR('open', '/');
      $html .= $this->_createTR('type', 'user_name', $user);
      $html .= $this->_createTR('type', 'password', $password);
      $html .= $this->_createTR('clickAndWait', "//input[@value='Login!']");
      return $html;
   }
}
?>