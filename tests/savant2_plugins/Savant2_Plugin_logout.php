<?php
require_once 'FlyspraySavant2Plugin.php';

/**
* 
* Logout current user
* 
* 
* @author Anders Betnér 
* 
* @package Savant2
* 
* @license LGPL http://www.gnu.org/copyleft/lesser.html
* 
*/

class Savant2_Plugin_logout extends FlyspraySavant2Plugin {

   /**
   * 
   * Logout the current user
   * 
   * If no user is logged in the selenium test will fail.
   * 
   * @access public
   * @return string html code
   * 
   */

   function plugin() {
      $html = $this->_createTR('clickAndWait', 'logoutlink');
      return $html;
   }
}
?>