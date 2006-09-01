<?php
require_once 'FlyspraySavant2Plugin.php';

/**
* 
* One line description
* 
* 
* @author Anders Betnér 
* 
* @package Savant2
* 
* @license LGPL http://www.gnu.org/copyleft/lesser.html
*
**/

class Savant2_Plugin_template extends FlyspraySavant2Plugin {

	/**
	* 
	* Full description
	* 
	* @access public
	* 
	* @param string $parameterName
	* @return string html code
	* 
	*/
	
	function plugin() {
      $html =  $this->_createTR();
      $html .= $this->_createTR('','');
		return $html;
	}
}
?>