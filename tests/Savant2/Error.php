<?php

/**
* 
* Provides a simple error class for Savant.
*
* $Id: Error.php,v 1.2 2005/08/09 22:19:39 pmjones Exp $
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

class Savant2_Error {
	
	
	/**
	* 
	* The error code, typically a SAVANT_ERROR_* constant.
	* 
	* @access public
	*
	* @var int
	*
	*/
	
	var $code = null;
	
	
	/**
	* 
	* An array of error-specific information.
	* 
	* @access public
	*
	* @var array
	*
	*/
	
	var $info = array();
	
	
	/**
	* 
	* The error message text.
	*
	* @access public
	*
	* @var string
	*
	*/
	
	var $text = null;
	
	
	/**
	* 
	* A debug backtrace for the error, if any.
	*
	* @access public
	*
	* @var array
	*
	*/
	
	var $backtrace = null;
	
	
	/**
	* 
	* Constructor.
	*
	* @access public
	*
	* @param array $conf An associative array where the key is a
	* Savant2_Error property and the value is the value for that
	* property.
	*
	*/
	
	function Savant2_Error($conf = array())
	{
		// set public properties
		foreach ($conf as $key => $val) {
			$this->$key = $val;
		}
		
		// generate a backtrace
		if (function_exists('debug_backtrace')) {
			$this->backtrace = debug_backtrace();
		}
		
		// extended behaviors
		$this->error();
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
	
	function error()
	{
	}
}
?>