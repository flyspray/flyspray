<?php

/**
* Base plugin class.
*/
require_once 'Savant2/Plugin.php';

/**
* 
* Outputs a single checkbox <input> element.
* 
* $Id: Savant2_Plugin_checkbox.php,v 1.2 2005/08/09 22:19:39 pmjones Exp $
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

class Savant2_Plugin_checkbox extends Savant2_Plugin {

	/**
	* 
	* Outputs a single checkbox <input> element.
	* 
	* @access public
	* 
	* @param string $name The HTML "name=" value for the checkbox.
	* 
	* @param string $value The value of the checkbox when checked.
	* 
	* @param array $checked If $value is in this array of values,
	* mark the checkbox as checked.
	* 
	* @param array $default The value to return if the checkbox is not
	* checked.
	* 
	* @param string|array $attr Any extra HTML attributes to place
	* within the checkbox element.
	* 
	* @return string
	* 
	*/
	
	function plugin(
		$name,
		$value = '1',
		$checked = null,
		$default = null,
		$attr = null)
	{
		$html = '';
		
		// define the hidden default value (if any) when not checked
		if (! is_null($default)) {
			$html .= '<input type="hidden"';
			$html .= ' name="' . htmlspecialchars($name) . '"';
			$html .= ' value="' .htmlspecialchars($default) . '" />';
			$html .= "\n";
		}
		
		// start the checkbox tag with name and value
		$html .= '<input type="checkbox"';
		$html .= ' name="' . htmlspecialchars($name) . '"';
		$html .= ' value="' . htmlspecialchars($value) . '"';
		
		// is the checkbox checked?
		settype($checked, 'array');
		if (in_array($value, $checked)) {
			$html .= ' checked="checked"';
		}
		
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
		
		// close the checkbox tag and return
		$html .= ' />';
		return $html;
	}
}
?>