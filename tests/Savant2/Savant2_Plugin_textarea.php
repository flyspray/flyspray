<?php

/**
* Base plugin class.
*/
require_once 'Savant2/Plugin.php';

/**
* 
* Outputs a single <textarea> element.
* 
* $Id: Savant2_Plugin_textarea.php,v 1.2 2005/08/09 22:19:39 pmjones Exp $
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

class Savant2_Plugin_textarea extends Savant2_Plugin {
	
	/**
	* 
	* Outputs a single <textarea> element.
	* 
	* @access public
	* 
	* @param string $name The HTML "name=" value.
	* 
	* @param string $text The initial value of the textarea element.
	* 
	* @param int $rows How many rows tall should the area be?
	* 
	* @param int $cols The many columns wide should the area be?
	* 
	* @param string $attr Any "extra" HTML code to place within the
	* checkbox element.
	* 
	* @return string
	* 
	*/
	
	function plugin($name, $text = '', $rows = 24, $cols = 80, $attr = null)
	{
		// start the tag
		$html = '<textarea name="' . htmlspecialchars($name) . '"';
		$html .= ' rows="' . htmlspecialchars($rows) . '"';
		$html .= ' cols="' . htmlspecialchars($cols) . '"';
		
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
		
		// add the default text, close the tag, and return
		$html .= '>' . htmlspecialchars($text) . '</textarea>';
		return $html;
	}
}

?>