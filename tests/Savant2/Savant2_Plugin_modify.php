<?php

/**
* Base plugin class.
*/
require_once 'Savant2/Plugin.php';

/**
* 
* Modifies a value with a series of functions.
* 
* $Id: Savant2_Plugin_modify.php,v 1.2 2005/08/09 22:19:39 pmjones Exp $
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

class Savant2_Plugin_modify extends Savant2_Plugin {
	
	/**
	* 
	* Modifies a value with a series of functions.
	* 
	* Allows you to pass a space-separated list of value-manipulation
	* functions so that the value is "massaged" before output. For
	* example, if you want to strip slashes, force to lower case, and
	* convert to HTML entities (as for an input text box), you might do
	* this:
	* 
	* $this->modify($value, 'stripslashes strtolower htmlentities');
	* 
	* @param object &$savant A reference to the calling Savant2 object.
	* 
	* @access public
	* 
	* @param string $value The value to be printed.
	* 
	* @param string $functions A space-separated list of
	* single-parameter functions to be applied to the $value before
	* printing.
	* 
	* @return string
	* 
	*/
	
	function plugin($value, $functions = null)
	{
		// is there a space-delimited function list?
		if (is_string($functions)) {
			
			// yes.  split into an array of the
			// functions to be called.
			$list = explode(' ', $functions);
			
			// loop through the function list and
			// apply to the output in sequence.
			foreach ($list as $func) {
				if (function_exists($func)) {
					$value = $func($value);
				}
			}
		}
		
		return $value;
	}

}
?>