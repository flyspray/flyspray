<?php

/**
* Base plugin class.
*/
require_once 'Savant2/Plugin.php';

/**
* 
* Outputs a single <input> element.
* 
* $Id: Savant2_Plugin_input.php,v 1.2 2005/08/09 22:19:39 pmjones Exp $
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

class Savant2_Plugin_input extends Savant2_Plugin {
	
	/**
	* 
	* Outputs a single <input> element.
	* 
	* @access public
	* 
	* @param string $type The HTML "type=" value (e.g., 'text',
	* 'hidden', 'password').
	* 
	* @param string $name The HTML "name=" value.
	* 
	* @param mixed $value The initial value of the input element.
	* 
	* @param string $attr Any extra HTML attributes to place within the
	* input element.
	* 
	* @return string
	* 
	*/
	
	function plugin($type, $name, $value = '', $attr = null)
	{
		$type = htmlspecialchars($type);
		$name = htmlspecialchars($name);
		$value = htmlspecialchars($value);
		
		// start the tag
		$html = "<input type=\"$type\" name=\"$name\" value=\"$value\"";
		
		// add extra attributes
		if (is_array($attr)) {
			// add from array
			foreach ($attr as $key => $val) {
				$key = htmlspecialchars($key);
				$val = htmlspecialchars($val);
				$html .= " $key=\"$val\"";
			}
		} elseif (! is_null($attr)) {
			// add from scalar
			$html .= " $attr";
		}
		
		// end the tag and return
		$html .= ' />';
		return $html;
	}
}

?>