<?php
/**
* A class for performing code analysis for php scripts
* It is designed to be the heart of a code limiting script
* to use with Savant {@link http://phpsavant.com}
*
* This code should be php4 compatiable but i've only run it in php5 and some of the Tokenizer constants have changed
*
* @author	Joshua Eichorn <josh@bluga.net>
* @copyright	Joshua Eichorn 2004
* @package	PHPCodeAnalyzer
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
* @license LGPL http://www.gnu.org/copyleft/lesser.html
*/

/**#@+
* compat tokeniezer defines
*/
if (! defined('T_OLD_FUNCTION')) {
    define('T_OLD_FUNCTION', T_FUNCTION);
}
if (!defined('T_ML_COMMENT')) {
    define('T_ML_COMMENT', T_COMMENT);
} else {
    define('T_DOC_COMMENT', T_ML_COMMENT);
} 
/**#@-*/

/**
* Code Analysis class
*
* Example Usage:
* <code>
* $analyzer = new PHPCodeAnalyzer();
* $analyzer->source = file_get_contents(__FILE__);
* $analyzer->analyze();
* print_r($analyzer->calledMethods);
* </code>
*
* @todo is it important to grab the details from creating new functions defines classes?
* @todo support php5 only stuff like interface
*
* @version	0.4
* @license LGPL http://www.gnu.org/copyleft/lesser.html
* @copyright	Joshua Eichorn 2004
* @package	PHPCodeAnalyzer
* @author	Joshua Eichorn <josh@bluga.net>
*/
class PHPCodeAnalyzer
{
	/**
	* Source code to analyze
	*/
	var $source = "";

	/**
	* functions called
	*/
	var $calledFunctions = array();

	/**
	* Called constructs
	*/
	var $calledConstructs = array();

	/**
	* methods called
	*/
	var $calledMethods = array();

	/**
	* static methods called
	*/
	var $calledStaticMethods = array();

	/**
	* new classes instantiated 
	*/
	var $classesInstantiated = array();

	/**
	* variables used
	*/
	var $usedVariables = array();

	/**
	* member variables used
	*/
	var $usedMemberVariables = array();

	/**
	* classes created
	*/
	var $createdClasses = array();

	/**
	* functions created
	*/
	var $createdFunctions = array();

	/**
	* Files includes or requried
	*/
	var $filesIncluded = array();

	// private variables
	/**#@+
	* @access private
	*/
	var $currentString = null;
	var $currentStrings = null;
	var $currentVar = false;
	var $staticClass = false;
	var $inNew = false;
	var $inInclude = false;
	var $lineNumber = 1;
	/**#@-*/

	/**
	* parse source filling informational arrays
	*/
	function analyze()
	{
		$tokens = token_get_all($this->source);

		// mapping of token to method to call
		$handleMap = array(
			T_STRING => 'handleString',
			T_CONSTANT_ENCAPSED_STRING => 'handleString',
			T_ENCAPSED_AND_WHITESPACE => 'handleString',
			T_CHARACTER => 'handleString',
			T_NUM_STRING => 'handleString',
			T_DNUMBER => 'handleString',
			T_FUNC_C => 'handleString',
			T_CLASS_C => 'handleString',
			T_FILE => 'handleString',
			T_LINE => 'handleString',
			T_DOUBLE_ARROW => 'handleString',

			T_DOUBLE_COLON => 'handleDoubleColon',
			T_NEW => 'handleNew',
			T_OBJECT_OPERATOR => 'handleObjectOperator',
			T_VARIABLE => 'handleVariable',
			T_FUNCTION => 'handleFunction',
			T_OLD_FUNCTION => 'handleFunction',
			T_CLASS => 'handleClass',
			T_WHITESPACE => 'handleWhitespace',
			T_INLINE_HTML => 'handleWhitespace',
			T_OPEN_TAG => 'handleWhitespace',
			T_CLOSE_TAG => 'handleWhitespace',

			T_AS	=> 'handleAs',

			T_ECHO => 'handleConstruct',
			T_EVAL => 'handleConstruct',
			T_UNSET => 'handleConstruct',
			T_ISSET => 'handleConstruct',
			T_PRINT => 'handleConstruct',
			T_FOR	=> 'handleConstruct',
			T_FOREACH=> 'handleConstruct',
			T_EMPTY	=> 'handleConstruct',
			T_EXIT	=> 'handleConstruct',
			T_CASE	=> 'handleConstruct',
			T_GLOBAL=> 'handleConstruct',
			T_UNSET	=> 'handleConstruct',
			T_WHILE	=> 'handleConstruct',
			T_DO	=> 'handleConstruct',
			T_IF	=> 'handleConstruct',
			T_LIST	=> 'handleConstruct',
			T_RETURN=> 'handleConstruct',
			T_STATIC=> 'handleConstruct',
			T_ENDFOR=> 'handleConstruct',
			T_ENDFOREACH=> 'handleConstruct',
			T_ENDIF=> 'handleConstruct',
			T_ENDSWITCH=> 'handleConstruct',
			T_ENDWHILE=> 'handleConstruct',

			T_INCLUDE => 'handleInclude',
			T_INCLUDE_ONCE => 'handleInclude',
			T_REQUIRE => 'handleInclude',
			T_REQUIRE_ONCE => 'handleInclude',
		);

		foreach($tokens as $token)
		{
			if (is_string($token))
			{
				// we have a simple 1-character token
				$this->handleSimpleToken($token);
			}
			else
			{
				list($id, $text) = $token;
				if (isseT($handleMap[$id]))
				{
					$call = $handleMap[$id];
					$this->$call($id,$text);
				}
				/*else
				{
					echo token_name($id).": $text<br>\n";
				}*/
			}
		}
	}

	/**
	* Handle a 1 char token
	* @access private
	*/
	function handleSimpleToken($token)
	{
		if ($token !== ";")
		{
			$this->currentStrings .= $token;
		}
		switch($token)
		{
			case "(":
				// method is called
				if ($this->staticClass !== false)
				{
					if (!isset($this->calledStaticMethods[$this->staticClass][$this->currentString]))
					{
						$this->calledStaticMethods[$this->staticClass][$this->currentString] 
							= array();
					}
					$this->calledStaticMethods[$this->staticClass][$this->currentString][] 
						= $this->lineNumber;
					$this->staticClass = false;
				}
				else if ($this->currentVar !== false)
				{
					if (!isset($this->calledMethods[$this->currentVar][$this->currentString]))
					{
						$this->calledMethods[$this->currentVar][$this->currentString] = array();
					}
					$this->calledMethods[$this->currentVar][$this->currentString][] = $this->lineNumber;
					$this->currentVar = false;
				}
				else if ($this->inNew !== false)
				{
					$this->classInstantiated();
				}
				else if ($this->currentString !== null)
				{
					$this->functionCalled();
				}
				//$this->currentString = null;
			break;
			case "=":
			case ";":
				if ($this->inNew !== false)
				{
					$this->classInstantiated();
				}
				else if ($this->inInclude !== false)
				{
					$this->fileIncluded();
				}
				else if ($this->currentVar !== false)
				{
					$this->useMemberVar();
				}
				$this->currentString = null;
				$this->currentStrings = null;
			break;
		}
	}

	/**
	* handle includes and requires
	* @access private
	*/
	function handleInclude($id,$text)
	{
		$this->inInclude = true;
		$this->handleConstruct($id,$text);
	}

	/**
	* handle String tokens
	* @access private
	*/
	function handleString($id,$text)
	{
		$this->currentString = $text;
		$this->currentStrings .= $text;
	}

	/**
	* handle variables
	* @access private
	*/
	function handleVariable($id,$text)
	{
		$this->currentString = $text;
		$this->currentStrings .= $text;
		$this->useVariable();
	}


	/**
	* handle Double Colon tokens
	* @access private
	*/
	function handleDoubleColon($id,$text)
	{
		$this->staticClass = $this->currentString;
		$this->currentString = null;
	}

	/**
	* handle new keyword
	* @access private
	*/
	function handleNew($id,$text)
	{
		$this->inNew = true;
	}

	/**
	* handle function
	* @access private
	*/
	function handleFunction($id,$text)
	{
		$this->createdFunctions[] = $this->lineNumber;
	}

	/**
	* handle class
	* @access private
	*/
	function handleClass($id,$text)
	{
		$this->createdClasses[] = $this->lineNumber;
	}

	/**
	* Handle ->
	* @access private
	*/
	function handleObjectOperator($id,$text)
	{
		$this->currentVar = $this->currentString;
		$this->currentString = null;
		$this->currentStrings .= $text;
	}

	/**
	* handle whitespace to figure out line counts
	* @access private
	*/
	function handleWhitespace($id,$text)
	{
		$this->lineNumber+=substr_count($text,"\n");
		if ($id == T_CLOSE_TAG)
		{
			$this->handleSimpleToken(";");
		}
	}


	/**
	* as has been used we must have a var before it
	*
	* @access private
	*/
	function handleAs($id,$text)
	{
		$this->handleSimpleToken(";");
	}

	/**
	* a language construct has been called record it
	* @access private
	*/
	function handleConstruct($id,$construct)
	{
		if (!isset($this->calledConstructs[$construct]))
		{
			$this->calledConstructs[$construct] = array();
		}
		$this->calledConstructs[$construct][] = $this->lineNumber;
		$this->currentString = null;
	}

	/**
	* a class was Instantiated record it
	* @access private
	*/
	function classInstantiated()
	{
		if (!isset($this->classesInstantiated[$this->currentString]))
		{
			$this->classesInstantiated[$this->currentString] = array();
		}
		$this->classesInstantiated[$this->currentString][] = $this->lineNumber;
		$this->inNew = false;
	}

	/**
	* a file was included record it
	* @access private
	*/
	function fileIncluded()
	{
		if (!isset($this->filesIncluded[$this->currentStrings]))
		{
			$this->filesIncluded[$this->currentStrings] = array();
		}
		$this->filesIncluded[$this->currentStrings][] = $this->lineNumber;
		$this->inInclude = false;
		$this->currentString = null;
		$this->currentStrings = "";
	}

	/**
	* a function was called record it
	* @access private
	*/
	function functionCalled($id = false)
	{
		if (!isset($this->calledFunctions[$this->currentString]))
		{
			$this->calledFunctions[$this->currentString] = array();
		}
		$this->calledFunctions[$this->currentString][] = $this->lineNumber;
		$this->currentString = null;
	}

	/**
	* we used a member variable record it
	* @access private
	*/
	function useMemberVar()
	{
		if (!isset($this->usedMemberVariables[$this->currentVar][$this->currentString]))
		{
			$this->usedMemberVariables[$this->currentVar][$this->currentString] = array();
		}
		$this->usedMemberVariables[$this->currentVar][$this->currentString][] = $this->lineNumber;
		$this->currentVar = false;
		$this->currentString = null;
	}

	/**
	* we used a variable record it
	* @access private
	*/
	function useVariable()
	{
		if (!isset($this->usedVariables[$this->currentString]))
		{
			$this->usedVariables[$this->currentString] = array();
		}
		$this->usedVariables[$this->currentString][] = $this->lineNumber;
	}
}
?> 
