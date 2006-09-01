<?php

/**
* 
* Basic compiler for Savant2.
* 
* This is a simple compiler provided as an example.  It probably won't
* work with streams, but it does limit the template syntax in a
* relatively strict way.  It's not perfect, but it's OK for many
* purposes.  Basically, the compiler converts specialized instances of
* curly braces into PHP commands or Savant2 method calls.  It will
* probably mess up embedded JavaScript unless you change the prefix
* and suffix to something else (e.g., '<!-- ' and ' -->', but then it 
* will mess up your HTML comments ;-).
* 
* When in "restrict" mode, ise of PHP commands not in the whitelists
* will cause the compiler to * fail.  Use of various constructs and
* superglobals, likewise.
* 
* Use {$var} or {$this->var} to print a variable.
* 
* Use {: function-list} to print the results of function calls.
* 
* Use {['pluginName', 'arg1', $arg2, $this->arg3]} to call plugins.
* 
* Use these for looping:
* 	{foreach ():} ... {endforeach}
* 	{for ():} ... {endfor}
* 	{while ():} ... {endwhile}
* 
* Use these for conditionals (normal PHP can go in the parens):
* 	{if (...):}
* 	{elseif (...):}
* 	{else:}
* 	{endif}
*   {switch (...):}
*   {case ...:}
*   {default:}
*   {endswitch}
* 
* {break} and {continue} are supported as well.
* 
* Use this to include a template:
* 	{tpl 'template.tpl.php'}
* 	{tpl $tplname}
* 	{tpl $this->tplname}
* 
* $Id: Savant2_Compiler_basic.php,v 1.14 2005/08/09 22:19:39 pmjones Exp $
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

require_once 'Savant2/Compiler.php';
require_once 'Savant2/Error.php';
require_once 'Savant2/PHPCodeAnalyzer.php';

class Savant2_Compiler_basic extends Savant2_Compiler {
	
		
	/**
	* 
	* The template directive prefix.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $prefix = '{';
	
		
	/**
	* 
	* The template directive suffix.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $suffix = '}';
	
	
	/**
	* 
	* The conversion regular expressions.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $convert = array(
	
		// branching
		'(if\s*(.+):)'        => '$1',
		'(elseif\s*(.+):)'    => '$1',
		'(else\s*(.+):)'      => '$1',
		'(endif)'             => '$1',
		'(switch\s*(.+):)'    => '$1',
		'(case\s*(.+):)'      => '$1',
		'(default:)'          => '$1',
		'(endswitch)'         => '$1',
		'(break)'             => '$1',
		
		// looping
		'(foreach\s*(.+):)'   => '$1',
		'(endforeach)'        => '$1',
		'(for\s*(.+):)'       => '$1',
		'(endfor)'            => '$1',
		'(while\s*(.+):)'     => '$1',
		'(endwhile)'          => '$1',
		'(continue)'          => '$1',
		
		// simple variable printing
		'(\$(.+))'            => 'print $1',
		
		// extended printing
		'(\:(.+))'            => 'print ($2)',
		
		// comments
		'\*(.*)?\*'           => '/**$1*/',
		
		// template includes
		'tpl (.*)'            => 'include $this->findTemplate($1)',
		
		// plugins
		'\[\s*(.+)?\s*\]'     => '$this->plugin($1)',
	);
	
	
	/**
	* 
	* The list of allowed functions when in restricted mode.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $allowedFunctions = array();
	
	
	/**
	* 
	* The list of allowed static methods when in restricted mode.
	* 
	* @access public
	* 
	* @var array
	* 
	*/
	
	var $allowedStatic = array();
	
	
	/**
	* 
	* The directory where compiled templates are saved.
	* 
	* @access public
	* 
	* @var string
	* 
	*/
	
	var $compileDir = null;
	
	
	/**
	* 
	* Whether or not to force every template to be compiled every time.
	* 
	* @access public
	* 
	* @var bool
	* 
	*/
	
	var $forceCompile = false;
	
	
	
	/**
	* 
	* Whether or not to strict-check the compiled template.
	* 
	* Strict-checks are off by default until all problems with
	* PhpCodeAnalyzer have been resolved.
	* 
	* @access public
	* 
	* @var bool
	* 
	*/
	
	var $strict = false;
	
	
	/**
	* 
	* Constructor.
	* 
	*/
	
	function Savant2_Compiler_basic($conf = array())
	{
		parent::Savant2_Compiler($conf);
		$this->ca =& new PHPCodeAnalyzer();
		$this->allowedFunctions = $this->allowedFunctions();
		$this->allowedStatic = $this->allowedStatic();
	}
	
	
	/**
	* 
	* Has the source template changed since it was last compiled?
	* 
	* @access public
	* 
	* @var string $tpl The source template file.
	* 
	*/
	
	function changed($tpl)
	{
		// the path for the compiled file
		$file = $this->getPath($tpl);
		
		// if the copmiled file does not exist, or if the mod-time of
		// the source is later than that of the existing compiled file,
		// then the source template file has changed.
		if (! file_exists($file) ||
			filemtime($tpl) > filemtime($file)) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	* 
	* Saves the PHP compiled from template source.
	* 
	* @access public
	* 
	* @var string $tpl The source template file.
	* 
	*/
	
	function saveCompiled($tpl, $php)
	{
		$fp = fopen($this->getPath($tpl), 'w');
		if (! $fp) {
			return false;
		} else {
			$result = fwrite($fp, $php);
			fclose($fp);
			return $result;
		}
	}
	
	
	/**
	* 
	* Gets the path to the compiled PHP for a template source.
	* 
	* @access public
	* 
	* @var string $tpl The source template file.
	* 
	*/
	
	function getPath($tpl)
	{
		$dir = $this->compileDir;
		if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
			$dir .= DIRECTORY_SEPARATOR;
		}
		return $dir . 'Savant2_' . md5($tpl);
	}
	
	
	/**
	* 
	* Compiles a template source into PHP code for Savant.
	* 
	* @access public
	* 
	* @var string $tpl The source template file.
	* 
	*/
	
	function compile($tpl)
	{
		// create a end-tag so that text editors don't
		// stop colorizing text
		$end = '?' . '>';
		
		// recompile only if we are forcing compiled, or
		// if the template source has changed.
		if ($this->forceCompile || $this->changed($tpl)) {
			
			// get the template source text
			$php = file_get_contents($tpl);
			
			/**
			* @todo Do we really care about PHP tags?  The code analyzer
			* will disallow any offending PHP regardless.
			*/
			
			// disallow PHP long tags
			$php = str_replace('<?php', '&lt;?php', $php);
			
			// disallow PHP short tags (if turned on)
			if (ini_get('short_open_tag')) {
				$php = str_replace('<?', '&lt;?', $php);
				$php = str_replace('<?=', '&lt;?=', $php);
			}
			
			// disallow closing tags
			$php = str_replace($end, '?&gt;', $php);
			
			// convert each template command
			foreach ($this->convert as $find => $replace) {
			
				// allow whitespace around the command
				$find = preg_quote($this->prefix) . '\s*' . $find .
					'\s*' . preg_quote($this->suffix);
					
				// actually do the find-and-replace
				$php = preg_replace(
					"/$find/U",
					"<?php $replace $end",
					$php
				);
				
			}
			
			// +++ DEBUG
			// $this->saveCompiled($tpl, $php);
			// --- DEBUG
			
			// are we doing strict checking?
			if ($this->strict) {
				// analyze the code for restriction violations.
				$report = $this->analyze($php);
				if (count($report) > 0) {
					// there were violations, report them as a generic
					// Savant error and return.  Savant will wrap this
					// generic rror with another error that will report
					// properly to the customized error handler (if any).
					return new Savant2_Error(
						array(
							'code' => SAVANT2_ERROR_COMPILE_FAIL,
							'text' => $GLOBALS['_SAVANT2']['error'][SAVANT2_ERROR_COMPILE_FAIL],
							'info' => $report
						)
					);
				}
			}
			
			// otherwise, save the compiled template
			$this->saveCompiled($tpl, $php);
		}
		
		// return the path to the compiled PHP script
		return $this->getPath($tpl);
	}
	
	
	/**
	* 
	* Analyze a compiled template for restriction violations.
	* 
	* @access public
	* 
	* @var string $php The compiled PHP code from a template source.
	* 
	* @return array An array of restriction violations; if empty, then
	* there were no violations discovered by analysis.
	* 
	*/
	
	function analyze(&$php)
	{
		// analyze the compiled code
		$ca =& $this->ca;
		$ca->source =& $php;
		$ca->analyze();
		
		// array of captured restriction violations
		$report = array();
		
		// -------------------------------------------------------------
		//
		// go through the list of called functions and make sure each
		// one is allowed via the whitelist.  if not, record each non-
		// allowed function.  this also restricts variable-functions
		// such as $var().
		//
		
		foreach ($ca->calledFunctions as $func => $lines) {
			if (! in_array($func, $this->allowedFunctions)) {
				$report[$func] = $lines;
			}
		}
		
		// -------------------------------------------------------------
		//
		// disallow use of various constructs (include is allowed, we
		// need it for {tpl}).
		//
		
		$tmp = array(
			'eval',
			'global',
			'include_once',
			'require',
			'require_once',
			'parent',
			'self'
		);
		
		foreach ($tmp as $val) {
			if (isset($ca->calledConstructs[$val])) {
				$report[$val] = $ca->calledConstructs[$val];
			}
		}
		
		// -------------------------------------------------------------
		//
		// disallow instantiation of new classes
		//
		
		foreach ($ca->classesInstantiated as $key => $val) {
			$report['new ' . $key] = $val;
		}
		
		// -------------------------------------------------------------
		//
		// disallow access to the various superglobals
		// so that templates cannot manipulate them.
		//
		
		$tmp = array(
			'$_COOKIE',
			'$_ENV',
			'$_FILES',
			'$_GET',
			'$_POST',
			'$_REQUEST',
			'$_SERVER',
			'$_SESSION',
			'$GLOBALS',
			'$HTTP_COOKIE_VARS',
			'$HTTP_ENV_VARS',
			'$HTTP_GET_VARS',
			'$HTTP_POST_FILES',
			'$HTTP_POST_VARS',
			'$HTTP_SERVER_VARS',
			'$HTTP_SESSION_VARS'
		);
		
		foreach ($ca->usedVariables as $var => $lines) {
			if (in_array(strtoupper($var), $tmp)) {
				$report[$var] = $lines;
			}
		}
		
		// -------------------------------------------------------------
		//
		// allow only certain $this methods
		//
		
		$tmp = array('plugin', 'splugin', 'findTemplate');
		if (isset($ca->calledMethods['$this'])) {
			foreach ($ca->calledMethods['$this'] as $method => $lines) {
				if (! in_array($method, $tmp)) {
					$report['$this->' . $method] = $lines;
				}
			}
		}
		
		// -------------------------------------------------------------
		//
		// disallow private and variable-variable $this properties
		// 
		
		if (isset($ca->usedMemberVariables['$this'])) {
			foreach ($ca->usedMemberVariables['$this'] as $prop => $lines) {
				$char = substr($prop, 0, 1);
				if ($char == '_' || $char == '$') {
					$report['$this->' . $prop] = $lines;
				}
			}
		}
		
		// -------------------------------------------------------------
		//
		// allow only certain static method calls
		//
		
		foreach ($ca->calledStaticMethods as $class => $methods) {
			foreach ($methods as $method => $lines) {
				if (! array_key_exists($class, $this->allowedStatic)) {
				
					// the class itself is not allowed
					$report["$class::$method"] = $lines;
					
				} elseif (! in_array('*', $this->allowedStatic[$class]) &&
					! in_array($method, $this->allowedStatic[$class])){
					
					// the specific method is not allowed,
					// and there is no wildcard for the class methods.
					$report["$class::$method"] = $lines;
					
				}
			}
		}
		
		// -------------------------------------------------------------
		//
		// only allow includes via $this->findTemplate(*)
		//
		
		foreach ($ca->filesIncluded as $text => $lines) {
			
			// in each include statment, look for $this->findTemplate.
			preg_match(
				'/(.*)?\$this->findTemplate\((.*)?\)(.*)/i',
				$text,
				$matches
			);
			
			if (! empty($matches[1]) || ! empty($matches[3]) ||
				empty($matches[2])) {
				
				// there is something before or after the findTemplate call,
				// or it's a direct include (which is not allowed)
				$report["include $text"] = $lines;
				
			}
		}
		
		// -------------------------------------------------------------
		//
		// do not allow the use of "$this" by itself;
		// it must be always be followed by "->" or another
		// valid variable-name character (a-z, 0-9, or _).
		//
		
		$regex = '/(.*)?\$this(?!(\-\>)|([a-z0-9_]))(.*)?/i';
		preg_match_all($regex, $php, $matches, PREG_SET_ORDER);
		foreach ($matches as $val) {
			$report['\'$this\' without \'->\''][] = $val[0];
		}
		
		/** @todo disallow standalone variable-variables, $$var */
		
		/** @todo disallow vars from static classes? class::$var */
		
		
		// -------------------------------------------------------------
		//
		// done!
		//
		
		// +++ DEBUG
		//echo "<pre>";
		//print_r($ca);
		//echo "</pre>";
		// --- DEBUG
		
		return $report;
	}
	
	
	/**
	* 
	* A list of allowed static method calls for templates.
	* 
	* The format is ...
	* 
	* array(
	*     'Class1' => array('method1', 'method2'),
	*     'Class2' => array('methodA', 'methodB'),
	*     'Class3' => '*'
	* );
	* 
	* If you want to allow all methods from the static class to be allowed,
	* use a '*' in the method name list.
	* 
	*/

	function allowedStatic()
	{
		return array();
	}
	
	
	/**
	* 
	* A list of allowed functions for templates.
	* 
	*/
	
	function allowedFunctions()
	{
		return array(
			
			// arrays
			'array_count_values',
			'array_key_exists',
			'array_keys',
			'array_sum',
			'array_values',
			'compact',
			'count',
			'current',
			'each',
			'end',
			'extract',
			'in_array',
			'key',
			'list',
			'next',
			'pos',
			'prev',
			'reset',
			'sizeof',
			
			// calendar
			'cal_days_in_month',
			'cal_from_jd',
			'cal_to_jd',
			'easter_date',
			'easter_days',
			'FrenchToJD',
			'GregorianToJD',
			'JDDayOfWeek',
			'JDMonthName',
			'JDToFrench',
			'JDToGregorian',
			'jdtojewish',
			'JDToJulian',
			'jdtounix',
			'JewishToJD',
			'JulianToJD',
			'unixtojd',
			
			// date
			'checkdate',
			'date_sunrise',
			'date_sunset',
			'date',
			'getdate',
			'gettimeofday',
			'gmdate',
			'gmmktime',
			'gmstrftime',
			'idate',
			'localtime',
			'microtime',
			'mktime',
			'strftime',
			'strptime',
			'strtotime',
			'time',
		
			// gettext
			'_',
			'gettext',
			'ngettext',
			
			// math
			'abs',
			'acos',
			'acosh',
			'asin',
			'asinh',
			'atan2',
			'atan',
			'atanh',
			'base_convert',
			'bindec',
			'ceil',
			'cos',
			'cosh',
			'decbin',
			'dechex',
			'decoct',
			'deg2rad',
			'exp',
			'expm1',
			'floor',
			'fmod',
			'getrandmax',
			'hexdec',
			'hypot',
			'is_finite',
			'is_infinite',
			'is_nan',
			'lcg_value',
			'log10',
			'log1p',
			'log',
			'max',
			'min',
			'mt_getrandmax',
			'mt_rand',
			'mt_srand',
			'octdec',
			'pi',
			'pow',
			'rad2deg',
			'rand',
			'round',
			'sin',
			'sinh',
			'sqrt',
			'srand',
			'tan',
			'tanh',
		
			// strings
			'chop',
			'count_chars',
			'echo',
			'explode',
			'hebrev',
			'hebrevc',
			'html_entity_decode',
			'htmlentities',
			'htmlspecialchars',
			'implode',
			'join',
			'localeconv',
			'ltrim',
			'money_format',
			'nl_langinfo',
			'nl2br',
			'number_format',
			'ord',
			'print',
			'printf',
			'quoted_printable_decode',
			'rtrim',
			'sprintf',
			'sscanf',
			'str_pad',
			'str_repeat',
			'str_replace',
			'str_rot13',
			'str_shuffle',
			'str_word_count',
			'strcasecmp',
			'strchr',
			'strcmp',
			'strcoll',
			'strcspn',
			'strip_tags',
			'stripcslashes',
			'stripos',
			'stripslashes',
			'stristr',
			'strlen',
			'strnatcasecmp',
			'strnatcmp',
			'strncasecmp',
			'strncmp',
			'strpbrk',
			'strpos',
			'strrchr',
			'strrev',
			'strripos',
			'strrpos',
			'strspn',
			'strstr',
			'strtok',
			'strtolower',
			'strtoupper',
			'strtr',
			'substr_compare',
			'substr_count',
			'substr_replace',
			'substr',
			'trim',
			'ucfirst',
			'ucwords',
			'wordwrap',
		
			// url
			'base64_decode',
			'base64_encode',
			'rawurldecode',
			'rawurlencode',
			'urldecode',
			'urlencode',
		
			// variables
			'empty',
			'is_array',
			'is_bool',
			'is_double',
			'is_float',
			'is_int',
			'is_integer',
			'is_long',
			'is_null',
			'is_numeric',
			'is_object',
			'is_real',
			'is_resource',
			'is_scalar',
			'is_string',
			'isset',
			'print_r',
			'unset',
			'var_dump',
		);
	}
}


	
?>