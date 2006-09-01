<?php

/**
* The base Savant2_Error class.
*/
require_once 'Savant2/Error.php';

/**
* The PEAR_ErrorStack class.
*/
require_once 'PEAR/ErrorStack.php';

/**
* 
* Provides an interface to PEAR_ErrorStack class for Savant.
*
* $Id: Savant2_Error_stack.php,v 1.2 2005/08/09 22:19:39 pmjones Exp $
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

class Savant2_Error_stack extends Savant2_Error {
	
	
	/**
	* 
	* Pushes an error onto the PEAR_ErrorStack.
	* 
	* @return void
	* 
	*/
	
	function error()
	{
		// push an error onto the stack
		PEAR_ErrorStack::staticPush(
			'Savant2',       // package name
			$this->code,    // error code
			null,           // error level
			$this->info,    // user info
			$this->text     // error message
		);
	}
}
?>