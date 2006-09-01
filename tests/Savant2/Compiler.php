<?php

/**
* 
* Abstract Savant2_Compiler class.
* 
* You have to extend this class for it to be useful; e.g., "class
* Savant2_Plugin_example extends Savant2_Plugin".
* 
* $Id: Compiler.php,v 1.5 2005/08/09 22:19:39 pmjones Exp $
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @package Savant2
* 
* @license LGPL http://www.gnu.org/copyleft/lesser.html
* 
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU Lesser General Public License as
* published by the Free Software Foundation; either version 2.1 of the
* License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
*
*/

class Savant2_Compiler {
	
	/**
	* 
	* Reference to the "parent" Savant object.
	*
	*/
	
	var $Savant = null;
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	*/
	
	function Savant2_Compiler($conf = array())
	{
		settype($conf, 'array');
		foreach ($conf as $key => $val) {
			$this->$key = $val;
		}
	}
	
	
	/**
	* 
	* Stub method for extended behaviors.
	*
	* @access public
	* 
	* @return void
	*
	*/
	
	function compile($tpl)
	{
	}
}
?>