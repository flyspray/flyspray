<?php

/**
* Base plugin class.
*/
require_once 'Savant2/Plugin.php';

/**
* 
* Outputs a set of radio <input>s with the same name.
* 
* $Id: Savant2_Plugin_radios.php,v 1.2 2005/08/09 22:19:39 pmjones Exp $
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

class Savant2_Plugin_radios extends Savant2_Plugin {
	
	
	/**
	* 
	* Outputs a set of radio <input>s with the same name.
	* 
	* @access public
	* 
	* @param string $name The HTML "name=" value of all the radio <input>s.
	* 
	* @param array $radios An array of key-value pairs where the key is the
	* radio button value and the value is the radio button label.
	* 
	* @param string $checked A comparison string; if any of the $option
	* element values and $checked are the same, that radio button will
	* be marked as "checked" (otherwise not).
	* 
	* @param array $default The value to return if no radio buttons are
	* checked.
	* 
	* @param string|array $attr Any extra HTML attributes to place
	* within the checkbox element.
	* 
	* @param string $sep The HTML text to place between every radio
	* button in the set.
	* 
	* @return string
	* 
	*/
	
	function plugin(
		$name,
		$radios,
		$checked = null,
		$default = null,
		$sep = "<br />\n",
		$attr = null,
		$labelIsValue = false
	)
	{
		settype($radios, 'array');
		$html = '';
		
		// define the hidden default value (if any) when no buttons are checked
		if (! is_null($default)) {
			$html .= '<input type="hidden"';
			$html .= ' name="' . htmlspecialchars($name) . '"';
			$html .= ' value="' . htmlspecialchars($default) . '" />';
			$html .= "\n";
		}
		
		// the array of individual radio buttons
		$radio = array();
		
		// build the full set of radio buttons
		foreach ($radios as $value => $label) {
			
			// reset to blank HTML for this radio button
			$tmp = '';
			
			// is the label being used as the value?
			if ($labelIsValue) {
				$value = $label;
			}
			
			// start the radio button tag
			$tmp .= '<input type="radio"';
			$tmp .= ' name="' . htmlspecialchars($name) . '"';
			$tmp .= ' value="' . htmlspecialchars($value) . '"';
			
			// is the radio button selected?
			if ($value == $checked) {
				$tmp .= ' checked="checked"';
			}
			
			// add extra attributes
			if (is_array($attr)) {
				// add from array
				foreach ($attr as $key => $val) {
					$key = htmlspecialchars($key);
					$val = htmlspecialchars($val);
					$tmp .= " $key=\"$val\"";
				}
			} elseif (! is_null($attr)) {
				// add from scalar
				$tmp .= " $attr";
			}
			
			// add the label and save the button in the array
			$tmp .= ' />' . htmlspecialchars($label);
			$radio[] = $tmp;
		}
		
		// return the radio buttons with separators
		return $html . implode($sep, $radio);
	}
}
?>