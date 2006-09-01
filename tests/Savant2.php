<?php

/**
* 
* Error constants.
* 
*/

define('SAVANT2_ERROR_ASSIGN',       -1);
define('SAVANT2_ERROR_ASSIGNREF',    -2);
define('SAVANT2_ERROR_COMPILER',     -3);
define('SAVANT2_ERROR_NOFILTER',     -4);
define('SAVANT2_ERROR_NOPLUGIN',     -5);
define('SAVANT2_ERROR_NOSCRIPT',     -6);
define('SAVANT2_ERROR_NOTEMPLATE',   -7);
define('SAVANT2_ERROR_COMPILE_FAIL', -8);


/**
* 
* Error messages.
* 
*/

if (! isset($GLOBALS['_SAVANT2']['error'])) {
	$GLOBALS['_SAVANT2']['error'] = array(
		SAVANT2_ERROR_ASSIGN       => 'assign() parameters not correct',
		SAVANT2_ERROR_ASSIGNREF    => 'assignRef() parameters not correct',
		SAVANT2_ERROR_COMPILER     => 'compiler not an object or has no compile() method',
		SAVANT2_ERROR_NOFILTER     => 'filter file not found',
		SAVANT2_ERROR_NOPLUGIN     => 'plugin file not found',
		SAVANT2_ERROR_NOSCRIPT     => 'compiled template script file not found',
		SAVANT2_ERROR_NOTEMPLATE   => 'template source file not found',
		SAVANT2_ERROR_COMPILE_FAIL => 'template source failed to compile'
	);
}


/**
* 
* Provides an object-oriented template system.
* 
* Savant2 helps you separate model logic from view logic using PHP as
* the template language. By default, Savant2 does not compile templates.
* However, you may pass an optional compiler object to compile template
* source to include-able PHP code.
* 
* Please see the documentation at {@link http://phpsavant.com/}, and be
* sure to donate! :-)
* 
* $Id: Savant2.php,v 1.29 2005/09/11 22:42:24 pmjones Exp $
* 
* @author Paul M. Jones <pmjones@ciaweb.net>
* 
* @package Savant2
* 
* @version 2.4.0 stable
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

class Savant2 {
	
	
	/**
	* 
	* PHP5 ONLY: What method __call() will alias to.
	* 
	* Generally 'plugin' or 'splugin' (as __call() is intended for those).
	* 
	* @access private
	* 
	* @var string
	* 
	*/
	
	var $_call = 'plugin';
	
	
	/**
	* 
	* The custom compiler (pre-processor) object, if any.
	* 
	* @access private
	* 
	* @var object
	* 
	*/
	
	var $_compiler = null;
	
	
	/**
	* 
	* The class type to use when instantiating error objects.
	* 
	* @access private
	* 
	* @var string
	* 
	*/
	
	var $_error = null;
	
	
	/**
	* 
	* Array of callbacks used to escape output.
	* 
	* @access private
	* 
	* @var array
	* 
	* @see setEscape()
	* 
	* @see addEscape()
	* 
	* @see escape()
	* 
	* @see _()
	* 
	*/
	
	var $_escape = array('htmlspecialchars');
	
	
	/**
	* 
	* Whether or not to extract assigned variables into fetch() scope.
	* 
	* When true, all variables and references assigned to Savant2 are
	* extracted into the local scope of the template script at fetch()
	* time, and may be addressed as "$varname" instead of
	* "$this->varname".  The "$this->varname" notation will also work.
	* 
	* When false, you //must// use "$this->varname" in your templates to
	* address a variable instead of "$varname".  This has three
	* benefits: speed (no time spent extracting variables), memory use
	* (saves RAM by not making new references to variables), and clarity
	* (any $this->varname is obviously an assigned var, and vars created
	* within the template are not prefixed with $this).
	* 
	* @access private
	* 
	* @var bool
	* 
	*/
	
	var $_extract = false;
	
	
	/**
	* 
	* The output of the template script.
	* 
	* @access private
	* 
	* @var string
	* 
	*/
	
	var $_output = null;
	
	
	/**
	* 
	* The set of search directories for resources (plugins/filters) and
	* templates.
	* 
	* @access private
	* 
	* @var array
	* 
	*/
	
	var $_path = array(
		'resource' => array(),
		'template' => array()
	);
	
	
	/**
	* 
	* Array of resource (plugin/filter) object instances.
	* 
	* @access private
	* 
	* @var array
	* 
	*/
	
	var $_resource = array(
		'plugin' => array(),
		'filter' => array()
	);
	
	
	/**
	* 
	* Whether or not to automatically self-reference in plugins and filters.
	* 
	* @access private
	* 
	* @var bool
	* 
	*/
	
	var $_reference = false;
	
	
	/**
	* 
	* Whether or not to restrict template includes only to registered paths.
	* 
	* @access private
	* 
	* @var bool
	* 
	*/
	
	var $_restrict = false;
	
	
	/**
	* 
	* The path to the compiled template script file.
	* 
	* By default, the template source and template script are the same file.
	*
	* @access private
	* 
	* @var string
	* 
	*/
	
	var $_script = null;
	
	
	/**
	* 
	* The name of the default template source file.
	* 
	* @access private
	* 
	* @var string
	* 
	*/
	
	var $_template = null;
	
	
	// -----------------------------------------------------------------
	//
	// Constructor and general property setters
	//
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Constructor.
	* 
	* @access public
	* 
	* @param array $conf An associative array of configuration keys for
	* the Savant2 object.  Any, or none, of the keys may be set. The
	* keys are:
	* 
	* 'template_path' => The default path string or array of directories
	* to search for templates.
	* 
	* 'resource_path' => The default path string or array of directories
	* to search for plugin and filter resources.
	* 
	* 'error' => The custom error class that Savant2 should use
	* when returning errors.
	* 
	* 'extract' => Whether or not to extract variables into the local
	* scope when executing a template.
	* 
	* 'template' => The default template source name to use.
	* 
	*/
	
	function Savant2($conf = array())
	{
		// set the default template search dirs
		if (isset($conf['template_path'])) {
			// user-defined dirs
			$this->setPath('template', $conf['template_path']);
		} else {
			// default directory only
			$this->setPath('template', null);
		}
		
		// set the default filter search dirs
		if (isset($conf['resource_path'])) {
			// user-defined dirs
			$this->setPath('resource', $conf['resource_path']);
		} else {
			// default directory only
			$this->setPath('resource', null);
		}
		
		// set the error class
		if (isset($conf['error'])) {
			$this->setError($conf['error']);
		}
		
		// set the extraction flag
		if (isset($conf['extract'])) {
			$this->setExtract($conf['extract']);
		}
		
		// set the restrict flag
		if (isset($conf['restrict'])) {
			$this->setRestrict($conf['restrict']);
		}
		
		// set the Savant reference flag
		if (isset($conf['reference'])) {
			$this->setReference($conf['reference']);
		}
		
		// set the default template
		if (isset($conf['template'])) {
			$this->setTemplate($conf['template']);
		}
		
		// set the output escaping callbacks
		if (isset($config['escape'])) {
			call_user_func_array(
				array($this, 'setEscape'),
				(array) $config['escape']
			);
		}	
	}
	
	
	/**
	* 
	* Sets a custom compiler/pre-processor for template sources.
	* 
	* By default, Savant2 does not use a compiler; use this to set your
	* own custom compiler (pre-processor) for template sources.
	* 
	* @access public
	* 
	* @param object $compiler The compiler object; it must have a
	* "compile()" method.  If null or false, the current compiler object
	* is removed from Savant2.
	* 
	* @return void
	* 
	* @throws object An error object with a SAVANT2_ERROR_COMPILER code.
	* 
	*/
	
	function setCompiler(&$compiler)
	{
		if (! $compiler) {
			// nullify any current compiler
			$this->_compiler = null;
		} elseif (is_object($compiler) && method_exists($compiler, 'compile')) {
			// refer to a compiler object
			$this->_compiler =& $compiler;
		} else {
			// no usable compiler passed
			$this->_compiler = null;
			return $this->error(SAVANT2_ERROR_COMPILER);
		}
	}
	
	
	/**
	* 
	* Sets the method that __call() will alias to.
	* 
	* @access public
	* 
	* @param string $method The Savant2 method for __call() to alias to,
	* generally 'plugin' or 'splugin'.
	* 
	* @return void
	* 
	*/
	
	function setCall($method = 'plugin')
	{
		$this->_call = $method;
	}
	
	
	/**
	* 
	* Sets the custom error class for Savant2 errors.
	* 
	* @access public
	* 
	* @param string $error The name of the custom error class name; if
	* null or false, resets the error class to 'Savant2_Error'.
	* 
	* @return void
	* 
	*/
	
	function setError($error)
	{
		if (! $error) {
			$this->_error = null;
		} else {
			$this->_error = $error;
		}
	}
	
	
	/**
	*
	* Turns path checking on/off.
	* 
	* @access public
	*
	* @param bool $flag True to turn on path checks, false to turn off.
	*
	* @return void
	*
	*/
	
	function setRestrict($flag = false)
	{
		if ($flag) {
			$this->_restrict = true;
		} else {
			$this->_restrict = false;
		}
	}
	
	
	/**
	*
	* Turns extraction of variables on/off.
	* 
	* @access public
	*
	* @param bool $flag True to turn on extraction, false to turn off.
	*
	* @return void
	*
	*/
	
	function setExtract($flag = true)
	{
		if ($flag) {
			$this->_extract = true;
		} else {
			$this->_extract = false;
		}
	}
	
	
	/**
	*
	* Sets the automated Savant reference for plugins and filters.
	*
	* @access public
	*
	* @param bool $flag Whether to reference Savant2 or not.
	*
	* @return void
	*
	*/
	
	function setReference($flag = false)
	{
		$this->_reference = $flag;
	}
	
	
	/**
	*
	* Sets the default template name.
	*
	* @access public
	*
	* @param string $template The default template name.
	*
	* @return void
	*
	*/
	
	function setTemplate($template)
	{
		$this->_template = $template;
	}
	
	
	// -----------------------------------------------------------------
	//
	// Output escaping and management.
	//
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Clears then sets the callbacks to use when calling $this->escape().
	* 
	* Each parameter passed to this function is treated as a separate
	* callback.  For example:
	* 
	* <code>
	* $savant->setEscape(
	*     'stripslashes',
	*     'htmlspecialchars',
	*     array('StaticClass', 'method'),
	*     array($object, $method)
	* );
	* </code>
	* 
	* @access public
	*
	* @return void
	*
	*/
	
	function setEscape()
	{
		$this->_escape = func_get_args();
	}
	
	
	/**
	* 
	* Adds to the callbacks used when calling $this->escape().
	* 
	* Each parameter passed to this function is treated as a separate
	* callback.  For example:
	* 
	* <code>
	* $savant->addEscape(
	*     'stripslashes',
	*     'htmlspecialchars',
	*     array('StaticClass', 'method'),
	*     array($object, $method)
	* );
	* </code>
	* 
	* @access public
	*
	* @return void
	*
	*/
	
	function addEscape()
	{
		$args = func_get_args();
		$this->_escape = array_merge($this->_escape, $args);
	}
	
	
	/**
	*
	* Gets the array of output-escaping callbacks.
	*
	* @access public
	*
	* @return array The array of output-escaping callbacks.
	*
	*/
	
	function getEscape()
	{
		return $this->_escape;
	}
	
	
	/**
	*
	* Applies escaping to a value.
	* 
	* You can override the predefined escaping callbacks by passing
	* added parameters as replacement callbacks.
	* 
	* <code>
	* // use predefined callbacks
	* $result = $savant->escape($value);
	* 
	* // use replacement callbacks
	* $result = $savant->escape(
	*     $value,
	*     'stripslashes',
	*     'htmlspecialchars',
	*     array('StaticClass', 'method'),
	*     array($object, $method)
	* );
	* </code>
	* 
	* @access public
	* 
	* @param mixed $value The value to be escaped.
	* 
	* @return mixed
	*
	*/
	
	function escape($value)
	{
		// were custom callbacks passed?
		if (func_num_args() == 1) {
		
			// no, only a value was passed.
			// loop through the predefined callbacks.
			foreach ($this->_escape as $func) {
				$value = call_user_func($func, $value);
			}
			
		} else {
		
			// yes, use the custom callbacks instead.
			$callbacks = func_get_args();
			
			// drop $value
			array_shift($callbacks);
			
			// loop through custom callbacks.
			foreach ($callbacks as $func) {
				$value = call_user_func($func, $value);
			}
			
		}
		
		return $value;
	}
	
	
	/**
	*
	* Prints a value after escaping it for output.
	* 
	* You can override the predefined escaping callbacks by passing
	* added parameters as replacement callbacks.
	* 
	* <code>
	* // use predefined callbacks
	* $this->_($value);
	* 
	* // use replacement callbacks
	* $this->_(
	*     $value,
	*     'stripslashes',
	*     'htmlspecialchars',
	*     array('StaticClass', 'method'),
	*     array($object, $method)
	* );
	* </code>
	* 
	* @access public
	* 
	* @param mixed $value The value to be escaped and printed.
	* 
	* @return void
	*
	*/
	
	function eprint($value)
	{
		$args = func_get_args();
		echo call_user_func_array(
			array($this, 'escape'),
			$args
		);
	}
	
	
	/**
	*
	* Alias to eprint() and identical in every way.
	* 
	* @access public
	* 
	* @param mixed $value The value to be escaped and printed.
	* 
	* @return void
	*
	*/
	
	function _($value)
	{
		$args = func_get_args();
		return call_user_func_array(
			array($this, 'eprint'),
			$args
		);
	}
	
	
	
	// -----------------------------------------------------------------
	//
	// Path management and file finding
	//
	// -----------------------------------------------------------------
	
	
	/**
	*
	* Sets an entire array of search paths.
	*
	* @access public
	*
	* @param string $type The type of path to set, typcially 'template'
	* or 'resource'.
	* 
	* @param string|array $new The new set of search paths.  If null or
	* false, resets to the current directory only.
	*
	* @return void
	*
	*/
	
	function setPath($type, $new)
	{
		// clear out the prior search dirs
		$this->_path[$type] = array();
		
		// convert from string to path
		if (is_string($new) && ! strpos('://', $new)) {
			// the search config is a string, and it's not a stream
			// identifier (the "://" piece), add it as a path
			// string.
			$new = explode(PATH_SEPARATOR, $new);
		} else {
			// force to array
			settype($new, 'array');
		}
		
		// always add the fallback directories as last resort
		switch (strtolower($type)) {
		case 'template':
			$this->addPath($type, '.');
			break;
		case 'resource':
			$this->addPath($type, dirname(__FILE__) . '/Savant2/');
			break;
		}
		
		// actually add the user-specified directories
		foreach ($new as $dir) {
			$this->addPath($type, $dir);
		}
	}
	
	
	/**
	*
	* Adds a search directory for templates.
	*
	* @access public
	*
	* @param string $dir The directory or stream to search.
	*
	* @return void
	*
	*/
	
	function addPath($type, $dir)
	{
		// no surrounding spaces allowed!
		$dir = trim($dir);
		
		// add trailing separators as needed
		if (strpos($dir, '://') && substr($dir, -1) != '/') {
			// stream
			$dir .= '/';
		} elseif (substr($dir, -1) != DIRECTORY_SEPARATOR) {
			// directory
			$dir .= DIRECTORY_SEPARATOR;
		}
		
		// add to the top of the search dirs
		array_unshift($this->_path[$type], $dir);
	}
	
	
	/**
	*
	* Gets the array of search directories for template sources.
	*
	* @access public
	*
	* @return array The array of search directories for template sources.
	*
	*/
	
	function getPath($type = null)
	{
		if (! $type) {
			return $this->_path;
		} else {
			return $this->_path[$type];
		}
	}
	
	
	/**
	* 
	* Searches a series of paths for a given file.
	* 
	* @param array $type The type of paths to search (template, plugin,
	* or filter).
	* 
	* @param string $file The file name to look for.
	* 
	* @return string|bool The full path and file name for the target file,
	* or boolean false if the file is not found in any of the paths.
	*
	*/
	
	function findFile($type, $file)
	{
		// get the set of paths
		$set = $this->getPath($type);
		
		// start looping through them
		foreach ($set as $path) {
			
			// get the path to the file
			$fullname = $path . $file;
			
			// are we doing path checks?
			if (! $this->_restrict) {
			
				// no.  this is faster but less secure.
				if (file_exists($fullname) && is_readable($fullname)) {
					return $fullname;
				}
				
			} else {
				
				// yes.  this is slower, but attempts to restrict
				// access only to defined paths.
				
				// is the path based on a stream?
				if (strpos('://', $path) === false) {
					// not a stream, so do a realpath() to avoid
					// directory traversal attempts on the local file
					// system. Suggested by Ian Eure, initially
					// rejected, but then adopted when the secure
					// compiler was added.
					$path = realpath($path); // needed for substr() later
					$fullname = realpath($fullname);
				}
				
				// the substr() check added by Ian Eure to make sure
				// that the realpath() results in a directory registered
				// with Savant so that non-registered directores are not
				// accessible via directory traversal attempts.
				if (file_exists($fullname) && is_readable($fullname) &&
					substr($fullname, 0, strlen($path)) == $path) {
					return $fullname;
				}
			}
		}
		
		// could not find the file in the set of paths
		return false;
	}
	
	
	// -----------------------------------------------------------------
	//
	// Variable and reference assignment
	//
	// -----------------------------------------------------------------
	
	
	/**
	* 
	* Sets variables for the template.
	* 
	* This method is overloaded; you can assign all the properties of
	* an object, an associative array, or a single value by name.
	* 
	* You are not allowed to set variables that begin with an underscore;
	* these are either private properties for Savant2 or private variables
	* within the template script itself.
	* 
	* <code>
	* 
	* $Savant2 =& new Savant2();
	* 
	* // assign directly
	* $Savant2->var1 = 'something';
	* $Savant2->var2 = 'else';
	* 
	* // assign by name and value
	* $Savant2->assign('var1', 'something');
	* $Savant2->assign('var2', 'else');
	* 
	* // assign by assoc-array
	* $ary = array('var1' => 'something', 'var2' => 'else');
	* $Savant2->assign($obj);
	* 
	* // assign by object
	* $obj = new stdClass;
	* $obj->var1 = 'something';
	* $obj->var2 = 'else';
	* $Savant2->assign($obj);
	* 
	* </code>
	* 
	* Greg Beaver came up with the idea of assigning to public class
	* properties.
	* 
	* @access public
	* 
	* @return void
	* 
	* @throws object An error object with a SAVANT2_ERROR_ASSIGN code.
	* 
	*/
	
	function assign()
	{
		// this method is overloaded.
		$arg = func_get_args();
		
		// must have at least one argument. no error, just do nothing.
		if (! isset($arg[0])) {
			return;
		}
		
		// assign by object
		if (is_object($arg[0])) {
			// assign public properties
			foreach (get_object_vars($arg[0]) as $key => $val) {
				if (substr($key, 0, 1) != '_') {
					$this->$key = $val;
				}
			}
			return;
		}
		
		// assign by associative array
		if (is_array($arg[0])) {
			foreach ($arg[0] as $key => $val) {
				if (substr($key, 0, 1) != '_') {
					$this->$key = $val;
				}
			}
			return;
		}
		
		// assign by string name and mixed value.
		// 
		// we use array_key_exists() instead of isset() becuase isset()
		// fails if the value is set to null.
		if (is_string($arg[0]) &&
			substr($arg[0], 0, 1) != '_' &&
			array_key_exists(1, $arg)) {
			$this->$arg[0] = $arg[1];
		} else {
			return $this->error(SAVANT2_ERROR_ASSIGN, $arg);
		}
	}
	
	
	/**
	* 
	* Sets references for the template.
	* 
	* // assign by name and value
	* $Savant2->assignRef('ref', $reference);
	* 
	* // assign directly
	* $Savant2->ref =& $reference;
	* 
	* Greg Beaver came up with the idea of assigning to public class
	* properties.
	* 
	* @access public
	* 
	* @param string $key The name for the reference in the template.
	*
	* @param mixed &$val The referenced variable.
	* 
	* @return void
	* 
	* @throws object An error object with a SAVANT2_ERROR_ASSIGNREF code.
	* 
	*/
	
	function assignRef($key, &$val)
	{
		if (is_string($key) && substr($key, 0, 1) != '_') {
			$this->$key =& $val;
		} else {
			return $this->error(
				SAVANT2_ERROR_ASSIGNREF,
				array('key' => $key, 'val' => $val)
			);
		}
	}
	
	
	/**
	*
	* Unsets assigned variables and references.
	* 
	* @access public
	* 
	* @param mixed $var If null, clears all variables; if a string, clears
	* the one variable named by the string; if a sequential array, clears
	* the variables names in that array.
	* 
	* @return void
	*
	*/
	
	function clear($var = null)
	{
		if (is_null($var)) {
			// clear all variables
			$var = array_keys(get_object_vars($this));
		} else {
			// clear specific variables
			settype($var, 'array');
		}
		
		// clear out the selected variables
		foreach ($var as $name) {
			if (substr($name, 0, 1) != '_' && isset($this->$name)) {
				unset($this->$name);
			}
		}
	}
	
	
	/**
	* 
	* Gets the current value of one, many, or all assigned variables.
	* 
	* Never returns variables starting with an underscore; these are
	* reserved for internal Savant2 use.
	* 
	* @access public
	* 
	* @param mixed $key If null, returns a copy of all variables and
	* their values; if an array, returns an only those variables named
	* in the array; if a string, returns only that variable.
	* 
	* @return mixed If multiple variables were reqested, returns an
	* associative array where the key is the variable name and the 
	* value is the variable value; if one variable was requested,
	* returns the variable value only.
	* 
	*/
	
	function getVars($key = null)
	{
		if (is_null($key)) {
			$key = array_keys(get_object_vars($this));
		}
		
		if (is_array($key)) {
			// return a series of vars
			$tmp = array();
			foreach ($key as $var) {
				if (substr($var, 0, 1) != '_' && isset($this->$var)) {
					$tmp[$var] = $this->$var;
				}
			}
			return $tmp;
		} else {
			// return a single var
			if (substr($key, 0, 1) != '_' && isset($this->$key)) {
				return $this->$key;
			}
		}
	}
	
	
	// -----------------------------------------------------------------
	//
	// Template processing
	//
	// -----------------------------------------------------------------
	
	
	/**
	*
	* Loads a template script for execution (does not execute the script).
	* 
	* This will optionally compile the template source into a PHP script
	* if a compiler object has been passed into Savant2.
	* 
	* Also good for including templates from the template paths within
	* another template, like so:
	*
	* include $this->loadTemplate('template.tpl.php');
	* 
	* @access public
	*
	* @param string $tpl The template source name to look for.
	* 
	* @param bool $setScript Default false; if true, sets the $this->_script
	* property to the resulting script path (or null on error).  Normally,
	* only $this->fetch() will need to set this to true.
	* 
	* @return string The full path to the compiled template script.
	* 
	* @throws object An error object with a SAVANT2_ERROR_NOTEMPLATE code.
	* 
	*/
	
	function loadTemplate($tpl = null, $setScript = false)
	{
		// set to default template if none specified.
		if (is_null($tpl)) {
			$tpl = $this->_template;
		}
		
		// find the template source.
		$file = $this->findFile('template', $tpl);
		if (! $file) {
			return $this->error(
				SAVANT2_ERROR_NOTEMPLATE,
				array('template' => $tpl)
			);
		}
		
		// are we compiling source into a script?
		if (is_object($this->_compiler)) {
			// compile the template source and get the path to the
			// compiled script (will be returned instead of the
			// source path)
			$result = $this->_compiler->compile($file);
		} else {
			// no compiling requested, return the source path
			$result = $file;
		}
		
		// is there a script from the compiler?
		if (! $result || $this->isError($result)) {
		
			if ($setScript) {
				$this->_script = null;
			}
			
			// return an error, along with any error info
			// generated by the compiler.
			return $this->error(
				SAVANT2_ERROR_NOSCRIPT,
				array(
					'template' => $tpl,
					'compiler' => $result
				)
			);
			
		} else {
		
			if ($setScript) {
				$this->_script = $result;
			}
			
			return $result;
			
		}
	}
	
	
	/**
	* 
	* This is a an alias to loadTemplate() that cannot set the script.
	* 
	* @access public
	*
	* @param string $tpl The template source name to look for.
	* 
	* @return string The full path to the compiled template script.
	* 
	* @throws object An error object with a SAVANT2_ERROR_NOTEMPLATE code.
	* 
	*/
	
	function findTemplate($tpl = null)
	{
		return $this->loadTemplate($tpl, false);
	}
	
	
	/**
	* 
	* Executes a template script and returns the results as a string.
	* 
	* @param string $_tpl The name of the template source file ...
	* automatically searches the template paths and compiles as needed.
	* 
	* @return string The output of the the template script.
	* 
	* @throws object An error object with a SAVANT2_ERROR_NOSCRIPT code.
	* 
	*/
	
	function fetch($_tpl = null)
	{
		// clear prior output
		$this->_output = null;
		
		// load the template script
		$_result = $this->loadTemplate($_tpl, true);
		
		// is there a template script to be processed?
		if ($this->isError($_result)) {
			return $_result;
		}
		
		// unset so as not to introduce into template scope
		unset($_tpl);
		unset($_result);
		
		// never allow a 'this' property
		if (isset($this->this)) {
			unset($this->this);
		}
		
		// are we extracting variables into local scope?
		if ($this->_extract) {
			// extract references to this object's public properties.
			// this allows variables assigned by-reference to refer all
			// the way back to the model logic.  variables assigned
			// by-copy only refer back to the property.
			foreach (array_keys(get_object_vars($this)) as $_prop) {
				if (substr($_prop, 0, 1) != '_') {
					// set a variable-variable to an object property
					// reference
					$$_prop =& $this->$_prop;
				}
			}
			
			// unset private loop vars
			unset($_prop);
		}
		
		// start capturing output into a buffer
		ob_start();
		
		// include the requested template filename in the local scope
		// (this will execute the view logic).
		include $this->_script;
		
		// done with the requested template; get the buffer and 
		// clear it.
		$this->_output = ob_get_contents();
		ob_end_clean();
		
		// done!
		return $this->applyFilters();
	}
	
	
	/**
	* 
	* Execute and display a template script.
	* 
	* @param string $tpl The name of the template file to parse;
	* automatically searches through the template paths.
	* 
	* @return void
	* 
	* @throws object An error object with a SAVANT2_ERROR_NOSCRIPT code.
	* 
	* @see fetch()
	* 
	*/
	
	function display($tpl = null)
	{
		$result = $this->fetch($tpl);
		if ($this->isError($result)) {
			return $result;
		} else {
			echo $result;
		}
	}
	
	
	// -----------------------------------------------------------------
	//
	// Plugins
	//
	// -----------------------------------------------------------------
	
	
	/**
	*
	* Loads a plugin class and instantiates it within Savant2.
	*
	* @access public
	*
	* @param string $name The plugin name (not including Savant2_Plugin_
	* prefix).
	*
	* @param array $conf An associative array of plugin configuration
	* options.
	*
	* @param bool $savantRef Default false.  When true, sets the $Savant
	* property of the filter to a reference to this Savant object.
	*
	* @return void
	* 
	* @throws object An error object with a SAVANT2_ERROR_NOPLUGIN code.
	* 
	*/
	
	function loadPlugin($name, $conf = array(), $savantRef = null)
	{
		// if no $savantRef is provided, use the default.
		if (is_null($savantRef)) {
			$savantRef = $this->_reference;
		}
		
		// some basic information
		$class = "Savant2_Plugin_$name";
		$file = "$class.php";
		
		// is it loaded?
		if (! class_exists($class)) {
			
			$result = $this->findFile('resource', $file);
			if (! $result) {
				return $this->error(
					SAVANT2_ERROR_NOPLUGIN,
					array('plugin' => $name)
				);
			} else {
				include_once $result;
			}
		}
		
		// is it instantiated?
		if (! isset($this->_resource['plugin'][$name]) ||
			! is_object($this->_resource['plugin'][$name]) ||
			! is_a($this->_resource['plugin'][$name], $class)) {
			
			// instantiate it
			$this->_resource['plugin'][$name] =& new $class($conf);
			
			// add a Savant reference if requested
			if ($savantRef) {
				$this->_resource['plugin'][$name]->Savant =& $this;
			}
			
		}
	}
	
	
	/**
	*
	* Unloads one or more plugins from Savant2.
	*
	* @access public
	*
	* @param string|array $name The plugin name (not including Savant2_Plugin_
	* prefix).  If null, unloads all plugins; if a string, unloads that one
	* plugin; if an array, unloads all plugins named as values in the array.
	*
	* @return void
	* 
	*/
	
	function unloadPlugin($name = null)
	{
		if (is_null($name)) {
			$this->_resource['plugin'] = array();
		} else {
			settype($name, 'array');
			foreach ($name as $key) {
				if (isset($this->_resource['plugin'][$key])) {
					unset($this->_resource['plugin'][$key]);
				}
			}
		}
	}
	
	
	/**
	*
	* Executes a plugin with arbitrary parameters and returns the
	* result.
	* 
	* @access public
	* 
	* @param string $name The plugin name (not including Savant2_Plugin_
	* prefix).
	*
	* @return mixed The plugin results.
	*
	* @throws object An error object with a SAVANT2_ERROR_NOPLUGIN code.
	* 
	* @see loadPlugin()
	* 
	*/
	
	function splugin($name)
	{
		// attempt to load the plugin
		$result = $this->loadPlugin($name);
		if ($this->isError($result)) {
			return $result;
		}
		
		// call the plugin's "plugin()" method with arguments,
		// dropping the first argument (the plugin name)
		$args = func_get_args();
		array_shift($args);
		return call_user_func_array(
			array(&$this->_resource['plugin'][$name], 'plugin'), $args
		);
	}
	
	
	/**
	*
	* Executes a plugin with arbitrary parameters and displays the
	* result.
	* 
	* @access public
	* 
	* @param string $name The plugin name (not including Savant2_Plugin_
	* prefix).
	*
	* @return void
	* 
	* @throws object An error object with a SAVANT2_ERROR_NOPLUGIN code.
	* 
	*/
	
	function plugin($name)
	{
		$args = func_get_args();
		
		$result = call_user_func_array(
			array(&$this, 'splugin'),
			$args
		);
		
		if ($this->isError($result)) {
			return $result;
		} else {
			echo $result;
		}
	}
	
	
	/**
	*
	* PHP5 ONLY: Magic method alias to plugin().
	* 
	* E.g., instead of $this->plugin('form', ...) you would use
	* $this->form(...).  You can set this to use any other Savant2 method
	* by issuing, for example, setCall('splugin') to use splugin() ... which 
	* is really the only other sensible choice.
	* 
	* @access public
	* 
	* @param string $func The plugin name.
	*
	* @param array $args Arguments passed to the plugin.
	*
	* @return void
	* 
	* @throws object An error object with a SAVANT2_ERROR_NOPLUGIN code.
	* 
	*/
	
	function __call($func, $args)
	{
		// add the plugin name to the args
		array_unshift($args, $func);
		
		// call the plugin() method
		return call_user_func_array(
			array(&$this, $this->_call),
			$args
		);
	}
	
	
	// -----------------------------------------------------------------
	//
	// Filters
	//
	// -----------------------------------------------------------------
	
	
	/**
	*
	* Loads a filter class and instantiates it within Savant2.
	*
	* @access public
	*
	* @param string $name The filter name (not including Savant2_Filter_
	* prefix).
	*
	* @param array $conf An associative array of filter configuration
	* options.
	* 
	* @param bool $savantRef Default false.  When true, sets the $Savant
	* property of the filter to a reference to this Savant object.
	*
	* @return void
	* 
	* @throws object An error object with a SAVANT2_ERROR_NOFILTER code.
	* 
	*/
	
	function loadFilter($name, $conf = array(), $savantRef = null)
	{
		// if no $savantRef is provided, use the default.
		if (is_null($savantRef)) {
			$savantRef = $this->_reference;
		}
		
		// some basic information
		$class = "Savant2_Filter_$name";
		$file = "$class.php";
		
		// is it loaded?
		if (! class_exists($class)) {
			
			$result = $this->findFile('resource', $file);
			if (! $result) {
				return $this->error(
					SAVANT2_ERROR_NOFILTER,
					array('filter' => $name)
				);
			} else {
				include_once $result;
			}
		}
		
		// is it instantiated?
		if (! isset($this->_resource['filter'][$name]) ||
			! is_object($this->_resource['filter'][$name]) ||
			! is_a($this->_resource['filter'][$name], $class)) {
			
			// instantiate it
			$this->_resource['filter'][$name] =& new $class($conf);
			
			// add a Savant reference if requested
			if ($savantRef) {
				$this->_resource['filter'][$name]->Savant =& $this;
			}
			
		}
	}
	
	
	/**
	*
	* Unloads one or more filters from Savant2.
	*
	* @access public
	*
	* @param string|array $name The filter name (not including Savant2_Filter_
	* prefix).  If null, unloads all filters; if a string, unloads that one
	* filter; if an array, unloads all filters named as values in the array.
	*
	* @return void
	* 
	*/
	
	function unloadFilter($name = null)
	{
		if (is_null($name)) {
			$this->_resource['filter'] = array();
		} else {
			settype($name, 'array');
			foreach ($name as $key) {
				if (isset($this->_resource['filter'][$key])) {
					unset($this->_resource['filter'][$key]);
				}
			}
		}
	}
	
	
	/**
	*
	* Apply all loaded filters, in order, to text.
	*
	* @access public
	*
	* @param string $text The text to which filters should be applied. 
	* If null, sets the text to $this->_output.
	* 
	* @return string The text after being passed through all loded
	* filters.
	* 
	*/
	
	function applyFilters($text = null)
	{
		// set to output text if no text specified
		if (is_null($text)) {
			$text = $this->_output;
		}
		
		// get the list of filter names...
		$filter = array_keys($this->_resource['filter']);
		
		// ... and apply them each in turn.
		foreach ($filter as $name) {
			$this->_resource['filter'][$name]->filter($text);
		}
		
		// done
		return $text;
	}
	
	
	// -----------------------------------------------------------------
	//
	// Error handling
	//
	// -----------------------------------------------------------------
	
	
	/**
	*
	* Returns an error object.
	* 
	* @access public
	* 
	* @param int $code A SAVANT2_ERROR_* constant.
	* 
	* @param array $info An array of error-specific information.
	* 
	* @return object An error object of the type specified by
	* $this->_error.
	* 
	*/
	
	function &error($code, $info = array())
	{
		// the error config array
		$conf = array(
			'code' => $code,
			'text' => 'Savant2: ',
			'info' => (array) $info
		);
		
		// set an error message from the globals
		if (isset($GLOBALS['_SAVANT2']['error'][$code])) {
			$conf['text'] .= $GLOBALS['_SAVANT2']['error'][$code];
		} else {
			$conf['text'] .= '???';
		}
		
		// set up the error class name
		if ($this->_error) {
			$class = 'Savant2_Error_' . $this->_error;
		} else {
			$class = 'Savant2_Error';
		}

		// set up the error class file name
		$file = $class . '.php';
		
		// is it loaded?
		if (! class_exists($class)) {
			
			// find the error class
			$result = $this->findFile('resource', $file);
			if (! $result) {
				// could not find the custom error class, revert to
				// Savant_Error base class.
				$class = 'Savant2_Error';
				$result = dirname(__FILE__) . '/Savant2/Error.php';
			}
			
			// include the error class
			include_once $result;
		}
		
		// instantiate and return the error class
		$err =& new $class($conf);
		return $err;
	}
	
	
	/**
	*
	* Tests if an object is of the Savant2_Error class.
	* 
	* @access public
	* 
	* @param object &$obj The object to be tested.
	* 
	* @return boolean True if $obj is an error object of the type
	* Savant2_Error, or is a subclass that Savant2_Error. False if not.
	*
	*/
	
	function isError(&$obj)
	{
		if (is_object($obj)) {
			if (is_a($obj, 'Savant2_Error') ||
				is_subclass_of($obj, 'Savant2_Error')) {
				return true;
			}
		}
		
		return false;
	}
}
?>