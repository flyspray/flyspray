<?php

/**
* Base plugin class.
*/
require_once 'Savant2/Plugin.php';

/**
* 
* Outputs a series of HTML <option>s.
* 
* $Id: Savant2_Plugin_options.php,v 1.2 2005/08/09 22:19:39 pmjones Exp $
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

class Savant2_Plugin_options extends Savant2_Plugin {
	
	
	/**
	*
	* Outputs a series of HTML <option>s.
	* 
	* Outputs a series of HTML <option>s based on an associative array
	* where the key is the option value and the value is the option
	* label. You can pass a "selected" value as well to tell the
	* function which option value(s) should be marked as selected.
	* 
	* @access public
	* 
	* @param array $options An associative array of key-value pairs; the
	* key is the option value, the value is the option label.
	* 
	* @param string|array $selected A string or array that matches one
	* or more option values, to tell the function what options should be
	* marked as selected.  Defaults to an empty array.
	* 
	* @param string|array $attr Extra attributes to apply to the option
	* tag.  If a string, they are added as-is; if an array, the key is
	* the attribute name and the value is the attribute value.
	* 
	* @return string A set of HTML <option> tags.
	* 
	*/
	
	function plugin($options, $selected = array(), $attr = null,
		$labelIsValue = false)
	{
		$html = '';
		
		// force $selected to be an array.  this allows multi-selects to
		// have multiple selected options.
		settype($selected, 'array');
		settype($options, 'array');
		
		// loop through the options array
		foreach ($options as $value => $label) {
			
			// is the label being used as the value?
			if ($labelIsValue) {
				$value = $label;
			}
			
			// set the value and label in the tag
			$html .= '<option value="' . htmlspecialchars($value) . '"';
			$html .= ' label="' . htmlspecialchars($label) . '"';
			
			// is the option one of the selected values?
			if (in_array($value, $selected)) {
				$html .= ' selected="selected"';
			}
			
			// are we adding extra attributes?
			if (is_array($attr)) {
				// yes, from an array
				foreach ($attr as $key => $val) {
					$val = htmlspecialchars($val);
					$html .= " $key=\"$val\"";
				}
			} elseif (! is_null($attr)) {
				// yes, from a string
				$html .= ' ' . $attr;
			}
			
			// add the label and close the tag
			$html .= '>' . htmlspecialchars($label) . "</option>\n";
		}
		
		return $html;
	}
}
?>