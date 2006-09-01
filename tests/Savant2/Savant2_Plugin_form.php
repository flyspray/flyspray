<?php

/**
* Base plugin class.
*/

require_once 'Savant2/Plugin.php';


/**
* 
* Creates XHTML forms with CSS and table-based layouts.
* 
* $Id: Savant2_Plugin_form.php,v 1.5 2005/08/09 22:19:39 pmjones Exp $
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @package Savant2
* 
* @todo Add non-standard elements: date, time, hierselect, autocomplete
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

class Savant2_Plugin_form extends Savant2_Plugin {
	
	
	/**
	* 
	* The CSS class to use when generating form layout.
	* 
	* This class name will be applied to the following tags:
	* 
	* - div
	* - fieldset
	* - legend
	* - table
	* - tr
	* - th
	* - td
	* - label
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $class = '';
	
	
	/**
	* 
	* The default 'float' style for fieldset blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	var $float = '';
	
	
	/**
	* 
	* The default 'clear' style for fieldset blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	var $clear = '';
	
	
	/**
	* 
	* The sprintf() format for element notes in col-type blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	var $noteCol = '<span style="font-size: 80%%; font-style: italic;">%s</span>';
	
	
	/**
	* 
	* The sprintf() format for element notes in row-type blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	var $noteRow = '<span style="font-size: 80%%; font-style: italic;">%s</span>';
	
	
	/**
	* 
	* The text used to separate radio buttons in col-type blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	var $radioCol = '<br />';
	
	
	/**
	* 
	* The text used to separate radio buttons in row-type blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	var $radioRow = '&nbsp;&nbsp;';
	
	
	/**
	* 
	* The base number of tabs to use when tidying up the generated XHTML.
	* 
	* @access public
	* 
	* @var int
	* 
	*/
	
	var $tabBase = 2;
	
	
	/**
	* 
	* The sprintf() format for validation messages in col-type blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	var $validCol = '<br /><span style="color: red; font-size: 80%%;">%s</span>';
	
	
	/**
	* 
	* The sprintf() format for validation messages in col-type blocks.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	var $validRow = '<br /><span style="color: red; font-size: 80%%;">%s</span>';
	
	
	/**
	* 
	* Whether or not to automatically dispel magic quotes from values.
	* 
	* @access public
	* 
	* @var bool
	* 
	*/
	
	var $unquote = true;
	
	
	/**
	* 
	* Whether or not to use automatic layout.
	* 
	* @access public
	* 
	* @var bool
	* 
	*/
	
	var $layout = true;
	
	
	/**
	* 
	* The kind of fieldset block being generated ('col' or 'row').
	* 
	* @access private
	* 
	* @var bool
	* 
	*/
	
	var $_blockType = null;
	
	
	/**
	* 
	* The legend for the fieldset block, if any.
	* 
	* @access private
	* 
	* @var string
	* 
	*/
	
	var $_blockLabel = null;
	
	
	/**
	* 
	* Whether or not the form is generating elements within a fieldset block.
	* 
	* @access private
	* 
	* @var bool
	* 
	*/
	
	var $_inBlock = false;
	
	
	/**
	* 
	* Whether or not the form is generating elements as a group.
	* 
	* @access private
	* 
	* @var bool
	* 
	*/
	
	var $_inGroup = false;
	
	
	/**
	* 
	* The number of tabs to use before certain tags when tidying XHTML layout.
	* 
	* @access private
	* 
	* @var bool
	* 
	*/
	
	var $_tabs = array(
		'form'                  => 0,
		'/form'                 => 0,
		'div'                   => 1,
		'/div'                  => 1,
		'fieldset'              => 1,
		'/fieldset'             => 1,
		'legend'                => 2,
		'table'                 => 2,
		'/table'                => 2,
		'tr'                    => 3,
		'/tr'                   => 3,
		'th'                    => 4,
		'/th'                   => 4,
		'td'                    => 4,
		'/td'                   => 4,
		'label'                 => 5,
		'input type="button"'   => 5,
		'input type="checkbox"' => 5,
		'input type="file"'     => 5,
		'input type="hidden"'   => 5,
		'input type="image"'    => 5,
		'input type="password"' => 5,
		'input type="reset"'    => 5,
		'input type="submit"'   => 5,
		'input type="text"'     => 5,
		'textarea'              => 5,
		'select'                => 5,
		'/select'               => 5,
		'option'                => 6
	);
	
		
	/**
	* 
	* Central switcher API for the the various public methods.
	* 
	* @access public
	* 
	* @param string $method The public method to call from this class; all
	* additional parameters will be passed to the called method, and all
	* returns from the mehtod will be tidied.
	* 
	* @return string XHTML generated by the public method.
	* 
	*/
	
	function plugin($method)
	{
		// only pass calls to public methods (i.e., no leading underscore)
		if (substr($method, 0, 1) != '_' && method_exists($this, $method)) {
			
			// get all arguments and drop the first one (the method name)
			$args = func_get_args();
			array_shift($args);
			
			// call the method, then return the tidied-up XHTML results
			$xhtml = call_user_func_array(array(&$this, $method), $args);
			return $this->_tidy($xhtml);
		}
	}
	
	
	/**
	* 
	* Sets the value of a public property.
	* 
	* @access public
	* 
	* @param string $key The name of the property to set.
	* 
	* @param mixed $val The new value for the property.
	* 
	* @return void
	* 
	*/
	
	function set($key, $val)
	{
		if (substr($key, 0, 1) != '_' && isset($this->$key)) {
			$this->$key = $val;
		}
	}
	
	
	// ---------------------------------------------------------------------
	//
	// Form methods
	//
	// ---------------------------------------------------------------------
	
	
	/**
	* 
	* Starts the form.
	* 
	* The form defaults to 'action="$_SERVER['REQUEST_URI']"' and
	* 'method="post"', but you can override those, and add any other
	* attributes you like.
	* 
	* @access public
	* 
	* @param array|string $attr Attributes to add to the form tag.
	* 
	* @return A <form> tag.
	* 
	*/
	
	function start($attr = null)
	{
		// make sure there is at least an empty array of attributes
		if (is_null($attr)) {
			$attr = array();
		}
		
		// make sure there is a default action and method from
		// the attribute array.
		if (is_array($attr)) {
			
			// default action
			if (! isset($attr['action'])) {
				$attr['action'] = $_SERVER['REQUEST_URI'];
			}
			
			// default method
			if (! isset($attr['method'])) {
				$attr['method'] = 'post';
			}
			
			// default encoding
			if (! isset($attr['enctype'])) {
				$attr['enctype'] = 'multipart/form-data';
			}
		}
		
		// start the form
		$xhtml = '<form';
		$xhtml .= $this->_attr($attr) . ">";
		return $xhtml;
	}
	
	
	/**
	* 
	* Ends the form and closes any existing layout.
	* 
	* @access public
	* 
	* @return The ending layout XHTML and a </form> tag.
	* 
	*/
	
	function end()
	{
		$xhtml = '';
		$xhtml .= $this->group('end');
		$xhtml .= $this->block('end');
		return $xhtml . '</form>';
	}
	
	
	// ---------------------------------------------------------------------
	//
	// Element methods
	//
	// ---------------------------------------------------------------------
	
	
	/**
	* 
	* Generates a 'button' element.
	* 
	* @access public
	* 
	* @param string $name The element name.
	* 
	* @param mixed $value The element value.
	* 
	* @param string $label The element label.
	* 
	* @param array|string $attr Attributes for the element tag.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	function button($name, $value = null, $label = null, $attr = null,
		$validCode = null, $validMsg = null)
	{
		$xhtml = $this->_input('button', $name, $value, $attr);
		return $this->_element($label, $xhtml, $validCode, $validMsg);
	}
	
	
	/**
	* 
	* Generates a 'checkbox' element.
	* 
	* @access public
	* 
	* @param string $name The element name.
	* 
	* @param mixed $value The element value.
	* 
	* @param string $label The element label.
	* 
	* @param mixed $options If a scalar (single value), then value of the
	* checkbox when checked; if an array, element 0 is the value when
	* checked, and element 1 is the value when not-checked.
	* 
	* @param array|string $attr Attributes for the element tag.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	function checkbox($name, $value = null, $label = null, $options = null,
		$attr = null, $validCode = null, $validMsg = null)
	{
		if (is_null($options)) {
			$options = array(1, 0);
		} else {
			settype($options, 'array');
		}
		
		$options = $this->_unquote($options);
		
		if (isset($options[1])) {
			$xhtml = $this->_input('hidden', $name, $options[1]);
		} else {
			$xhtml = '';
		}
		
		$xhtml .= '<input type="checkbox"';
		$xhtml .= ' name="' . htmlspecialchars($name) . '"';
		$xhtml .= ' value="' . htmlspecialchars($options[0]) . '"';
		
		if ($value == $options[0]) {
			$xhtml .= ' checked="checked"';
		}
		
		$xhtml .= $this->_attr($attr);
		$xhtml .= ' />';
		return $this->_element($label, $xhtml, $validCode, $validMsg);
	}
	
	
	/**
	* 
	* Generates a 'file' element.
	* 
	* @access public
	* 
	* @param string $name The element name.
	* 
	* @param mixed $value The element value.
	* 
	* @param string $label The element label.
	* 
	* @param array|string $attr Attributes for the element tag.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	function file($name, $value = null, $label = null, $attr = null,
		$validCode = null, $validMsg = null)
	{
		$xhtml = $this->_input('file', $name, $value, $attr);
		return $this->_element($label, $xhtml, $validCode, $validMsg);
	}
	
	
	/**
	* 
	* Generates a 'hidden' element (no layout is generated).
	* 
	* @access public
	* 
	* @param string $name The element name.
	* 
	* @param mixed $value The element value.
	* 
	* @param array|string $attr Attributes for the element tag.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	function hidden($name, $value = null, $attr = null)
	{
		return $this->_input('hidden', $name, $value, $attr);
	}
	
	
	/**
	* 
	* Generates an 'image' element.
	* 
	* @access public
	* 
	* @param string $name The element name.
	* 
	* @param mixed $src The image HREF source.
	* 
	* @param string $label The element label.
	* 
	* @param array|string $attr Attributes for the element tag.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	function image($name, $src, $label = null, $attr = null, $validCode = null,
		$validMsg = null)
	{
		$xhtml = '<input type="image"';
		$xhtml .= ' name="' . htmlspecialchars($name) . '"';
		$xhtml .= ' src="' . htmlspecialchars($src) . '"';
		$xhtml .= $this->_attr($attr);
		$xhtml .= ' />';
		return $this->_element($label, $xhtml, $validCode, $validMsg);
	}
	
	
	/**
	* 
	* Generates a 'password' element.
	* 
	* @access public
	* 
	* @param string $name The element name.
	* 
	* @param mixed $value The element value.
	* 
	* @param string $label The element label.
	* 
	* @param array|string $attr Attributes for the element tag.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	function password($name, $value = null, $label = null, $attr = null,
		$validCode = null, $validMsg = null)
	{
		$xhtml = $this->_input('password', $name, $value, $attr);
		return $this->_element($label, $xhtml, $validCode, $validMsg);
	}
	
	
	/**
	* 
	* Generates a set of radio button elements.
	* 
	* @access public
	* 
	* @param string $name The element name.
	* 
	* @param mixed $value The radio value to mark as 'checked'.
	* 
	* @param string $label The element label.
	* 
	* @param array $options An array of key-value pairs where the array
	* key is the radio value, and the array value is the radio text.
	* 
	* @param array|string $attr Attributes added to each radio.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The radio buttons XHTML.
	* 
	*/
	
	function radio($name, $value = null, $label = null, $options = null,
		$attr = null, $validCode = null, $validMsg = null)
	{
		settype($options, 'array');
		$value = $this->_unquote($value);
		
		$list = array();
		foreach ($options as $optval => $optlabel) {
			$radio = '<label style="white-space: nowrap;"><input type="radio"';
			$radio .= ' name="' . htmlspecialchars($name) . '"';
			$radio .= ' value="' . htmlspecialchars($optval) . '"';
			
			if ($optval == $value) {
				$radio .= ' checked="checked"';
			}
			
			$radio .= ' />' . htmlspecialchars($optlabel) . '</label>';
			$list[] = $radio;
			
		}
		
		// pick the separator string
		if ($this->_inBlock && $this->_blockType == 'row') {
			$sep = $this->radioRow;
		} else {
			$sep = $this->radioCol;
		}
		
		// done!
		$xhtml = implode($sep, $list);
		return $this->_element($label, $xhtml, $validCode, $validMsg);
	}
	
	
	/**
	* 
	* Generates a 'reset' button.
	* 
	* @access public
	* 
	* @param string $name The element name.
	* 
	* @param mixed $value The element value.
	* 
	* @param string $label The element label.
	* 
	* @param array|string $attr Attributes for the element tag.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	function reset($name, $value = null, $label = null, $attr = null,
		$validCode = null, $validMsg = null)
	{
		$xhtml =  $this->_input('reset', $name, $value, $attr);
		return $this->_element($label, $xhtml, $validCode, $validMsg);
	}
	
	
	/**
	* 
	* Generates 'select' list of options.
	* 
	* @access public
	* 
	* @param string $name The element name.
	* 
	* @param mixed $value The option value to mark as 'selected'; if an 
	* array, will mark all values in the array as 'selected' (used for
	* multiple-select elements).
	* 
	* @param string $label The element label.
	* 
	* @param array $options An array of key-value pairs where the array
	* key is the radio value, and the array value is the radio text.
	* 
	* @param array|string $attr Attributes added to the 'select' tag.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The select tag and options XHTML.
	* 
	*/
	
	function select($name, $value = null, $label = null, $options = null,
		$attr = null, $validCode = null, $validMsg = null)
	{
		settype($value, 'array');
		settype($options, 'array');
		
		$value = $this->_unquote($value);
		
		$xhtml = '';
		$xhtml .= '<select name="' . htmlspecialchars($name) . '"';
		$xhtml .= $this->_attr($attr);
		$xhtml .= '>';
		
		$list = array();
		foreach ($options as $optval => $optlabel) {
			$opt = '<option value="' . htmlspecialchars($optval) . '"';
			$opt .= ' label="' . htmlspecialchars($optlabel) . '"';
			if (in_array($optval, $value)) {
				$opt .= ' selected="selected"';
			}
			$opt .= '>' . htmlspecialchars($optlabel) . "</option>";
			$list[] = $opt;
		}
		
		$xhtml .= implode('', $list);
		$xhtml .= '</select>';
		return $this->_element($label, $xhtml, $validCode, $validMsg);
	}
	
	
	/**
	* 
	* Generates a 'submit' button.
	* 
	* @access public
	* 
	* @param string $name The element name.
	* 
	* @param mixed $value The element value.
	* 
	* @param string $label The element label.
	* 
	* @param array|string $attr Attributes for the element tag.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	function submit($name, $value = null, $label = null, $attr = null,
		$validCode = null, $validMsg = null)
	{
		$xhtml =  $this->_input('submit', $name, $value, $attr);
		return $this->_element($label, $xhtml, $validCode, $validMsg);
	}
	
	
	/**
	* 
	* Adds a note to the form.
	* 
	* @access public
	* 
	* @param string $text The note text.
	* 
	* @param string $label The label, if any, for the note.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	function note($text, $label = null, $validCode = null, $validMsg = null)
	{
		// pick the format
		if ($this->_inBlock && $this->_blockType == 'row') {
			$format = $this->noteRow;
		} else {
			$format = $this->noteCol;
		}
		
		// don't show the format when there's no note
		if (trim($text) == '') {
			$xhtml = '';
		} else {
			$xhtml = sprintf($format, $text);
		}
		
		// format and return
		return $this->_element($label, $xhtml, $validCode, $validMsg);
	}
	
	
	/**
	* 
	* Generates a 'text' element.
	* 
	* @access public
	* 
	* @param string $name The element name.
	* 
	* @param mixed $value The element value.
	* 
	* @param string $label The element label.
	* 
	* @param array|string $attr Attributes for the element tag.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	function text($name, $value = null, $label = null, $attr = null,
		$validCode = null, $validMsg = null)
	{
		$xhtml = $this->_input('text', $name, $value, $attr);
		return $this->_element($label, $xhtml, $validCode, $validMsg);
	}
	
	
	/**
	* 
	* Generates a 'textarea' element.
	* 
	* @access public
	* 
	* @param string $name The element name.
	* 
	* @param mixed $value The element value.
	* 
	* @param string $label The element label.
	* 
	* @param array|string $attr Attributes for the element tag.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The element XHTML.
	* 
	*/
	
	function textarea($name, $value = null, $label = null, $attr = null,
		$validCode = null, $validMsg = null)
	{
		$value = $this->_unquote($value);
		$xhtml = '';
		$xhtml .= '<textarea name="' . htmlspecialchars($name) . '"';
		$xhtml .= $this->_attr($attr);
		$xhtml .= '>' . htmlspecialchars($value) . '</textarea>';
		return $this->_element($label, $xhtml, $validCode, $validMsg);
	}
	
	
	// ---------------------------------------------------------------------
	//
	// Layout methods
	//
	// ---------------------------------------------------------------------
	
	
	/**
	* 
	* Builds XHTML to start, end, or split layout blocks.
	* 
	* @param string $action Whether to 'start', 'split', or 'end' a block.
	* 
	* @param string $label The fieldset legend.  If an empty string,
	* builds a fieldset with no legend; if null, builds a div (not a
	* fieldset).
	* 
	* @param string $type The layout type to use, 'col' or 'row'.  The
	* 'col' layout uses a left-column for element labels and a
	* right-column for the elements; the 'row' layout shows the elements
	* left-to-right, with the element label over the element, all in a
	* single row.
	* 
	* @param string $float Whether the block should float 'left' or
	* 'right' (set to an empty string if you don't want floating). 
	* Defaults to the value of $this->float.
	* 
	* @param string $float Whether the block should be cleared of 'left'
	* or 'right' floating blocks (set to an empty string if you don't
	* want to clear).  Defaults to the value of $this->clear.
	* 
	* @return string The appropriate XHTML for the block action.
	* 
	*/
	
	function block($action = 'start', $label = null, $type = 'col', 
		$float = null, $clear = null)
	{
		if (is_null($float)) {
			$float = $this->float;
		}
		
		if (is_null($clear)) {
			$clear = $this->clear;
		}
		
		switch (strtolower($action)) {
		
		case 'start':
			return $this->_blockStart($label, $type, $float, $clear);
			break;
		
		case 'split':
			return $this->_blockSplit();
			break;
		
		case 'end':
			return $this->_blockEnd();
			break;
		
		}
		
		return;
	}
	
	/**
	* 
	* Builds the layout for a group of elements; auto-starts a block if needed.
	* 
	* @access public
	* 
	* @param string $type Whether to 'start' or 'end' the group.
	* 
	* @param string $label The label for the group.
	* 
	* @return string The element-group layout XHTML.
	* 
	*/
	
	function group($type, $label = null)
	{
		// the XHTML to return
		$xhtml = '';
		
		// if not using automated layout, stop now.
		if (! $this->layout) {
			return $xhtml;
		}
		
		// if not in a block, start one
		if (! $this->_inBlock) {
			$xhtml .= $this->block();
		}
		
		// are we starting a new group?
		if ($type == 'start' && ! $this->_inGroup) {
			
			// build a 'col' group?
			if ($this->_blockType == 'col') {
				$xhtml .= $this->_tag('tr');
				$xhtml .= $this->_tag('th');
				
				// add a label if specified
				if (! is_null($label)) {
					$xhtml .= $this->_tag('label');
					$xhtml .= htmlspecialchars($label);
					$xhtml .= '</label>';
				}
				$xhtml .= '</th>';
				$xhtml .= $this->_tag('td');
			}
		
			// build a 'row' group?
			if ($this->_blockType == 'row') {
				$xhtml .= $this->_tag('td');
				if (! is_null($label)) {
					$xhtml .= $this->_tag('label');
					$xhtml .= htmlspecialchars($label);
					$xhtml .= '</label><br />';
				}
			}
			
			// we're in a group now
			$this->_inGroup = true;
			
		}
		
		// are we ending a current group?
		if ($type == 'end' && $this->_inGroup) {
			
			// we're out of the group now
			$this->_inGroup = false;
			
			if ($this->_blockType == 'col') {
				$xhtml .= '</td></tr>';
			}
			
			if ($this->_blockType == 'row') {
				$xhtml .= '</td>';
			}
		}
		
		// done!
		return $xhtml;
	}
	
	
	// ---------------------------------------------------------------------
	//
	// Private support methods
	//
	// ---------------------------------------------------------------------
	
	
	/**
	* 
	* Builds an attribute string for a tag.
	* 
	* @access private
	* 
	* @param array|string $attr The attributes to add to a tag; if an array,
	* the key is the attribute name and the value is the attribute value; if a
	* string, adds the literal string to the tag.
	* 
	* @return string A string of tag attributes.
	* 
	*/
	
	function _attr($attr = null)
	{
		if (is_array($attr)) {
			// add from array
			$xhtml = '';
			foreach ($attr as $key => $val) {
				$key = htmlspecialchars($key);
				$val = htmlspecialchars($val);
				$xhtml .= " $key=\"$val\"";
			}
		} elseif (! is_null($attr)) {
			// add from scalar
			$xhtml = " $attr";
		} else {
			$xhtml = null;
		}
		
		return $xhtml;
	}
	
	
	/**
	* 
	* Builds an XHTML opening tag with class and attributes.
	* 
	* @access private
	* 
	* @param string $type The tag type ('td', 'th', 'div', etc).
	* 
	* @param array|string $attr Additional attributes for the tag.
	* 
	* @return string The opening tag XHTML.
	* 
	*/
	
	function _tag($type, $attr = null)
	{
		// open the tag
		$xhtml = '<' . $type;
		
		// add a CSS class attribute
		if ($this->class) {
			$xhtml .= ' class="' . $this->class . '"';
		}
		
		// add other attributes
		$xhtml .= $this->_attr($attr);
		
		// done!
		return $xhtml . ">";
	}
	
	
	/**
	* 
	* Adds an element to the table layout; auto-starts a block as needed.
	* 
	* @access private
	* 
	* @param string $label The label for the element.
	* 
	* @param string $fieldXhtml The XHTML for the element field.
	* 
	* @param mixed $validCode A validation code.  If exactly boolean
	* true, or exactly null, no validation message will be displayed. 
	* If any other integer, string, or array value, the element is
	* treated as not-valid and will display the corresponding message.
	* 
	* @param mixed array|string $validMsg A validation message.  If an
	* array, the $validCode value is used as a key for this array to
	* determine which message(s) should be displayed.
	* 
	* @return string The element layout XHTML.
	* 
	*/
	
	function _element($label, $fieldXhtml, $validCode = null, $validMsg = null)
	{
		// the XHTML to return
		$xhtml = '';
		
		// if we're starting an element without having started
		// a block first, forcibly start a default block
		if (! $this->_inBlock) {
		
			// is there a label for the element?
			if (is_null($label)) {
				// not in a block, and no label specified. this is most
				// likely a hidden element above the form itself. just
				// return the XHTML as it is, no layout at all.
				return $fieldXhtml;
			} else {
				// start a block and continue
				$xhtml .= $this->block();
			}
		}
		
		// are we checking validation and adding validation messages?
		if ($validCode === null || $validCode === true) {
		
			// do nothing
			
		} else {
		
			// force to arrays so we can have multiple messages.
			settype($validCode, 'array');
			settype($validMsg, 'array');
			
			// pick the format
			if ($this->_inBlock && $this->_blockType == 'row') {
				$format = $this->validRow;
			} else {
				$format = $this->validCol;
			}
			
			// add the validation messages
			foreach ($validCode as $code) {
				if (isset($validMsg[$code])) {
					// print the message
					$fieldXhtml .= sprintf(
						$format,
						$validMsg[$code]
					);
				} else {
					// print the code
					$fieldXhtml .= sprintf(
						$format,
						$code
					);
				}
			}
		}
		
		// are we in a group?
		if (! $this->_inGroup) {
			// no, put the element in a group by itself
			$xhtml .= $this->group('start', $label);
			$xhtml .= $fieldXhtml;
			$xhtml .= $this->group('end');
		} else {
			// yes, just add the element to the current group.
			// elements in groups do not get their own labels,
			// the group has already set the label.
			$xhtml .= $fieldXhtml;
		}
		
		// done!
		return $xhtml;
	}
	
	
	/**
	* 
	* Recursively removes magic quotes from values and arrays.
	* 
	* @access private
	* 
	* @param mixed $value The value from which to remove magic quotes.
	* 
	* @return mixed The un-quoted value.
	* 
	*/
	
	function _unquote($value)
	{
		if (! $this->unquote) {
			return $value;
		}
		
		static $mq;
		if (! isset($mq)) {
			$mq = get_magic_quotes_gpc() || get_magic_quotes_runtime();
		}
		
		if ($mq) {
			if (is_array($value)) {
				foreach ($value as $k => $v) {
					$value[$k] = $this->_unquote($v);
				}
			} else {
				$value = stripslashes($value);
			}
		}
		
		return $value;
	}
	
	
	/**
	* 
	* Builds an 'input' element.
	* 
	* @access private
	* 
	* @param string $type The input type ('text', 'hidden', etc).
	* 
	* @param string $name The element name.
	* 
	* @param mixed $value The element value.
	* 
	* @param array|string $attr Attributes for the element tag.
	* 
	* @return The 'input' tag XHTML.
	* 
	*/
	
	function _input($type, $name, $value = null, $attr = null)
	{
		$value = $this->_unquote($value);
		$xhtml = '<input type="' . $type . '"';
		$xhtml .= ' name="' . htmlspecialchars($name) . '"';
		$xhtml .= ' value="' . htmlspecialchars($value) . '"';
		$xhtml .= $this->_attr($attr);
		$xhtml .= ' />';
		return $xhtml;
	}
	
	
	/**
	* 
	* Puts in newlines and tabs to make the source code readable.
	* 
	* @access private
	* 
	* @param string $xhtml The XHTML to tidy up.
	* 
	* @return string The tidied XHTML.
	* 
	*/
	
	function _tidy($xhtml)
	{
		// only tidy up if layout is turned on
		if ($this->layout) {
			foreach ($this->_tabs as $key => $val) {
				$key = '<' . $key;
				$pad = str_pad('', $val + $this->tabBase, "\t");
				$xhtml = str_replace($key, "\n$pad$key", $xhtml);
			}
		}
		return $xhtml;
	}
	
	
	/**
	* 
	* Generates XHTML to start a fieldset block.
	* 
	* @access private
	* 
	* @param string $label The fieldset legend.  If an empty string,
	* builds a fieldset with no legend; if null, builds a div (not a
	* fieldset).
	* 
	* @param string $type The layout type to use, 'col' or 'row'.  The
	* 'col' layout uses a left-column for element labels and a
	* right-column for the elements; the 'row' layout shows the elements
	* left-to-right, with the element label over the element, all in a
	* single row.
	* 
	* @param string $float Whether the block should float 'left' or
	* 'right' (set to an empty string if you don't want floating). 
	* Defaults to the value of $this->float.
	* 
	* @param string $float Whether the block should be cleared of 'left'
	* or 'right' floating blocks (set to an empty string if you don't
	* want to clear).  Defaults to the value of $this->clear.
	* 
	* @return string The XHTML to start a block.
	* 
	*/
	
	function _blockStart($label = null, $type = 'col', $float = null,
		$clear = null)
	{
		// the XHTML text to return.
		$xhtml = '';
		
		// if not using automated layout, stop now.
		if (! $this->layout) {
			return $xhtml;
		}
		
		// are we already in a block? if so, end the current one
		// so we can start a new one.
		if ($this->_inBlock) {
			$xhtml .= $this->block('end');
		}
		
		// set the new block type and label
		$this->_inBlock = true;
		$this->_blockType = $type;
		$this->_blockLabel = $label;
		
		// build up the "style" attribute for the new block
		$style = '';
		
		if ($float) {
			$style .= " float: $float;";
		}
		
		if ($clear) {
			$style .= " clear: $clear;";
		}
		
		if (! empty($style)) {
			$attr = 'style="' . trim($style) . '"';
		} else {
			$attr = null;
		}
		
		// build the block opening XHTML itself; use a fieldset when a label
		// is specifed, or a div when the label is not specified
		if (is_string($this->_blockLabel)) {
		
			// has a label, use a fieldset with e style attribute
			$xhtml .=  $this->_tag('fieldset', $attr);
			
			// add the label as a legend, if it exists
			if (! empty($this->_blockLabel)) {
				$xhtml .=  $this->_tag('legend');
				$xhtml .= htmlspecialchars($this->_blockLabel);
				$xhtml .= '</legend>';
			}
			
		} else {
			// no label, use a div with the style attribute
			$xhtml .= $this->_tag('div', $attr);
		}
		
		// start a table for the block elements
		$xhtml .=  $this->_tag('table');
		
		// if the block is row-based, start a row
		if ($this->_blockType == 'row') {
			$xhtml .=  $this->_tag('tr');
		}
		
		// done!
		return $xhtml;
	}
	
	
	/**
	* 
	* Generates the XHTML to end a block.
	* 
	* @access public
	* 
	* @return string The XHTML to end a block.
	* 
	*/
	
	function _blockEnd()
	{
		// the XHTML to return
		$xhtml = '';
		
		// if not using automated layout, stop now.
		if (! $this->layout) {
			return $xhtml;
		}
		
		// if not in a block, return right away
		if (! $this->_inBlock) {
			return;
		}
		
		// are we in a group?  if so, end it.
		if ($this->_inGroup) {
			$xhtml .= $this->group('end');
		}
		
		// end the block layout proper
		if ($this->_blockType == 'row') {
			// previous block was type 'row'
			$xhtml .=  '</tr></table>';
		} else {
			// previous block was type 'col'
			$xhtml .=  '</table>';
		}
		
		// end the fieldset or div tag for the block
		if (is_string($this->_blockLabel)) {
			// there was a label, so the block used fieldset
			$xhtml .=  '</fieldset>';
		} else {
			// there was no label, so the block used div
			$xhtml .=  '</div>';
		}
		
		// reset tracking properties
		$this->_inBlock = false;
		$this->_blockType = null;
		$this->_blockLabel = null;
		
		// done!
		return $xhtml;
	}
	
	
	/**
	* 
	* Generates the layout to split the layout within a block.
	* 
	* @access public
	* 
	* @return string The XHTML to split the layout with in a block.
	* 
	*/
	
	function _blockSplit()
	{
		// the XHTML to return
		$xhtml = '';
		
		// if not using automated layout, stop now.
		if (! $this->layout) {
			return $xhtml;
		}
		
		// not already in a block, so don't bother.
		if (! $this->_inBlock) {
			return;
		}
		
		// end any group we might already be in
		if ($this->_inGroup) {
			$xhtml .= $this->group('end');
		}
		
		// end the current block and start a new one
		switch ($this->_blockType) {
		
		case 'row':
			$xhtml .= '</tr>';
			$xhtml .= $this->_tag('tr');
			break;
		
		case 'col':
			$xhtml .= '</table>';
			$xhtml .= $this->_tag('table');
			break;
		}
		
		// done!
		return $xhtml;
	}
}

?>